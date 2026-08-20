<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear_activo') {
        $nombre = trim($_POST['nombre'] ?? '');
        $estado = trim($_POST['estado'] ?? 'BUENO');

        if (empty($nombre)) {
            header('Location: ../views/administrador/activos.php?error=campos_vacios');
            exit;
        }

        try {
            $pdo = Database::obtenerConexion();
            $stmtCols = $pdo->query("DESCRIBE activos");
            $columnas = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

            $campos = ['nombre' => $nombre];
            if (in_array('estado', $columnas)) $campos['estado'] = $estado;

            $cols = implode(', ', array_keys($campos));
            $params = ':' . implode(', :', array_keys($campos));

            $stmt = $pdo->prepare("INSERT INTO activos ({$cols}) VALUES ({$params})");
            $stmt->execute($campos);

            header('Location: ../views/administrador/activos.php?msg=activo_guardado');
            exit;
        } catch (PDOException $e) {
            header('Location: ../views/administrador/activos.php?error=db');
            exit;
        }
    }
}