<?php
// config/auth_middleware.php

// Normaliza la raiz del proyecto para construir URLs absolutas.
// Evita '//' al inicio (que el navegador interpreta como host 'views', etc.)
function calcularRaizProyecto() {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

    if (strpos($base, '/controllers') !== false) {
        $root = dirname($base);
    } elseif (preg_match('#/views/(administrador|directiva|residente|auth)$#', $base)) {
        $root = dirname(dirname($base));
    } else {
        $root = $base;
    }

    $root = rtrim(str_replace('\\', '/', $root), '/');
    return ($root === '.' || $root === '/') ? '' : $root;
}

function obtenerRutaSegunRol($rol) {
    $map = [
        'ADMINISTRADOR' => '/views/administrador/comunicados.php',
        'DIRECTIVA'     => '/views/directiva/dashboard.php',
        'RESIDENTE'     => '/views/residente/dashboard.php',
    ];
    return $map[strtoupper($rol)] ?? $map['RESIDENTE'];
}

function redirigirSegunRol($rol) {
    header("Location: " . calcularRaizProyecto() . obtenerRutaSegunRol($rol));
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

// ============================================================
// Red de seguridad global: si cualquier pagina o accion lanza un
// error no capturado (Error o Exception), se muestra el mensaje y
// se regresa a la pagina anterior en lugar de una pagina HTTP 500.
set_exception_handler(function ($e) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $mensaje = 'Error interno: ' . $e->getMessage();

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $_SESSION['flash_error'] = $mensaje;
        $origen = $_SERVER['HTTP_REFERER'] ?? '';
        $host   = $_SERVER['HTTP_HOST'] ?? '';
        if ($origen !== '' && $host !== '' && strpos($origen, $host) !== false) {
            header('Location: ' . $origen);
            exit();
        }
    }

    if (!headers_sent()) {
        http_response_code(200);
    }
    echo '<div style="font-family:Arial,sans-serif;max-width:640px;margin:3rem auto;padding:1.5rem;border:1px solid #e74c3c;border-radius:8px;color:#333;">'
        . '<h2 style="margin-top:0;color:#c0392b;">Ocurrio un problema</h2>'
        . '<p>' . htmlspecialchars($mensaje) . '</p>'
        . '<p><a href="javascript:history.back()">Volver atras</a></p>'
        . '</div>';
    exit();
});
