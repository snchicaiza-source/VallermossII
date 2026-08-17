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

        // Identificar la columna de contraseña (soporta 'clave_hash', 'password' o texto plano)
        $hashGuardado = $usuario['clave_hash'] ?? $usuario['password'] ?? $usuario['clave'] ?? '';

        // Verificación de credenciales (compatible con hash y texto plano para desarrollo)
        $passwordValida = false;
        if ($usuario && !empty($hashGuardado)) {
            if (password_verify($clave, $hashGuardado) || $clave === $hashGuardado) {
                $passwordValida = true;
            }
        }

        if ($passwordValida) {
            // Unificación de variables de sesión para compatibilidad con todo el proyecto
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

            // Redirección por rol usando auth_middleware o redirección directa según corresponda
            if (function_exists('redirigirSegunRol')) {
                redirigirSegunRol($usuario['rol']);
            } else {
                switch (strtoupper($usuario['rol'])) {
                    case 'ADMINISTRADOR':
                        header("Location: ../views/administrador/comunicados.php");
                        break;
                    case 'DIRECTIVA':
                        header("Location: ../views/directiva/dashboard.php");
                        break;
                    case 'RESIDENTE':
                    default:
                        header("Location: ../views/residente/dashboard.php");
                        break;
                }
            }
            exit();
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