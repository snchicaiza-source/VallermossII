<?php
// models/Pago.php
require_once __DIR__ . '/../config/db.php';

class Pago {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::obtenerConexion();
    }

    // Registrar un nuevo pago reportado por el residente
    public function registrar($id_usuario, $monto, $concepto, $comprobante_url) {
        try {
            $sql = "INSERT INTO pagos (id_usuario, monto, concepto, comprobante_url, estado, fecha_vencimiento) 
                    VALUES (:id_usuario, :monto, :concepto, :comprobante_url, 'PENDIENTE', CURDATE())";
            
            $stmt = $this->pdo->prepare($sql);
            $ok = $stmt->execute([
                ':id_usuario'      => $id_usuario,
                ':monto'           => $monto,
                ':concepto'        => $concepto,
                ':comprobante_url' => $comprobante_url
            ]);
            return $ok ? (int)$this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Obtener pagos en estado PENDIENTE para revisión/auditoría
    public function obtenerPendientes() {
        try {
            $sql = "SELECT p.*, u.nombres, u.numero_vivienda, u.correo 
                    FROM pagos p
                    LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                    WHERE p.estado = 'PENDIENTE'
                    ORDER BY p.id_pago DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Obtener historial completo de pagos (para Administración / Directiva)
    public function obtenerTodos() {
        try {
            $sql = "SELECT p.*, u.nombres AS usuario_nombre, u.correo 
                    FROM pagos p
                    LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                    ORDER BY p.id_pago DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Obtener pagos asociados a un usuario específico (para el Dashboard del Residente)
    public function obtenerPorUsuario($id_usuario) {
        try {
            $sql = "SELECT * FROM pagos WHERE id_usuario = :id_usuario ORDER BY id_pago DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Cambiar estado de pago (PAGADO / RECHAZADO)
    public function cambiarEstado($id_pago, $nuevoEstado) {
        try {
            $sql = "UPDATE pagos SET estado = :estado WHERE id_pago = :id_pago";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':estado'  => $nuevoEstado,
                ':id_pago' => $id_pago
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>