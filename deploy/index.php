<?php
session_start();

if (isset($_SESSION['rol']) || isset($_SESSION['usuario_rol'])) {
    $rol = $_SESSION['rol'] ?? $_SESSION['usuario_rol'];
    
    switch (strtoupper($rol)) {
        case 'ADMINISTRADOR':
            header("Location: views/administrador/comunicados.php");
            exit();
        case 'DIRECTIVA':
            header("Location: views/directiva/dashboard.php");
            exit();
        case 'RESIDENTE':
        default:
            header("Location: views/residente/dashboard.php");
            exit();
    }
} else {
    header("Location: views/auth/login.php");
    exit();
}
