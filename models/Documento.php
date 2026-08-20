<?php
require_once __DIR__ . '/../config/db.php';

class Documento {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::obtenerConexion();
    }

    public function obtenerTodos() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM documentos_directiva ORDER BY fecha_publicacion DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
