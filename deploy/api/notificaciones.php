<?php
// api/notificaciones.php - AJAX endpoint para notificaciones
// A prueba de fallos: auto-crea la tabla si no existe y SIEMPRE devuelve JSON valido.
session_start();
header('Content-Type: application/json; charset=utf-8');

function jsonResp($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if (session_status() === PHP_SESSION_NONE || empty($_SESSION['id_usuario'])) {
    jsonResp(['error' => 'No autenticado'], 401);
}

require_once __DIR__ . '/../config/db.php';

try {
    $pdo = Database::obtenerConexion();
} catch (Throwable $e) {
    jsonResp(['error' => 'Sin conexion a la base de datos'], 500);
}

// Auto-reparacion: crea la tabla e indice si no existen en produccion
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS notificaciones_usuario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        tipo VARCHAR(50) NOT NULL DEFAULT 'COMUNICADO',
        titulo VARCHAR(200) NOT NULL,
        mensaje TEXT DEFAULT NULL,
        referencia_id INT DEFAULT NULL,
        referencia_tipo VARCHAR(50) DEFAULT NULL,
        leida TINYINT(1) DEFAULT 0,
        fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_usuario_leida (id_usuario, leida)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
} catch (PDOException $e) { /* ya existe */ }

$id_usuario = (int)$_SESSION['id_usuario'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {

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

} catch (PDOException $e) {
    // Nunca rompemos el frontend: devolvemos JSON con estado vacio
    echo json_encode([
        'notificaciones' => [],
        'total_no_leidas' => 0,
        'error_db' => true
    ]);
}
