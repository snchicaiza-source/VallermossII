<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../models/Pago.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

$pagoModel = new Pago();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'aprobar') {
        $id_pago = (int)($_POST['id_pago'] ?? 0);
        if ($id_pago > 0) {
            $resultado = $pagoModel->cambiarEstado($id_pago, 'PAGADO');
            if ($resultado) {
                $_SESSION['flash_success'] = "Pago #{$id_pago} aprobado correctamente.";
            } else {
                $_SESSION['flash_error'] = "No se pudo aprobar el pago.";
            }
        }
        header('Location: ../views/administrador/verificar_pagos.php');
        exit();
    }

    if ($action === 'rechazar') {
        $id_pago = (int)($_POST['id_pago'] ?? 0);
        if ($id_pago > 0) {
            $resultado = $pagoModel->cambiarEstado($id_pago, 'RECHAZADO');
            if ($resultado) {
                $_SESSION['flash_success'] = "Pago #{$id_pago} rechazado.";
            } else {
                $_SESSION['flash_error'] = "No se pudo rechazar el pago.";
            }
        }
        header('Location: ../views/administrador/verificar_pagos.php');
        exit();
    }
}
