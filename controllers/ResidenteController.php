<?php
// controllers/ResidenteController.php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../models/Pago.php';

// Garantizar que solo los residentes accedan
verificarRol(['RESIDENTE']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'subir_comprobante') {
        $id_pago = $_POST['id_pago'] ?? null;
        $id_usuario = $_SESSION['usuario_id'];

        if (!$id_pago || !isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = "Por favor seleccione un archivo válido (Imagen o PDF).";
            header("Location: ../views/residente/dashboard.php");
            exit();
        }

        $file = $_FILES['comprobante'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['flash_error'] = "Formato no permitido. Solo se aceptan imágenes (JPG, PNG) o PDF.";
            header("Location: ../views/residente/dashboard.php");
            exit();
        }

        // Crear carpeta de subidas si no existe
        $uploadDir = __DIR__ . '/../public/uploads/comprobantes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generar nombre único para el archivo
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'voucher_' . $id_usuario . '_' . $id_pago . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $pagoModel = new Pago();
            $pagoModel->registrarComprobante($id_pago, $id_usuario, 'public/uploads/comprobantes/' . $fileName);

            $_SESSION['flash_success'] = "¡Comprobante subido exitosamente! Está en revisión por la administración.";
        } else {
            $_SESSION['flash_error'] = "Error al guardar el archivo en el servidor.";
        }

        header("Location: ../views/residente/dashboard.php");
        exit();
    }
} else {
    header("Location: ../views/residente/dashboard.php");
    exit();
}
?>