<?php
require 'config.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir al login si no está logueado
    exit;
}

// Verificar si se ha enviado una fecha de filtro
$filter_date = isset($_POST['filter_date']) ? $_POST['filter_date'] : null;

// Consulta para obtener los totales de recaudación, filtrados por fecha si se especifica
if ($filter_date) {
    $stmt = $pdo->prepare("
        SELECT attendance_date, SUM(revenue) as total_revenue 
        FROM attendance 
        WHERE attendance_date = :date
        GROUP BY attendance_date
        ORDER BY attendance_date DESC
    ");
    $stmt->execute(['date' => $filter_date]);
} else {
    // Si no se ha especificado fecha, obtener todos los registros
    $stmt = $pdo->query("
        SELECT attendance_date, SUM(revenue) as total_revenue 
        FROM attendance 
        GROUP BY attendance_date
        ORDER BY attendance_date DESC
    ");
}

$totals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recaudación por Día</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a69bd;
            --secondary-color: #718093;
            --accent-color: #f1c40f;
        }

        body {
            background-color: #f4f6f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .nav-link {
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--accent-color) !important;
            transform: translateY(-2px);
        }

        .container {
            margin-top: 40px;
        }

        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
        }

        .form-control {
            border-color: var(--primary-color);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 105, 189, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background-color: #3a4db0;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .table {
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background-color: var(--primary-color);
            color: white;
        }

        .total-revenue {
            background-color: #f0f4ff;
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php">
                <i class="bi bi-basketball"></i> Asistencias ABC
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="home.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_students.php"><i class="bi bi-person"></i> Gestionar Alumnos</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_attendance.php"><i class="bi bi-list-check"></i> Ver Asistencia</a></li>
                    <li class="nav-item"><a class="nav-link active" href="view_revenue.php"><i class="bi bi-cash-stack"></i> Ver Ingresos</a></li>
                    <li class="nav-item"><a class="nav-link" href="form_register.php"><i class="bi bi-person-lines-fill"></i> Registrar Usuario</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> Configuración</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container">
        <h1 class="mb-4" style="color: var(--primary-color);">Recaudación por Día</h1>

        <!-- Formulario de filtrado por fecha -->
        <div class="form-container">
            <form method="POST">
                <div class="mb-3">
                    <label for="filter_date" class="form-label">
                        <i class="bi bi-calendar-date"></i> Selecciona una fecha
                    </label>
                    <input type="date" id="filter_date" name="filter_date" class="form-control" value="<?= $filter_date ?>" />
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
            </form>
        </div>

        <!-- Tabla de recaudación -->
        <div class="table-responsive">
            <table class="table table-bordered mb-5">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Total Recaudado (S/.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($totals as $total): ?>
                        <tr>
                            <td><?= htmlspecialchars($total['attendance_date']) ?></td>
                            <td>S/ <?= number_format($total['total_revenue'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($totals) == 0): ?>
                        <tr>
                            <td colspan="2" class="text-center">No se han registrado ingresos para esta fecha.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>