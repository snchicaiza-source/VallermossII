<?php
require_once __DIR__ . '/../config/db.php';

class Presupuesto {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::obtenerConexion();
    }

    public function obtenerResumenAnual() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM presupuesto_ejecucion ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}