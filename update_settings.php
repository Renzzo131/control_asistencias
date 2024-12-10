<?php
require 'config.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir al login si no está logueado
    exit;
}

// Obtener el monto actual
$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'revenue_per_attendance'");
$stmt->execute();
$current_value = $stmt->fetchColumn();

// Procesar el formulario para actualizar el monto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_value = floatval($_POST['value']);

    // Actualizar el valor en la base de datos
    $stmt = $pdo->prepare("UPDATE settings SET value = :value WHERE name = 'revenue_per_attendance'");
    $stmt->execute(['value' => $new_value]);

    $message = "Monto actualizado exitosamente a S/. " . number_format($new_value, 2);
    $current_value = $new_value; // Actualizar el valor en la página
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Monto por Asistencia</title>
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

        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .alert {
            border-radius: 10px;
            padding: 1rem;
        }
    </style>
</head>
<body>
    <!-- Contenido principal -->
    <div class="container-fluid py-4 px-md-5">
        <div class="form-container">
            <h2 class="text-center mb-4" style="color: var(--primary-color)">
                <i class="bi bi-cash-stack"></i> Actualizar Monto por Asistencia
            </h2>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="value" class="form-label">
                        <i class="bi bi-cash"></i> Monto por asistencia (S/.)
                    </label>
                    <input type="number" step="0.01" id="value" name="value" class="form-control" 
       value="<?= htmlspecialchars($current_value) ?>" required min="0" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">

                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3">
                    <i class="bi bi-save"></i> Actualizar Monto
                </button>
            </form>
            <a href="record_attendance.php" class="btn btn-secondary w-100 mt-3">
                <i class="bi bi-arrow-left-circle"></i> Regresar al Registro de Asistencias
            </a>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
