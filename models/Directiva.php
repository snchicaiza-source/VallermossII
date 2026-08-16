<?php
// models/Directiva.php
require_once __DIR__ . '/../config/db.php';

class Directiva {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function obtenerPresupuesto() {
        $stmt = $this->db->query("SELECT * FROM presupuesto");
        return $stmt->fetchAll();
    }

    public function obtenerProveedores() {
        $stmt = $this->db->query("SELECT * FROM proveedores ORDER BY estado_pago DESC");
        return $stmt->fetchAll();
    }

    public function obtenerDocumentos() {
        $stmt = $this->db->query("SELECT * FROM documentos_directiva ORDER BY fecha_publicacion DESC");
        return $stmt->fetchAll();
    }
}
?>