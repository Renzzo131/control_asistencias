<?php
require 'config.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir al login si no está logueado
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener la fecha ingresada por el usuario
    $report_date = $_POST['report_date'];

    // Consultar los estudiantes y su asistencia para esa fecha
    $stmt = $pdo->prepare("
        SELECT students.name, students.category, attendance.status, attendance.revenue, attendance.attendance_date
        FROM attendance
        JOIN students ON attendance.student_id = students.id
        WHERE attendance.attendance_date = :date
    ");
    $stmt->execute(['date' => $report_date]);
    $attendance_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular el total recaudado en ese día
    $stmt = $pdo->prepare("SELECT SUM(revenue) FROM attendance WHERE attendance_date = :date");
    $stmt->execute(['date' => $report_date]);
    $total_revenue = $stmt->fetchColumn();

    // Obtener el total recaudado por cada categoría
    $stmt = $pdo->prepare("
        SELECT students.category, 
               SUM(attendance.revenue) AS category_revenue,
               COUNT(DISTINCT students.id) AS total_students,
               COUNT(CASE WHEN attendance.status = 'Present' THEN 1 END) AS present_students
        FROM attendance
        JOIN students ON attendance.student_id = students.id
        WHERE attendance.attendance_date = :date
        GROUP BY students.category
    ");
    $stmt->execute(['date' => $report_date]);
    $category_revenues = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Establecer la zona horaria para la fecha por defecto
date_default_timezone_set('America/Lima');
$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Reporte de Asistencia - Basket</title>
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

        .report-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
        }

        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
        }

        .form-control {
            border-color: var(--primary-color);
            transition: all 0.3s ease;
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
                    <li class="nav-item"><a class="nav-link" href="view_revenue.php"><i class="bi bi-cash-stack"></i> Ver Ingresos</a></li>
                    <li class="nav-item"><a class="nav-link" href="form_register.php"><i class="bi bi-person-lines-fill"></i> Registrar Usuario</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> Configuración</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container-fluid py-4 px-md-5">
        <div class="row">
            <div class="col-12 col-md-8 offset-md-2">
                <div class="form-container mb-4">
                    <h2 class="text-center mb-4" style="color: var(--primary-color)">
                        <i class="bi bi-file-earmark-text"></i> Generar Reporte de Asistencia
                    </h2>

                    <form method="POST" id="reportForm">
                        <div class="mb-3">
                            <label for="report_date" class="form-label">
                                <i class="bi bi-calendar-date"></i> Selecciona la fecha
                            </label>
                            <input type="date" id="report_date" name="report_date"
                                class="form-control"
                                max="<?= $today ?>"
                                value="<?= $today ?>"
                                required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-search"></i> Generar Reporte
                        </button>
                    </form>
                </div>

                <?php if (isset($attendance_data) && !empty($attendance_data)): ?>
                    <div class="report-container">
                        <h3 class="mb-4">
                            <i class="bi bi-file-text"></i> Reporte de Asistencia - Fecha: <?= $report_date ?>
                        </h3>

                        <!-- Tabla de Asistencia Detallada -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-5">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Asistencia</th>
                                        <th>Recaudación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendance_data as $data): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($data['name']) ?></td>
                                            <td><?= htmlspecialchars($data['category']) ?></td>
                                            <td>
                                                <?php if ($data['status'] == 'Present'): ?>
                                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Presente</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Ausente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>S/ <?= number_format($data['revenue'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Recaudación por Categoría -->
                        <h4 class="mt-4 mb-3">
                            <i class="bi bi-graph-up"></i> Recaudación por Categoría
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-hover mb-5">
                                <thead>
                                    <tr>
                                        <th>Categoría</th>
                                        <th>Total Estudiantes</th>
                                        <th>Presentes</th>
                                        <th>Recaudación Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($category_revenues as $category): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['category']) ?></td>
                                            <td><?= $category['total_students'] ?></td>
                                            <td>
                                                <?= $category['present_students'] ?>
                                                <small class="text-muted">
                                                    (<?= number_format(($category['present_students'] / $category['total_students']) * 100, 1) ?>%)
                                                </small>
                                            </td>
                                            <td>S/ <?= number_format($category['category_revenue'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Total de Recaudación -->
                        <div class="total-revenue text-center">
                            <h4>
                                <i class="bi bi-cash-coin"></i> Total Recaudado en esta fecha:
                                <strong>S/ <?= number_format($total_revenue, 2) ?></strong>
                            </h4>
                        </div>
                    </div>
                <?php elseif (isset($attendance_data) && empty($attendance_data)): ?>
                    <div class="alert alert-warning text-center" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        No hay datos de asistencia para la fecha seleccionada.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reportForm = document.getElementById('reportForm');
            const reportDateInput = document.getElementById('report_date');

            // Validación del formulario
            reportForm.addEventListener('submit', function(event) {
                if (!reportDateInput.value) {
                    event.preventDefault();
                    alert('Por favor, seleccione una fecha para generar el reporte');
                    reportDateInput.focus();
                }
            });

            // Establecer la fecha máxima al día actual
            reportDateInput.max = '<?= $today ?>';
        });
    </script>
</body>

</html>