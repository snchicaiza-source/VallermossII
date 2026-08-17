<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear_usuario') {
        $nombres = trim($_POST['nombres'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $rol = trim($_POST['rol'] ?? 'RESIDENTE');
        $puesto_casa = trim($_POST['puesto_casa'] ?? '');

        if (empty($nombres) || empty($email) || empty($password)) {
            header('Location: ../views/administrador/usuarios.php?error=campos_vacios');
            exit;
        }

        try {
            $pdo = Database::obtenerConexion();

            // Verificar si existe la columna puesto_casa
            $stmtCols = $pdo->query("DESCRIBE usuarios");
            $columnas = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $campos = [
                'nombres' => $nombres,
                'email' => $email,
                'password' => $hashedPassword,
                'rol' => $rol
            ];

            if (in_array('puesto_casa', $columnas)) {
                $campos['puesto_casa'] = $puesto_casa;
            }

            $cols = implode(', ', array_keys($campos));
            $params = ':' . implode(', :', array_keys($campos));

            $stmt = $pdo->prepare("INSERT INTO usuarios ({$cols}) VALUES ({$params})");
            $stmt->execute($campos);

            header('Location: ../views/administrador/usuarios.php?msg=creado');
            exit;
        } catch (PDOException $e) {
            header('Location: ../views/administrador/usuarios.php?error=db');
            exit;
        }
    }
}