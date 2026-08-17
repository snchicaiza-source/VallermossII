<?php
require_once __DIR__ . '/../config/db.php';

class Pago {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::obtenerConexion();
    }

    // Obtener pagos en estado PENDIENTE para auditoría
    public function obtenerPendientes() {
        try {
            $sql = "SELECT p.*, u.nombres AS usuario_nombre, u.email 
                    FROM pagos p
                    LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                    WHERE p.estado = 'PENDIENTE'
                    ORDER BY p.created_at DESC, p.id_pago DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Obtener historial completo de pagos
    public function obtenerTodos() {
        try {
            $sql = "SELECT p.*, u.nombres AS usuario_nombre, u.email 
                    FROM pagos p
                    LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                    ORDER BY p.created_at DESC, p.id_pago DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    // Método solicitado por views/residente/dashboard.php
    public function obtenerPorUsuario($id_usuario) {
        try {
            $sql = "SELECT * FROM pagos WHERE id_usuario = :id_usuario ORDER BY fecha_pago DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}