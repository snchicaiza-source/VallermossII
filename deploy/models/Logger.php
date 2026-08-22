<?php
// models/Logger.php
// Logger general del sistema: registra quien y cuando elimina (o realiza acciones criticas).
require_once __DIR__ . '/../config/db.php';

class Logger {

    private static $tablaLista = false;

    // Crea la tabla de log si no existe (auto-reparacion, patron del proyecto)
    public static function asegurarTabla() {
        if (self::$tablaLista) return;
        try {
            $pdo = Database::obtenerConexion();
            $pdo->exec("CREATE TABLE IF NOT EXISTS log_sistema (
                id_log INT AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT DEFAULT NULL,
                nombre_usuario VARCHAR(120) DEFAULT 'Sistema',
                accion VARCHAR(50) NOT NULL,
                modulo VARCHAR(80) NOT NULL,
                detalle VARCHAR(500) DEFAULT NULL,
                ip VARCHAR(45) DEFAULT NULL,
                fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            self::$tablaLista = true;
        } catch (PDOException $e) {
            error_log('[logger] No se pudo crear log_sistema: ' . $e->getMessage());
        }
    }

    /**
     * Registra una accion en el log del sistema.
     * Ejemplo: Logger::registrar('ELIMINACION', 'Recaudaciones', "Elimino la recaudacion #12 (Alicuota Enero, \$50.00)");
     */
    public static function registrar($accion, $modulo, $detalle = '') {
        self::asegurarTabla();
        try {
            $pdo = Database::obtenerConexion();
            $idUsuario   = $_SESSION['id_usuario'] ?? $_SESSION['usuario_id'] ?? null;
            $nombreUser  = $_SESSION['nombres'] ?? $_SESSION['usuario_nombres'] ?? 'Sistema';
            $ip          = $_SERVER['REMOTE_ADDR'] ?? null;

            // Recorta el detalle al maximo de la columna
            $detalle = mb_substr((string)$detalle, 0, 500);

            $stmt = $pdo->prepare("INSERT INTO log_sistema (id_usuario, nombre_usuario, accion, modulo, detalle, ip)
                                   VALUES (:id_usuario, :nombre, :accion, :modulo, :detalle, :ip)");
            $stmt->execute([
                ':id_usuario' => $idUsuario !== null ? (int)$idUsuario : null,
                ':nombre'     => mb_substr((string)$nombreUser, 0, 120),
                ':accion'     => mb_substr((string)$accion, 0, 50),
                ':modulo'     => mb_substr((string)$modulo, 0, 80),
                ':detalle'    => $detalle !== '' ? $detalle : null,
                ':ip'         => $ip
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('[logger] Error al registrar: ' . $e->getMessage());
            return false;
        }
    }

    // Registra una eliminacion con los datos del registro capturados antes del DELETE.
    public static function eliminacion($modulo, $detalle) {
        return self::registrar('ELIMINACION', $modulo, $detalle);
    }

    public static function obtieneUltimos($limite = 100) {
        self::asegurarTabla();
        try {
            $pdo = Database::obtenerConexion();
            $stmt = $pdo->prepare("SELECT * FROM log_sistema ORDER BY fecha DESC, id_log DESC LIMIT :limite");
            $stmt->bindValue(':limite', max(1, (int)$limite), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
