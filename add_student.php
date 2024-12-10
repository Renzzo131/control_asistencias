<?php
require 'config.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir al login si no está logueado
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger los datos del formulario
    $name = $_POST['name'];
    $category = $_POST['category'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_phone = $_POST['guardian_phone'];
    $guardian_address = $_POST['guardian_address'];

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Insertar el estudiante en la tabla 'students'
        $stmt = $pdo->prepare("INSERT INTO students (name, category) VALUES (:name, :category)");
        $stmt->execute([
            'name' => $name,
            'category' => $category
        ]);

        // Obtener el ID del estudiante recién insertado
        $student_id = $pdo->lastInsertId();

        // Insertar los datos del apoderado en la tabla 'guardians'
        $stmt = $pdo->prepare("INSERT INTO guardians (name, phone, address, relationship, student_id) 
                               VALUES (:guardian_name, :guardian_phone, :guardian_address, 'Padre/Madre', :student_id)");
        $stmt->execute([
            'guardian_name' => $guardian_name,
            'guardian_phone' => $guardian_phone,
            'guardian_address' => $guardian_address,
            'student_id' => $student_id
        ]);

        // Confirmar la transacción
        $pdo->commit();

        // Mensaje de éxito
        $message = "Alumno y apoderado registrados exitosamente.";
    } catch (Exception $e) {
        // Si ocurre algún error, revertir la transacción
        $pdo->rollBack();
        $message = "Hubo un error al registrar los datos. Intente nuevamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Alumno - Asistencias Basket</title>
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

        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 500px;
            margin: 2rem auto;
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

        .alert {
            border-radius: 10px;
            padding: 1rem;
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
        <div class="form-container">
            <h2 class="text-center mb-4" style="color: var(--primary-color)">
                <i class="bi bi-person-plus-fill"></i> Registrar Alumno
            </h2>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="studentForm">
                <div class="mb-3">
                    <label for="name" class="form-label">
                        <i class="bi bi-person"></i> Nombre Completo
                    </label>
                    <input type="text" id="name" name="name" class="form-control" required
                        placeholder="Ingrese el nombre del alumno">
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">
                        <i class="bi bi-tags"></i> Categoría
                    </label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">Seleccione una categoría</option>
                        <option value="Infantil">Infantil</option>
                        <option value="Sub-12">Sub-12</option>
                        <option value="Sub-14">Sub-14</option>
                        <option value="Sub-17">Sub-17</option>
                    </select>
                </div>

                <!-- Campos del Apoderado -->
                <div class="mb-3">
                    <label for="guardian_name" class="form-label">
                        <i class="bi bi-person-fill"></i> Nombre del Apoderado
                    </label>
                    <input type="text" id="guardian_name" name="guardian_name" class="form-control" required
                        placeholder="Ingrese el nombre del apoderado">
                </div>
                <div class="mb-3">
                    <label for="guardian_phone" class="form-label">
                        <i class="bi bi-telephone-fill"></i> Teléfono del Apoderado
                    </label>
                    <input type="tel" id="guardian_phone" name="guardian_phone" class="form-control" required
                        placeholder="Ingrese el teléfono del apoderado">
                </div>
                <div class="mb-3">
                    <label for="guardian_address" class="form-label">
                        <i class="bi bi-house-door"></i> Dirección del Apoderado
                    </label>
                    <input type="text" id="guardian_address" name="guardian_address" class="form-control" required
                        placeholder="Ingrese la dirección del apoderado">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-save"></i> Registrar Alumno
                </button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>