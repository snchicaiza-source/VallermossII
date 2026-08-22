<?php
// models/Notificacion.php
// Helper central para crear notificaciones in-app por usuario.
// Todos los metodos son estaticos y tolerantes a fallos: si la notificacion
// falla, nunca rompe el flujo principal del sistema.

require_once __DIR__ . '/../config/db.php';

class Notificacion {

    private static $tablaLista = false;

    /** Crea la tabla de notificaciones si aun no existe (auto-reparable). */
    private static function asegurarTabla($pdo) {
        if (self::$tablaLista) return;
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
            self::$tablaLista = true;
        } catch (PDOException $e) {
            error_log('[Notificacion::asegurarTabla] ' . $e->getMessage());
        }
    }

    /**
     * Envia una notificacion a uno o varios usuarios.
     * $idsUsuarios: int o array de ids. $refId/$refTipo permiten enlazar al modulo.
     */
    public static function enviar($idsUsuarios, $tipo, $titulo, $mensaje = '', $refId = null, $refTipo = null) {
        if (empty($idsUsuarios)) return false;
        $ids = is_array($idsUsuarios) ? array_map('intval', $idsUsuarios) : [(int)$idsUsuarios];
        $ids = array_filter(array_unique($ids), function ($i) { return $i > 0; });
        if (empty($ids)) return false;

        try {
            $pdo = Database::obtenerConexion();
            self::asegurarTabla($pdo);
            $stmt = $pdo->prepare("INSERT INTO notificaciones_usuario (id_usuario, tipo, titulo, mensaje, referencia_id, referencia_tipo, leida, fecha_creacion)
                                   VALUES (:uid, :tipo, :titulo, :mensaje, :rid, :rtipo, 0, NOW())");
            foreach ($ids as $uid) {
                $stmt->execute([
                    ':uid'    => $uid,
                    ':tipo'   => substr($tipo, 0, 50),
                    ':titulo' => substr($titulo, 0, 200),
                    ':mensaje'=> $mensaje,
                    ':rid'    => $refId,
                    ':rtipo'  => $refTipo
                ]);
            }
            return true;
        } catch (PDOException $e) {
            error_log('[Notificacion] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia a todos los usuarios ACTIVO de los roles indicados.
     * $roles: string ('ADMINISTRADOR') o array (['ADMINISTRADOR','DIRECTIVA']).
     */
    public static function enviarRol($roles, $tipo, $titulo, $mensaje = '', $refId = null, $refTipo = null, $excluirId = null) {
        try {
            $pdo = Database::obtenerConexion();
            $rolesArr = is_array($roles) ? $roles : [$roles];
            $placeholders = implode(',', array_fill(0, count($rolesArr), '?'));
            $sql = "SELECT id_usuario FROM usuarios WHERE rol IN ($placeholders) AND estado = 'ACTIVO'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($rolesArr);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if ($excluirId !== null) {
                $ids = array_diff(array_map('intval', $ids), [(int)$excluirId]);
            }
            return self::enviar($ids, $tipo, $titulo, $mensaje, $refId, $refTipo);
        } catch (PDOException $e) {
            error_log('[Notificacion::enviarRol] ' . $e->getMessage());
            return false;
        }
    }

    /** Atajo: administradores y directiva (quienes gestionan pagos, reservas e incidencias). */
    public static function enviarGestion($tipo, $titulo, $mensaje = '', $refId = null, $refTipo = null, $excluirId = null) {
        return self::enviarRol(['ADMINISTRADOR', 'DIRECTIVA'], $tipo, $titulo, $mensaje, $refId, $refTipo, $excluirId);
    }

    /** Atajo: todos los residentes activos. */
    public static function enviarResidentes($tipo, $titulo, $mensaje = '', $refId = null, $refTipo = null) {
        return self::enviarRol('RESIDENTE', $tipo, $titulo, $mensaje, $refId, $refTipo);
    }

    /** Nombre de un usuario (para armar mensajes). */
    public static function nombreUsuario($idUsuario) {
        try {
            $pdo = Database::obtenerConexion();
            $stmt = $pdo->prepare("SELECT nombres FROM usuarios WHERE id_usuario = :id");
            $stmt->execute([':id' => (int)$idUsuario]);
            return (string)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return '';
        }
    }
}
