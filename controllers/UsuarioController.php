<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../models/Usuario.php';

verificarRol(['ADMINISTRADOR']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $usuarioModel = new Usuario();

    if ($action === 'crear_usuario') {
        $nombres = trim($_POST['nombres']);
        $correo = trim($_POST['correo']);
        $password = trim($_POST['password']);
        $rol = $_POST['rol'];
        $numero_vivienda = trim($_POST['numero_vivienda']);

        $resultado = $usuarioModel->registrar($nombres, $correo, $password, $rol, $numero_vivienda);

        if ($resultado) {
            $_SESSION['flash_success'] = "Usuario registrado correctamente.";
        } else {
            $_SESSION['flash_error'] = "Error al registrar el usuario o el correo ya existe.";
        }
    } elseif ($action === 'cambiar_estado') {
        $id_usuario = (int)$_POST['id_usuario'];
        $nuevo_estado = $_POST['nuevo_estado'];

        $resultado = $usuarioModel->cambiarEstado($id_usuario, $nuevo_estado);

        if ($resultado) {
            $_SESSION['flash_success'] = "El estado del usuario ha sido actualizado.";
        } else {
            $_SESSION['flash_error'] = "No se pudo cambiar el estado del usuario.";
        }
    }

    header('Location: ../views/administrador/usuarios.php');
    exit();
}