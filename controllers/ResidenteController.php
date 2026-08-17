<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../models/Pago.php';

verificarRol(['RESIDENTE']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'subir_pago') {
        $monto = (float)$_POST['monto'];
        $concepto = trim($_POST['concepto']);
        $id_usuario = $_SESSION['id_usuario'];

        $comprobante_url = '';

        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
            $filename = 'pago_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $destination = __DIR__ . '/../public/uploads/' . $filename;

            if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $destination)) {
                $comprobante_url = $filename;
            }
        }

        $pagoModel = new Pago();
        $resultado = $pagoModel->registrar($id_usuario, $monto, $concepto, $comprobante_url);

        if ($resultado) {
            $_SESSION['flash_success'] = "Tu pago ha sido enviado correctamente a revisión.";
        } else {
            $_SESSION['flash_error'] = "Error al guardar el pago en la base de datos.";
        }

        header('Location: ../views/residente/dashboard.php');
        exit();
    }
}