<?php
// controllers/ViviendaController.php
// Gestion del catalogo de viviendas (solo administrador).
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/Validador.php';
require_once __DIR__ . '/../models/Vivienda.php';
require_once __DIR__ . '/../models/Logger.php';

// Verifica rol administrador por sesion
$rolSesion = strtoupper($_SESSION['rol'] ?? $_SESSION['usuario_rol'] ?? '');
if ($rolSesion !== 'ADMINISTRADOR') {
    $_SESSION['flash_error'] = "Solo el administrador puede gestionar el catálogo de viviendas.";
    header('Location: ../views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $volver = '../views/administrador/usuarios.php';

    if ($action === 'crear_vivienda') {
        $codigo = trim($_POST['codigo'] ?? '');

        $error = Validador::primerError([
            'codigo' => Validador::texto($codigo, 'El código de vivienda', 1, 30),
        ]);
        if ($error === null) {
            $resultado = Vivienda::crear($codigo);
            if ($resultado['ok']) {
                $_SESSION['flash_success'] = "Vivienda \"{$codigo}\" agregada al catálogo.";
            } else {
                $_SESSION['flash_error'] = $resultado['error'];
            }
        } else {
            $_SESSION['flash_error'] = $error;
        }
        header("Location: $volver");
        exit;
    }

    if ($action === 'activar_vivienda' || $action === 'desactivar_vivienda') {
        $id = (int)($_POST['id_vivienda'] ?? 0);
        if ($id > 0) {
            $activa = $action === 'activar_vivienda';
            $r = Vivienda::cambiarEstado($id, $activa);
            $_SESSION[$r['ok'] ? 'flash_success' : 'flash_error'] = $r['ok']
                ? 'Estado de la vivienda actualizado.'
                : $r['error'];
            Logger::registrar('ACTUALIZACION', 'Viviendas', ($activa ? 'Activó' : 'Desactivó') . " la vivienda #{$id}");
        }
        header("Location: $volver");
        exit;
    }

    if ($action === 'eliminar_vivienda') {
        $id = (int)($_POST['id_vivienda'] ?? 0);
        if ($id > 0) {
            // Captura el codigo para el log antes de eliminar
            $todas = Vivienda::obtenerTodas(false);
            $codigo = '';
            foreach ($todas as $v) {
                if ((int)$v['id_vivienda'] === $id) { $codigo = $v['codigo']; break; }
            }

            $r = Vivienda::eliminar($id);
            if ($r['ok']) {
                Logger::eliminacion('Viviendas', "Vivienda \"{$codigo}\" (#{$id}) eliminada del catalogo");
                $_SESSION['flash_success'] = "Vivienda \"{$codigo}\" eliminada del catálogo.";
            } else {
                $_SESSION['flash_error'] = $r['error'];
            }
        }
        header("Location: $volver");
        exit;
    }
}

header('Location: ../views/administrador/usuarios.php');
exit;
