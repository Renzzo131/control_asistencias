<?php
// Configuración de la base de datos
require_once 'config.php'; // Asegúrate de tener esta conexión configurada
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir al login si no está logueado
    exit;
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Verificar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Las contraseñas no coinciden. Por favor, intente de nuevo.";
        header("Location: form_register.php"); // Redirige al formulario
        exit;
    }

    // Hash de la contraseña (bcrypt)
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Verificar si el nombre de usuario ya existe
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $existing_user = $stmt->fetch();

    if ($existing_user) {
        $_SESSION['error_message'] = "El nombre de usuario ya existe. Por favor, elija otro.";
        header("Location: form_register.php"); // Redirige al formulario
        exit;
    }

    // Insertar el nuevo usuario en la base de datos
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->execute(['username' => $username, 'password' => $hashed_password]);

    // Mostrar mensaje de éxito
    $_SESSION['success_message'] = "Usuario registrado exitosamente.";
    header("Location: form_register.php"); // Redirige al formulario
    exit;
}
?>
