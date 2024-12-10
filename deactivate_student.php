<?php
require 'config.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir al login si no está logueado
    exit;
}

// Verificar si se ha pasado un ID válido
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Actualizar el estado del estudiante a "inactivo"
        $stmt = $pdo->prepare("UPDATE students SET status = 'inactivo' WHERE id = :id");
        $stmt->execute(['id' => $student_id]);

        // Confirmar la transacción
        $pdo->commit();

        // Mensaje de éxito
        $message = "Estudiante desactivado exitosamente.";
        echo "<script>
            setTimeout(function() {
                window.location.href = 'manage_students.php';
            }, 1500);
        </script>";
    } catch (Exception $e) {
        // Si ocurre algún error, revertir la transacción
        $pdo->rollBack();
        $message = "Hubo un error al desactivar el estudiante. Intente nuevamente.";
    }
} else {
    // Si no se ha pasado un ID válido
    $message = "ID de estudiante no válido.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desactivar Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info">
                <?= $message ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
