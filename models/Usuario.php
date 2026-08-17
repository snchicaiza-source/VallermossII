<?php
require_once __DIR__ . '/../config/db.php';

class Usuario {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::obtenerConexion();
    }

    // Busca únicamente en la columna real 'correo'
    public function obtenerPorCorreo($correo) {
        try {
            $sql = "SELECT * FROM usuarios WHERE correo = :correo LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':correo' => trim($correo)]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerTodos() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM usuarios ORDER BY id_usuario DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}