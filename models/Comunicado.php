<?php
// models/Comunicado.php
require_once __DIR__ . '/../config/db.php';

class Comunicado {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    // Registrar un nuevo comunicado en la BD
    public function crear($titulo, $mensaje, $canal, $enviado_por) {
        $stmt = $this->db->prepare("
            INSERT INTO comunicados (titulo, mensaje, canal, enviado_por) 
            VALUES (:titulo, :mensaje, :canal, :enviado_por)
        ");
        return $stmt->execute([
            ':titulo'      => $titulo,
            ':mensaje'     => $mensaje,
            ':canal'       => $canal,
            ':enviado_por' => $enviado_por
        ]);
    }

    // Listar historial de comunicados
    public function obtenerTodos() {
        $stmt = $this->db->query("
            SELECT c.*, u.nombres as remitente 
            FROM comunicados c
            JOIN usuarios u ON c.enviado_por = u.id_usuario
            ORDER BY c.fecha_envio DESC
        ");
        return $stmt->fetchAll();
    }
}
?>