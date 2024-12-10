<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir al login si no está logueado
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario - Asistencias Basket</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos de Bootstrap -->
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
            max-width: 400px;
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
        }

        .btn-primary:hover {
            background-color: #3a4db0;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .link {
            font-size: 0.9em;
            color: var(--primary-color);
            text-decoration: none;
        }

        .link:hover {
            text-decoration: underline;
        }

        .alert {
            font-size: 0.9em;
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
                    <li class="nav-item"><a class="nav-link active" href="form_register.php"><i class="bi bi-person-lines-fill"></i> Registrar Usuario</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> Configuración</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="form-container">
            <h3 class="text-center mb-4" style="color: var(--primary-color);">
                <i class="bi bi-person-plus-fill"></i> Registrar Usuario
            </h3>

            <!-- Mostrar el mensaje de error si existe -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $_SESSION['error_message'];
                    unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Mostrar el mensaje de éxito si existe -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success" role="alert">
                    <?= $_SESSION['success_message'];
                    unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="bi bi-person"></i> Nombre de Usuario
                    </label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Ingrese su usuario" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock"></i> Contraseña
                    </label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Ingrese su contraseña" required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">
                        <i class="bi bi-shield-lock"></i> Confirmar Contraseña
                    </label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repita su contraseña" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3">
                    <i class="bi bi-person-check-fill"></i> Registrar
                </button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>