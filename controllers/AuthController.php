<?php
// controllers/AuthController.php
session_start();

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../config/auth_middleware.php';

$root = dirname(dirname($_SERVER['SCRIPT_NAME']));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $correo = trim($_POST['correo'] ?? '');
        $clave  = trim($_POST['clave'] ?? '');

        if (empty($correo) || empty($clave)) {
            $_SESSION['error_login'] = "Por favor, complete todos los campos.";
            header("Location: {$root}/views/auth/login.php");
            exit();
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerPorCorreo($correo);

        $hashGuardado = $usuario['clave_hash'] ?? $usuario['password'] ?? $usuario['clave'] ?? '';

        $passwordValida = false;
        if ($usuario && !empty($hashGuardado)) {
            if (password_verify($clave, $hashGuardado)) {
                $passwordValida = true;
            }
        }

        if ($passwordValida) {
            $_SESSION['id_usuario']       = $usuario['id_usuario'];
            $_SESSION['usuario_id']       = $usuario['id_usuario'];
            $_SESSION['nombres']          = $usuario['nombres'];
            $_SESSION['usuario_nombres']  = $usuario['nombres'];
            $_SESSION['correo']           = $usuario['correo'];
            $_SESSION['usuario_correo']   = $usuario['correo'];
            $_SESSION['rol']              = $usuario['rol'];
            $_SESSION['usuario_rol']      = $usuario['rol'];
            $_SESSION['numero_vivienda']  = $usuario['numero_vivienda'] ?? '';
            $_SESSION['usuario_vivienda'] = $usuario['numero_vivienda'] ?? '';

            redirigirSegunRol($usuario['rol']);
        } else {
            $_SESSION['error_login'] = "Correo electronico o contrasena incorrectos.";
            header("Location: {$root}/views/auth/login.php");
            exit();
        }
    }

    if ($action === 'logout') {
        session_unset();
        session_destroy();
        header("Location: {$root}/views/auth/login.php?logout=1");
        exit();
    }
} else {
    header("Location: {$root}/views/auth/login.php");
    exit();
}
