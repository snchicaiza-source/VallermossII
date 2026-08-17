<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../models/Comunicado.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $comunicadoModel = new Comunicado();

    if ($action === 'crear_comunicado') {
        $titulo = trim($_POST['titulo']);
        $contenido = trim($_POST['contenido']);
        $prioridad = $_POST['prioridad'];
        $id_usuario = $_SESSION['id_usuario'];

        $resultado = $comunicadoModel->crear($titulo, $contenido, $prioridad, $id_usuario);

        if ($resultado) {
            $_SESSION['flash_success'] = "El comunicado ha sido publicado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "No se pudo registrar el comunicado.";
        }
    } elseif ($action === 'eliminar_comunicado') {
        $id_comunicado = (int)$_POST['id_comunicado'];
        $resultado = $comunicadoModel->eliminar($id_comunicado);

        if ($resultado) {
            $_SESSION['flash_success'] = "Comunicado eliminado con éxito.";
        } else {
            $_SESSION['flash_error'] = "No se pudo eliminar el comunicado.";
        }
    }

    header('Location: ../views/administrador/comunicados.php');
    exit();
}