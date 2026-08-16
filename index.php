<?php
// index.php
session_start();
require_once __DIR__ . '/config/auth_middleware.php';

if (isset($_SESSION['usuario_id'])) {
    redirigirSegunRol($_SESSION['usuario_rol']);
} else {
    header("Location: /VallermossoII/views/auth/login.php");
    exit();
}
?>