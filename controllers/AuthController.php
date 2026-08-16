<?php
// controllers/AuthController.php
session_start();
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../config/auth_middleware.php';

// Validar solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $correo = trim($_POST['correo'] ?? '');
        $clave  = trim($_POST['clave'] ?? '');

        if (empty($correo) || empty($clave)) {
            $_SESSION['error_login'] = "Por favor, complete todos los campos.";
            header("Location: ../views/auth/login.php");
            exit();
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerPorCorreo($correo);

        // Verificación de credenciales con password_verify
        if ($usuario && password_verify($clave, $usuario['clave_hash'])) {
            // Guardar datos clave en la sesión
            $_SESSION['usuario_id']       = $usuario['id_usuario'];
            $_SESSION['usuario_nombres']  = $usuario['nombres'];
            $_SESSION['usuario_correo']   = $usuario['correo'];
            $_SESSION['usuario_rol']      = $usuario['rol'];
            $_SESSION['usuario_vivienda'] = $usuario['numero_vivienda'];

            // Redirección segura según rol (definida en auth_middleware.php)
            redirigirSegunRol($usuario['rol']);
        } else {
            $_SESSION['error_login'] = "Correo electrónico o contraseña incorrectos.";
            header("Location: ../views/auth/login.php");
            exit();
        }
    }

    if ($action === 'logout') {
        session_unset();
        session_destroy();
        header("Location: ../views/auth/login.php?logout=1");
        exit();
    }
} else {
    // Si intentan ingresar por GET, redirigir al Login
    header("Location: ../views/auth/login.php");
    exit();
}
?>