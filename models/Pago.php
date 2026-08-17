<?php
require_once __DIR__ . '/../config/db.php';

class Pago {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::conectar();
    }

    public function registrar($id_usuario, $monto, $concepto, $comprobante_url) {
        $stmt = $this->pdo->prepare("
            INSERT INTO pagos (id_usuario, monto, concepto, comprobante_url, estado, fecha_registro) 
            VALUES (:id_usuario, :monto, :concepto, :comprobante_url, 'PENDIENTE', NOW())
        ");
        return $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':monto' => $monto,
            ':concepto' => $concepto,
            ':comprobante_url' => $comprobante_url
        ]);
    }

    public function obtenerPendientes() {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.nombres, u.numero_vivienda 
            FROM pagos p 
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario 
            WHERE p.estado = 'PENDIENTE' 
            ORDER BY p.fecha_registro DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorUsuario($id_usuario) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM pagos 
            WHERE id_usuario = :id_usuario 
            ORDER BY fecha_registro DESC
        ");
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstado($id_pago, $estado) {
        $stmt = $this->pdo->prepare("
            UPDATE pagos 
            SET estado = :estado 
            WHERE id_pago = :id_pago
        ");
        return $stmt->execute([
            ':estado' => $estado,
            ':id_pago' => $id_pago
        ]);
    }
}