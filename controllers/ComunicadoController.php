<?php
// controllers/ComunicadoController.php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../models/Comunicado.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../services/WhatsAppService.php';

// Seguridad: Verificar sesión y roles autorizados
verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo  = trim($_POST['titulo'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $canal   = $_POST['canal'] ?? 'AMBOS';

    if (empty($titulo) || empty($mensaje)) {
        $_SESSION['flash_error'] = "Todos los campos son obligatorios.";
        header("Location: ../views/administrador/comunicados.php");
        exit();
    }

    $comunicadoModel = new Comunicado();
    $id_usuario = $_SESSION['usuario_id'];

    // Guardar en Base de Datos
    $resultado = $comunicadoModel->crear($titulo, $mensaje, $canal, $id_usuario);

    if ($resultado) {
        $_SESSION['flash_success'] = "El comunicado '{$titulo}' ha sido registrado exitosamente.";
        
        if ($canal === 'WHATSAPP' || $canal === 'AMBOS') {
            $_SESSION['ultimo_comunicado'] = [
                'titulo'  => $titulo,
                'mensaje' => $mensaje
            ];
        }
    } else {
        $_SESSION['flash_error'] = "Ocurrió un error al guardar el comunicado en el sistema.";
    }

    // Redirección con ruta relativa segura
    header("Location: ../views/administrador/comunicados.php");
    exit();
} else {
    header("Location: ../views/administrador/comunicados.php");
    exit();
}
?>