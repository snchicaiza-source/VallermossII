<?php
// ws.php - Redireccionador interno de enlaces de WhatsApp
// Uso: /ws/CODIGO (con .htaccess) o ws.php?c=CODIGO
// El servidor busca el codigo en la BD y redirige al enlace completo de WhatsApp
// sin que el usuario vea el telefono ni el mensaje en la URL original.
require_once __DIR__ . '/config/db.php';

$codigo = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['c'] ?? '');

function paginaError($titulo, $mensaje) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . htmlspecialchars($titulo) . '</title>';
    echo '<style>body{font-family:Segoe UI,Arial,sans-serif;background:#F7F5F0;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}';
    echo '.box{background:#fff;border:1px solid #D8CFC4;border-radius:12px;padding:40px;text-align:center;max-width:420px;box-shadow:0 8px 24px rgba(0,0,0,.08);}';
    echo '.box i{font-size:3rem;color:#A38F78;margin-bottom:16px;}h1{font-size:1.2rem;color:#36322E;margin:0 0 8px;}p{color:#7A7268;font-size:.9rem;margin:0;}</style></head>';
    echo '<body><div class="box"><i class="fa-brands fa-whatsapp"></i><h1>' . htmlspecialchars($titulo) . '</h1><p>' . htmlspecialchars($mensaje) . '</p></div></body></html>';
    exit;
}

if ($codigo === '') {
    paginaError('Enlace no valido', 'Falta el codigo del enlace de WhatsApp.');
}

try {
    $pdo = Database::obtenerConexion();

    // Auto-reparacion: crea la tabla si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS enlaces_whatsapp (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(32) NOT NULL UNIQUE,
        telefono VARCHAR(20) NOT NULL,
        mensaje TEXT,
        activo TINYINT(1) DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $pdo->prepare("SELECT telefono, mensaje FROM enlaces_whatsapp WHERE codigo = :c AND activo = 1 LIMIT 1");
    $stmt->execute([':c' => $codigo]);
    $enlace = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$enlace) {
        paginaError('Enlace no encontrado', 'Este enlace no existe o fue desactivado.');
    }

    $texto = $enlace['mensaje'] ?: '';
    header('Location: https://wa.me/' . $enlace['telefono'] . '?text=' . urlencode($texto), true, 302);
    exit;

} catch (PDOException $e) {
    paginaError('Error del sistema', 'No se pudo procesar el enlace. Intenta mas tarde.');
}
