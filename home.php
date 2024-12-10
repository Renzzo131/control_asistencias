<?php
// Conectar a la base de datos
require 'config.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir al login si no está logueado
    exit;
}

// Obtener el total de alumnos
$stmt = $pdo->query("SELECT COUNT(*) FROM students");
$total_students = $stmt->fetchColumn();

// Obtener los ingresos del día
date_default_timezone_set('America/Lima');
$date_today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT SUM(revenue) FROM attendance WHERE attendance_date = :date");
$stmt->execute(['date' => $date_today]);
$daily_revenue = $stmt->fetchColumn();

// Obtener la asistencia promedio
$stmt = $pdo->query("SELECT COUNT(*) FROM attendance WHERE status = 'Present'");
$total_attendances = $stmt->fetchColumn();

// Obtener el total recaudado
$stmt = $pdo->query("SELECT SUM(revenue) FROM attendance");
$total_revenue = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Asistencias Basket</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos Bootstrap -->
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

        .dashboard-card {
            border-radius: 10px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: scale(1.03);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5rem;
        }

        .dashboard-card .card-title {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .dashboard-card .card-title i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .action-buttons .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background-color: var(--primary-color);
            color: white;
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
                    <li class="nav-item"><a class="nav-link" href="manage_students.php"><i class="bi bi-person"></i> Gestionar Alumnos</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_attendance.php"><i class="bi bi-list-check"></i> Ver Asistencia</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_revenue.php"><i class="bi bi-cash-stack"></i> Ver Ingresos</a></li>
                    <li class="nav-item"><a class="nav-link" href="form_register.php"><i class="bi bi-person-lines-fill"></i> Registrar Usuario</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> Configuración</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="text-center py-2" style="background-color: #e9ecef; font-size: 0.9rem; color: #6c757d;">
        &copy; 2024 Renzo Gamboa. Todos los derechos reservados.
    </div>
    <!-- Contenido principal -->
    <div class="container-fluid py-4 px-md-5">
        <div class="row mb-4 action-buttons">
            <div class="col-md-6 mb-3 mb-md-0">
                <a href="record_attendance.php" class="btn btn-warning w-100">
                    <i class="bi bi-check-circle"></i> Registrar Asistencia
                </a>
            </div>
            <div class="col-md-6">
                <a href="generate_report.php" class="btn btn-dark w-100">
                    <i class="bi bi-file-earmark-text"></i> Generar Reporte por Día
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Tarjetas de métricas -->
            <div class="col-md-3">
                <div class="card text-white bg-info dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-people"></i> Total de Alumnos</h5>
                        <p class="card-text fs-3 text-end"><?= number_format($total_students) ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-success dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-cash"></i> Ingresos de Hoy</h5>
                        <p class="card-text fs-3 text-end">S/ <?= number_format($daily_revenue, 2) ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-secondary dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-graph-up"></i> Asistencia Promedio</h5>
                        <p class="card-text fs-3 text-end"><?= number_format(($total_attendances / $total_students) * 100, 2) ?>%</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-danger dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-bar-chart"></i> Total Recaudado</h5>
                        <p class="card-text fs-3 text-end">S/ <?= number_format($total_revenue, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Resumen -->
        <div class="bg-white p-4 rounded-3 shadow-sm">
            <h3 class="mb-4">Resumen de Categorías</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th>Total de Alumnos</th>
                            <th>Asistencia de Hoy</th>
                            <th>Recaudación</th>
                            <th>Horario</th> <!-- Nueva columna para mostrar el horario -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $categories = $pdo->query("SELECT category, COUNT(*) as total_students FROM students GROUP BY category")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($categories as $category) {
                            // Obtener el horario de la categoría
                            $stmt = $pdo->prepare("SELECT class_time FROM class_schedule WHERE category = :category");
                            $stmt->execute(['category' => $category['category']]);
                            $class_time = $stmt->fetchColumn();

                            // Obtener la asistencia de hoy
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE status = 'Present' AND student_id IN (SELECT id FROM students WHERE category = :category) AND attendance_date = :date");
                            $stmt->execute(['category' => $category['category'], 'date' => $date_today]);
                            $attendance_count = $stmt->fetchColumn();

                            // Obtener la recaudación de hoy
                            $stmt = $pdo->prepare("SELECT SUM(revenue) FROM attendance WHERE student_id IN (SELECT id FROM students WHERE category = :category) AND attendance_date = :date");
                            $stmt->execute(['category' => $category['category'], 'date' => $date_today]);
                            $category_revenue = $stmt->fetchColumn();
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($category['category']) ?></td>
                                <td><?= $category['total_students'] ?></td>
                                <td><?= $attendance_count ?></td>
                                <td>S/ <?= number_format($category_revenue, 2) ?></td>
                                <td><?= $class_time ? htmlspecialchars($class_time) : 'No asignado' ?></td> <!-- Mostrar el horario -->
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>