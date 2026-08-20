<?php
// api/notificaciones.php - AJAX endpoint para notificaciones
session_start();
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE || empty($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$pdo = Database::obtenerConexion();
$id_usuario = $_SESSION['id_usuario'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {

    case 'list':
        $limite = min((int)($_GET['limite'] ?? 15), 50);
        $soloNoLeidas = isset($_GET['no_leidas']);

        $sql = "SELECT id, tipo, titulo, mensaje, referencia_id, referencia_tipo, leida, fecha_creacion FROM notificaciones_usuario WHERE id_usuario = :uid";
        $params = [':uid' => $id_usuario];

        if ($soloNoLeidas) {
            $sql .= " AND leida = 0";
        }

        $sql .= " ORDER BY fecha_creacion DESC LIMIT $limite";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $noLeidas = $pdo->prepare("SELECT COUNT(*) FROM notificaciones_usuario WHERE id_usuario = :uid AND leida = 0");
        $noLeidas->execute([':uid' => $id_usuario]);
        $totalNoLeidas = (int)$noLeidas->fetchColumn();

        echo json_encode([
            'notificaciones' => $notificaciones,
            'total_no_leidas' => $totalNoLeidas
        ]);
        break;

    case 'marcar_leida':
        $id_notif = (int)($_POST['id_notificacion'] ?? $_GET['id_notificacion'] ?? 0);
        if ($id_notif > 0) {
            $stmt = $pdo->prepare("UPDATE notificaciones_usuario SET leida = 1 WHERE id = :id AND id_usuario = :uid");
            $stmt->execute([':id' => $id_notif, ':uid' => $id_usuario]);
        }
        $noLeidas = $pdo->prepare("SELECT COUNT(*) FROM notificaciones_usuario WHERE id_usuario = :uid AND leida = 0");
        $noLeidas->execute([':uid' => $id_usuario]);
        echo json_encode(['total_no_leidas' => (int)$noLeidas->fetchColumn()]);
        break;

    case 'marcar_todas':
        $stmt = $pdo->prepare("UPDATE notificaciones_usuario SET leida = 1 WHERE id_usuario = :uid AND leida = 0");
        $stmt->execute([':uid' => $id_usuario]);
        echo json_encode(['total_no_leidas' => 0, 'message' => 'Todas marcadas como leidas']);
        break;

    case 'eliminar':
        $id_notif = (int)($_POST['id_notificacion'] ?? $_GET['id_notificacion'] ?? 0);
        if ($id_notif > 0) {
            $stmt = $pdo->prepare("DELETE FROM notificaciones_usuario WHERE id = :id AND id_usuario = :uid");
            $stmt->execute([':id' => $id_notif, ':uid' => $id_usuario]);
        }
        $noLeidas = $pdo->prepare("SELECT COUNT(*) FROM notificaciones_usuario WHERE id_usuario = :uid AND leida = 0");
        $noLeidas->execute([':uid' => $id_usuario]);
        echo json_encode(['total_no_leidas' => (int)$noLeidas->fetchColumn()]);
        break;

    case 'count':
        $noLeidas = $pdo->prepare("SELECT COUNT(*) FROM notificaciones_usuario WHERE id_usuario = :uid AND leida = 0");
        $noLeidas->execute([':uid' => $id_usuario]);
        echo json_encode(['total_no_leidas' => (int)$noLeidas->fetchColumn()]);
        break;

    default:
        echo json_encode(['error' => 'Accion no valida']);
        break;
}
