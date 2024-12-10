<?php
session_start();
require 'config.php'; // Incluye la conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verificar si el usuario existe
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verificar la contraseña
        if (password_verify($password, $user['password'])) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirigir al home.php
            header("Location: home.php");
            exit;
        } else {
            // Si la contraseña es incorrecta, guardar el mensaje de error
            $_SESSION['error_message'] = "Contraseña incorrecta.";
        }
    } else {
        // Si el usuario no existe, guardar el mensaje de error
        $_SESSION['error_message'] = "Usuario no encontrado.";
    }

    // Redirigir de nuevo a la página de login con el mensaje de error
    header("Location: index.php");
    exit;
}
?>
