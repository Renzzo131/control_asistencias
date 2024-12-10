
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
    <title>Configurar Horarios - Asistencias Basket</title>
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

        .form-select,
        .form-control {
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .form-select:focus,
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

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #5b667a;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4" style="color: var(--primary-color)">
                <i class="bi bi-calendar-event"></i> Configurar Horarios
            </h2>
            <form action="update_schedule.php" method="POST" id="scheduleForm">
                <div class="mb-3">
                    <label for="category" class="form-label">
                        <i class="bi bi-people"></i> Categoría
                    </label>
                    <select id="category" name="category" class="form-select" required>
                        <option value="" disabled selected>Seleccione una categoría</option>
                        <?php
                        require 'config.php';
                        $categories = $pdo->query("SELECT DISTINCT category FROM class_schedule")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($categories as $category) {
                            echo "<option value='" . htmlspecialchars($category['category']) . "'>" . htmlspecialchars($category['category']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="class_time" class="form-label">
                        <i class="bi bi-clock"></i> Nuevo Horario
                    </label>
                    <input type="time" id="class_time" name="class_time" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3">
                    <i class="bi bi-save"></i> Actualizar Horario
                </button>
                <a href="home.php" class="btn btn-secondary w-100 mt-3">
                    <i class="bi bi-arrow-left-circle"></i> Regresar al Dashboard
                </a>
            </form>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
