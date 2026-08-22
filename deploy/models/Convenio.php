<?php
require_once __DIR__ . '/../config/db.php';

class Convenio {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::obtenerConexion();
    }

    public function obtenerTodos() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM convenios ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}