<?php
require_once __DIR__ . '/../config/db.php';

class Presupuesto {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::obtenerConexion();
    }

    public function obtenerResumenAnual() {
        try {
            $stmt = $this->pdo->query("SELECT id_presupuesto as id, rubro as concepto, monto_asignado, monto_ejecutado, ROUND((monto_ejecutado / monto_asignado) * 100, 1) as porcentaje FROM presupuesto ORDER BY id_presupuesto DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
