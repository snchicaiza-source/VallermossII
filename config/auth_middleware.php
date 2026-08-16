<?php
// config/auth_middleware.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../views/auth/login.php");
        exit();
    }
}

function verificarRol($rolesPermitidos = []) {
    verificarSesion();
    
    if (!in_array($_SESSION['usuario_rol'], $rolesPermitidos)) {
        redirigirSegunRol($_SESSION['usuario_rol']);
        exit();
    }
}

function redirigirSegunRol($rol) {
    switch ($rol) {
        case 'ADMINISTRADOR':
            header("Location: ../views/administrador/comunicados.php");
            break;
        case 'DIRECTIVA':
            header("Location: ../views/administrador/comunicados.php");
            break;
        case 'RESIDENTE':
        default:
            header("Location: ../views/residente/dashboard.php");
            break;
    }
    exit();
}
?>