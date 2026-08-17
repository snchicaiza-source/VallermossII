<?php
// config/auth_middleware.php

function redirigirSegunRol($rol) {
    switch (strtoupper($rol)) {
        case 'ADMINISTRADOR':
            header("Location: ../views/administrador/comunicados.php");
            exit();
        case 'DIRECTIVA':
            header("Location: ../views/directiva/dashboard.php");
            exit();
        case 'RESIDENTE':
        default:
            header("Location: ../views/residente/dashboard.php");
            exit();
    }
}

function verificarRol($rolesPermitidos = []) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $scriptActual = $_SERVER['SCRIPT_NAME'];

    // Si ya estamos en login o index, NO redirigir de nuevo para evitar el bucle infinito
    if (strpos($scriptActual, 'login.php') !== false || strpos($scriptActual, 'index.php') !== false) {
        return;
    }

    $rolUsuario = $_SESSION['rol'] ?? $_SESSION['usuario_rol'] ?? '';

    // Si no hay sesión o el rol no está permitido
    if (empty($rolUsuario) || (!empty($rolesPermitidos) && !in_array(strtoupper($rolUsuario), $rolesPermitidos))) {
        header("Location: ../auth/login.php");
        exit();
    }
}