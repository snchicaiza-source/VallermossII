<?php
// config/auth_middleware.php

function redirigirSegunRol($rol) {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    if (strpos($base, '/controllers') !== false) {
        $root = dirname($base);
    } elseif (preg_match('#/views/(administrador|directiva|residente|auth)$#', $base)) {
        $root = dirname(dirname($base));
    } else {
        $root = $base;
    }

    $map = [
        'ADMINISTRADOR' => '/views/administrador/comunicados.php',
        'DIRECTIVA'     => '/views/directiva/dashboard.php',
        'RESIDENTE'     => '/views/residente/dashboard.php',
    ];

    $path = $map[strtoupper($rol)] ?? $map['RESIDENTE'];
    header("Location: " . $root . $path);
    exit();
}

function verificarRol($rolesPermitidos = []) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $scriptActual = $_SERVER['SCRIPT_NAME'];

    if (strpos($scriptActual, 'login.php') !== false || strpos($scriptActual, 'index.php') !== false) {
        return;
    }

    $rolUsuario = $_SESSION['rol'] ?? $_SESSION['usuario_rol'] ?? '';

    if (empty($rolUsuario) || (!empty($rolesPermitidos) && !in_array(strtoupper($rolUsuario), $rolesPermitidos))) {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        if (strpos($scriptDir, '/administrador') !== false || strpos($scriptDir, '/directiva') !== false || strpos($scriptDir, '/residente') !== false) {
            header("Location: ../auth/login.php");
        } else {
            header("Location: views/auth/login.php");
        }
        exit();
    }
}
