<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear_convenio') {
        $id_usuario = $_POST['id_usuario'] ?? null;
        $monto_total = $_POST['monto_total'] ?? 0;
        $num_cuotas = $_POST['num_cuotas'] ?? 1;

        if (!$id_usuario || $monto_total <= 0 || $num_cuotas <= 0) {
            header('Location: ../views/administrador/convenios.php?error=datos_invalidos');
            exit;
        }

        try {
            $pdo = Database::obtenerConexion();
            $stmtCols = $pdo->query("DESCRIBE convenios");
            $columnas = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

            $campos = ['id_usuario' => $id_usuario, 'monto_total' => $monto_total];
            if (in_array('num_cuotas', $columnas)) $campos['num_cuotas'] = $num_cuotas;
            if (in_array('estado', $columnas)) $campos['estado'] = 'ACTIVO';

            $cols = implode(', ', array_keys($campos));
            $params = ':' . implode(', :', array_keys($campos));

            $stmt = $pdo->prepare("INSERT INTO convenios ({$cols}) VALUES ({$params})");
            $stmt->execute($campos);

            header('Location: ../views/administrador/convenios.php?msg=convenio_creado');
            exit;
        } catch (PDOException $e) {
            header('Location: ../views/administrador/convenios.php?error=db');
            exit;
        }
    }
}