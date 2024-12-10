<?php
require 'config.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir al login si no está logueado
    exit;
}

// Obtener los parámetros de filtrado
$name_filter = isset($_GET['name']) ? $_GET['name'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Consultar los estudiantes con los filtros aplicados
$stmt = $pdo->prepare("
    SELECT s.id, s.name, s.category, s.status, g.name AS guardian_name, g.phone AS guardian_phone, g.address AS guardian_address
    FROM students s
    LEFT JOIN guardians g ON s.id = g.student_id
    WHERE s.name LIKE :name
    AND s.category LIKE :category
");
$stmt->execute([
    'name' => '%' . $name_filter . '%',
    'category' => '%' . $category_filter . '%'
]);

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Alumnos</title>
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

        /*         .navbar-nav .nav-item {
            margin-right: 10px;
        }

        .navbar-nav .nav-link {
            font-size: 1rem;
        } */
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
                    <li class="nav-item"><a class="nav-link active" href="manage_students.php"><i class="bi bi-person"></i> Gestionar Alumnos</a></li>
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
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Título de Gestionar Alumnos -->
            <h1 style="color: var(--primary-color);">Gestionar Alumnos</h1>

            <!-- Botón de Agregar Alumno -->
            <a href="add_student.php" class="btn btn-success d-flex align-items-center">
                <i class="bi bi-person-plus-fill me-2"></i> Agregar Alumno
            </a>
        </div>
        <!-- Filtros -->
        <div class="form-container">
            <form method="GET">
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Buscar por nombre" value="<?= htmlspecialchars($name_filter) ?>">
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Categoría</label>
                    <select name="category" id="category" class="form-control">
                        <option value="">Selecciona una categoría</option>
                        <option value="Infantil" <?= $category_filter == 'Infantil' ? 'selected' : '' ?>>Infantil</option>
                        <option value="Sub-12" <?= $category_filter == 'Sub-12' ? 'selected' : '' ?>>Sub-12</option>
                        <option value="Sub-14" <?= $category_filter == 'Sub-14' ? 'selected' : '' ?>>Sub-14</option>
                        <option value="Sub-17" <?= $category_filter == 'Sub-17' ? 'selected' : '' ?>>Sub-17</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </form>
        </div>

        <!-- Tabla de alumnos -->
        <div class="table-responsive mt-4">
            <table class="table table-bordered mb-5">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Apoderado</th>
                        <th>Teléfono Apoderado</th>
                        <th>Dirección Apoderado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <td><?= htmlspecialchars($student['category']) ?></td>
                            <td><?= htmlspecialchars($student['guardian_name']) ?></td>
                            <td><?= htmlspecialchars($student['guardian_phone']) ?></td>
                            <td><?= htmlspecialchars($student['guardian_address']) ?></td>
                            <td><?= ucfirst($student['status']) ?></td>
                            <td>
                                <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                                <?php if ($student['status'] == 'activo'): ?>
                                    <a href="deactivate_student.php?id=<?= $student['id'] ?>" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i></a>
                                <?php else: ?>
                                    <a href="activate_student.php?id=<?= $student['id'] ?>" class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>