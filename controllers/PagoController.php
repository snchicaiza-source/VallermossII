<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../models/Pago.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA', 'RESIDENTE']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pagoModel = new Pago();

    if ($action === 'procesar_pago') {
        $id_pago = (int)$_POST['id_pago'];
        $estado = $_POST['estado']; // APROBADO o RECHAZADO

        $resultado = $pagoModel->actualizarEstado($id_pago, $estado);

        if ($resultado) {
            $_SESSION['flash_success'] = "El pago #{$id_pago} fue marcado como '{$estado}'.";
        } else {
            $_SESSION['flash_error'] = "No se pudo actualizar el estado del pago.";
        }

        header('Location: ../views/administrador/verificar_pagos.php');
        exit();
    }
}