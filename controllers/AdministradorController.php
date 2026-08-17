<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../config/database.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::getInstance()->getConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // --- ACTIVOS ---
    case 'crear_activo':
        $nombre = trim($_POST['nombre'] ?? '');
        $estado = trim($_POST['estado'] ?? 'BUENO');

        if (empty($nombre)) {
            header('Location: ../views/administrador/activos.php?error=campos_vacios');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO activos (nombre, estado) VALUES (:nombre, :estado)");
        $stmt->execute([':nombre' => $nombre, ':estado' => $estado]);

        header('Location: ../views/administrador/activos.php?msg=creado');
        exit();

    case 'eliminar_activo':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM activos WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
        header('Location: ../views/administrador/activos.php?msg=eliminado');
        exit();

    // --- CONVENIOS ---
    case 'crear_convenio':
        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $monto_total = (float)($_POST['monto_total'] ?? 0);
        $num_cuotas = (int)($_POST['num_cuotas'] ?? 0);

        if ($id_usuario <= 0 || $monto_total <= 0 || $num_cuotas <= 0) {
            header('Location: ../views/administrador/convenios.php?error=campos_vacios');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO convenios (id_usuario, monto_total, num_cuotas, estado) VALUES (:id_usuario, :monto_total, :num_cuotas, 'ACTIVO')");
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':monto_total' => $monto_total,
            ':num_cuotas' => $num_cuotas
        ]);

        header('Location: ../views/administrador/convenios.php?msg=creado');
        exit();

    case 'cambiar_estado_convenio':
        $id = (int)($_POST['id'] ?? 0);
        $nuevo_estado = $_POST['nuevo_estado'] ?? '';

        if ($id > 0 && in_array($nuevo_estado, ['CUMPLIDO', 'INCUMPLIDO'])) {
            $stmt = $db->prepare("UPDATE convenios SET estado = :estado WHERE id = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);
        }
        header('Location: ../views/administrador/convenios.php?msg=actualizado');
        exit();

    // --- TRÁMITES ---
    case 'crear_tramite':
        $solicitante = trim($_POST['solicitante'] ?? '');
        $asunto = trim($_POST['asunto'] ?? '');

        if (empty($solicitante) || empty($asunto)) {
            header('Location: ../views/administrador/tramites.php?error=campos_vacios');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO tramites (solicitante, asunto, estado) VALUES (:solicitante, :asunto, 'PENDIENTE')");
        $stmt->execute([':solicitante' => $solicitante, ':asunto' => $asunto]);

        header('Location: ../views/administrador/tramites.php?msg=creado');
        exit();

    case 'cambiar_estado_tramite':
        $id = (int)($_POST['id'] ?? 0);
        $nuevo_estado = $_POST['nuevo_estado'] ?? '';

        if ($id > 0 && in_array($nuevo_estado, ['EN_PROCESO', 'COMPLETADO'])) {
            $stmt = $db->prepare("UPDATE tramites SET estado = :estado WHERE id = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);
        }
        header('Location: ../views/administrador/tramites.php?msg=actualizado');
        exit();

    default:
        header('Location: ../views/administrador/comunicados.php');
        exit();
}