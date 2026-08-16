<?php
// models/Usuario.php
require_once __DIR__ . '/../config/db.php';

class Usuario {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    // Método requerido para el Inicio de Sesión (AuthController)
    public function obtenerPorCorreo($correo) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE correo = :correo LIMIT 1");
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetch();
    }

    // Listar todos los usuarios del sistema (Panel Admin)
    public function obtenerTodos() {
        $stmt = $this->db->query("SELECT id_usuario, nombres, correo, rol, numero_vivienda, estado, created_at FROM usuarios ORDER BY id_usuario DESC");
        return $stmt->fetchAll();
    }

    // Registrar nuevo usuario (Residente, Directiva o Administrador)
    public function registrar($nombres, $correo, $password, $rol, $numero_vivienda) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nombres, correo, clave_hash, rol, numero_vivienda, estado)
            VALUES (:nombres, :correo, :clave_hash, :rol, :numero_vivienda, 'ACTIVO')
        ");
        return $stmt->execute([
            ':nombres'         => $nombres,
            ':correo'          => $correo,
            ':clave_hash'      => $hash,
            ':rol'             => $rol,
            ':numero_vivienda' => $numero_vivienda
        ]);
    }

    // Cambiar estado (Bloquear / Activar)
    public function cambiarEstado($id_usuario, $nuevo_estado) {
        $stmt = $this->db->prepare("UPDATE usuarios SET estado = :estado WHERE id_usuario = :id_usuario");
        return $stmt->execute([
            ':estado'     => $nuevo_estado,
            ':id_usuario' => $id_usuario
        ]);
    }
}
?>