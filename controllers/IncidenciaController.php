<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear_reporte') {
        $id_usuario = $_SESSION['id_usuario'] ?? null;
        $tipo = trim($_POST['tipo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!$id_usuario || empty($tipo) || empty($descripcion)) {
            header('Location: ../views/residente/reportar_danos.php?error=campos_vacios');
            exit;
        }

        try {
            $pdo = Database::obtenerConexion();
            
            // Detección flexible de estructura
            $stmtCols = $pdo->query("DESCRIBE incidencias");
            $columnas = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

            $campos = ['id_usuario' => $id_usuario, 'descripcion' => $descripcion];
            if (in_array('tipo', $columnas)) $campos['tipo'] = $tipo;
            if (in_array('estado', $columnas)) $campos['estado'] = 'PENDIENTE';

            $cols = implode(', ', array_keys($campos));
            $params = ':' . implode(', :', array_keys($campos));

            $stmt = $pdo->prepare("INSERT INTO incidencias ({$cols}) VALUES ({$params})");
            $stmt->execute($campos);

            header('Location: ../views/residente/reportar_danos.php?msg=reporte_enviado');
            exit;
        } catch (PDOException $e) {
            header('Location: ../views/residente/reportar_danos.php?error=db');
            exit;
        }
    }
}