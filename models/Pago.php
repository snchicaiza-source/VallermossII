<?php
// models/Pago.php
require_once __DIR__ . '/../config/db.php';

class Pago {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    // Obtener historial de alícuotas / pagos de un residente
    public function obtenerPorUsuario($id_usuario) {
        $stmt = $this->db->prepare("
            SELECT * FROM pagos 
            WHERE id_usuario = :id_usuario 
            ORDER BY fecha_vencimiento DESC
        ");
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetchAll();
    }

    // Registrar comprobante subido por residente
    public function registrarComprobante($id_pago, $id_usuario, $ruta_comprobante) {
        $stmt = $this->db->prepare("
            UPDATE pagos 
            SET comprobante_url = :comprobante, 
                estado = 'EN_REVISION', 
                fecha_subida = NOW() 
            WHERE id_pago = :id_pago AND id_usuario = :id_usuario
        ");
        return $stmt->execute([
            ':comprobante' => $ruta_comprobante,
            ':id_pago'     => $id_pago,
            ':id_usuario'  => $id_usuario
        ]);
    }

    // Listar todos los pagos para la Administración (filtrable por estado)
    public function obtenerTodosConUsuario() {
        $stmt = $this->db->query("
            SELECT p.*, u.nombres, u.numero_vivienda, u.correo 
            FROM pagos p
            JOIN usuarios u ON p.id_usuario = u.id_usuario
            ORDER BY p.fecha_subida DESC, p.fecha_vencimiento DESC
        ");
        return $stmt->fetchAll();
    }

    // Aprobar o rechazar pago
    public function cambiarEstado($id_pago, $nuevo_estado) {
        $stmt = $this->db->prepare("
            UPDATE pagos 
            SET estado = :estado 
            WHERE id_pago = :id_pago
        ");
        return $stmt->execute([
            ':estado'  => $nuevo_estado,
            ':id_pago' => $id_pago
        ]);
    }
}
?>