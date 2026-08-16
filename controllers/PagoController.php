<?php
// controllers/PagoController.php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../models/Pago.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $id_pago = $_POST['id_pago'] ?? null;

    if ($id_pago && in_array($action, ['aprobar', 'rechazar'])) {
        $nuevoEstado = ($action === 'aprobar') ? 'PAGADO' : 'PENDIENTE';
        
        $pagoModel = new Pago();
        $pagoModel->cambiarEstado($id_pago, $nuevoEstado);

        if ($action === 'aprobar') {
            $_SESSION['flash_success'] = "El comprobante ha sido APROBADO correctamente.";
        } else {
            $_SESSION['flash_error'] = "El comprobante fue RECHAZADO y la alícuota volvió a estado PENDIENTE.";
        }
    }

    header("Location: ../views/administrador/verificar_pagos.php");
    exit();
} else {
    header("Location: ../views/administrador/verificar_pagos.php");
    exit();
}
?>