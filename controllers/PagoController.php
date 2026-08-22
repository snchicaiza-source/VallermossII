<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../models/Pago.php';
require_once __DIR__ . '/../models/Notificacion.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

$pagoModel = new Pago();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'aprobar' || $action === 'rechazar') {
        $id_pago = (int)($_POST['id_pago'] ?? 0);
        if ($id_pago > 0) {
            $nuevoEstado = ($action === 'aprobar') ? 'PAGADO' : 'RECHAZADO';
            $resultado = $pagoModel->cambiarEstado($id_pago, $nuevoEstado);

            if ($resultado) {
                $_SESSION['flash_success'] = "Pago #{$id_pago} " . ($action === 'aprobar' ? 'aprobado' : 'rechazado') . " correctamente.";

                // Notifica al residente dueno del pago
                try {
                    $pdo = Database::obtenerConexion();

                    // Al aprobar, genera el recibo de pago automaticamente si no existe
                    if ($action === 'aprobar') {
                        try {
                            $pdo->exec("CREATE TABLE IF NOT EXISTS recibos_pago (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                id_pago INT NOT NULL,
                                id_usuario INT NOT NULL,
                                numero_recibo VARCHAR(20) NOT NULL,
                                monto_pagado DECIMAL(10,2) NOT NULL,
                                concepto VARCHAR(150) NOT NULL,
                                fecha_emision TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                            $existe = $pdo->prepare("SELECT id FROM recibos_pago WHERE id_pago = :id");
                            $existe->execute([':id' => $id_pago]);
                            if (!$existe->fetch()) {
                                $stmt = $pdo->prepare("SELECT id_usuario, concepto, monto FROM pagos WHERE id_pago = :id");
                                $stmt->execute([':id' => $id_pago]);
                                if ($p = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $ins = $pdo->prepare("INSERT INTO recibos_pago (id_pago, id_usuario, numero_recibo, monto_pagado, concepto) VALUES (:p, :u, :n, :m, :c)");
                                    $ins->execute([
                                        ':p' => $id_pago,
                                        ':u' => (int)$p['id_usuario'],
                                        ':n' => 'REC-' . str_pad((string)$id_pago, 6, '0', STR_PAD_LEFT),
                                        ':m' => (float)$p['monto'],
                                        ':c' => $p['concepto']
                                    ]);
                                }
                            }
                        } catch (PDOException $e) { /* el recibo no interrumpe el flujo */ }
                    }

                    $stmt = $pdo->prepare("SELECT id_usuario, concepto, monto FROM pagos WHERE id_pago = :id");
                    $stmt->execute([':id' => $id_pago]);
                    if ($pago = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $mensajeAprobacion = $action === 'aprobar'
                            ? "El pago \"{$pago['concepto']}\" por $" . number_format((float)$pago['monto'], 2) . " fue aprobado por la administración. Ya puedes descargar tu recibo en el módulo Recibos de Pago."
                            : "El pago \"{$pago['concepto']}\" por $" . number_format((float)$pago['monto'], 2) . " fue rechazado por la administración.";
                        Notificacion::enviar(
                            (int)$pago['id_usuario'],
                            'PAGO',
                            $action === 'aprobar' ? 'Tu pago fue aprobado' : 'Tu pago fue rechazado',
                            $mensajeAprobacion,
                            $id_pago,
                            'pago'
                        );
                    }
                } catch (PDOException $e) { /* la notificacion no interrumpe el flujo */ }
            } else {
                $_SESSION['flash_error'] = "No se pudo " . ($action === 'aprobar' ? 'aprobar' : 'rechazar') . " el pago.";
            }
        }
        header('Location: ../views/administrador/verificar_pagos.php');
        exit();
    }
}
