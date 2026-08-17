<?php
require_once __DIR__ . '/../config/db.php';

class Incidencia {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::obtenerConexion();
    }

    public function obtenerPorUsuario($id_usuario) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM incidencias WHERE id_usuario = :id ORDER BY id DESC");
            $stmt->execute([':id' => $id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}