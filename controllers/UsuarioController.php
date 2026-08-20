<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear_usuario') {
        $cedula = trim($_POST['cedula'] ?? '');
        $nombres = trim($_POST['nombres'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono_whatsapp'] ?? '');
        $numero_vivienda = trim($_POST['numero_vivienda'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $rol = trim($_POST['rol'] ?? 'RESIDENTE');

        if (empty($cedula) || empty($nombres) || empty($correo) || empty($password)) {
            header('Location: ../views/administrador/usuarios.php?error=campos_vacios');
            exit;
        }

        try {
            $pdo = Database::obtenerConexion();
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (cedula, nombres, correo, telefono_whatsapp, numero_vivienda, clave_hash, rol) VALUES (:cedula, :nombres, :correo, :telefono, :numero_vivienda, :clave_hash, :rol)");
            $stmt->execute([
                ':cedula' => $cedula,
                ':nombres' => $nombres,
                ':correo' => $correo,
                ':telefono' => $telefono,
                ':numero_vivienda' => $numero_vivienda,
                ':clave_hash' => $hashedPassword,
                ':rol' => $rol
            ]);

            header('Location: ../views/administrador/usuarios.php?msg=creado');
            exit;
        } catch (PDOException $e) {
            header('Location: ../views/administrador/usuarios.php?error=db');
            exit;
        }
    }

    if ($action === 'actualizar_perfil') {
        $id_usuario = $_SESSION['id_usuario'] ?? 0;
        $nombres = trim($_POST['nombres'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono_whatsapp'] ?? '');

        if ($id_usuario > 0 && !empty($nombres) && !empty($correo)) {
            try {
                $pdo = Database::obtenerConexion();
                $stmt = $pdo->prepare("UPDATE usuarios SET nombres = :nombres, correo = :correo, telefono_whatsapp = :telefono WHERE id_usuario = :id");
                $stmt->execute([
                    ':nombres' => $nombres,
                    ':correo' => $correo,
                    ':telefono' => $telefono,
                    ':id' => $id_usuario
                ]);

                $_SESSION['nombres'] = $nombres;
                $_SESSION['usuario_nombres'] = $nombres;
                $_SESSION['correo'] = $correo;
                $_SESSION['usuario_correo'] = $correo;

                $_SESSION['flash_success'] = "Perfil actualizado correctamente.";
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Error al actualizar el perfil.";
            }
        }
        header('Location: ../views/residente/perfil.php');
        exit;
    }
}
