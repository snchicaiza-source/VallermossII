<?php
require_once __DIR__ . '/../config/db.php';

class Incidencia {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::obtenerConexion();
    }

    public static function asegurarTabla() {
        try {
            $pdo = Database::obtenerConexion();
            $pdo->exec("CREATE TABLE IF NOT EXISTS incidencias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT NOT NULL,
                tipo ENUM('DANO','QUEJA','RESERVACION') DEFAULT 'DANO',
                descripcion TEXT,
                estado ENUM('PENDIENTE','EN_PROCESO','RESUELTO') DEFAULT 'PENDIENTE',
                fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (PDOException $e) {
            // silencioso: si la tabla ya existe no hay problema
        }
    }

    public function obtenerPorUsuario($id_usuario) {
        self::asegurarTabla();
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM incidencias WHERE id_usuario = :id ORDER BY id DESC");
            $stmt->execute([':id' => $id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}