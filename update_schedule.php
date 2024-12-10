<?php
require 'config.php'; // Conectar a la base de datos

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir al login si no está logueado
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $class_time = $_POST['class_time'];

    // Verificar si ya existe un horario para la categoría
    $stmt = $pdo->prepare("SELECT id FROM class_schedule WHERE category = :category");
    $stmt->execute(['category' => $category]);
    $existing_schedule = $stmt->fetch();

    if ($existing_schedule) {
        // Actualizar el horario existente
        $stmt = $pdo->prepare("UPDATE class_schedule SET class_time = :class_time WHERE category = :category");
        $stmt->execute(['class_time' => $class_time, 'category' => $category]);
    } else {
        // Insertar un nuevo horario
        $stmt = $pdo->prepare("INSERT INTO class_schedule (category, class_time) VALUES (:category, :class_time)");
        $stmt->execute(['category' => $category, 'class_time' => $class_time]);
    }
    // Redirigir de vuelta a la página de configuración con un mensaje de éxito
    header("Location: settings.php?status=success");
    exit();
}
?>

