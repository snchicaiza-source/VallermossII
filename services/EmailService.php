<?php
// services/EmailService.php
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {

    private static $config = null;

    private static function getConfig() {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../config/mail_config.php';
        }
        return self::$config;
    }

    /**
     * Enviar correo individual
     */
    public static function enviar($para, $asunto, $mensajeHTML) {
        $config = self::getConfig();

        if (isset($config['habilitado']) && !$config['habilitado']) {
            return ['exito' => false, 'mensaje' => 'Sistema de correo deshabilitado. Active mail_config[habilitado].'];
        }

        if (empty($config['smtp_user']) || $config['smtp_user'] === 'tu_correo@gmail.com') {
            return ['exito' => false, 'mensaje' => 'SMTP no configurado. Edite config/mail_config.php con credenciales validas.'];
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $config['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['smtp_user'];
            $mail->Password   = $config['smtp_pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $config['smtp_port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($para);
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $mensajeHTML;
            $mail->AltBody = strip_tags($mensajeHTML);

            $mail->send();
            return ['exito' => true, 'mensaje' => 'Correo enviado'];
        } catch (Exception $e) {
            return ['exito' => false, 'mensaje' => 'Error: ' . $mail->ErrorInfo];
        }
    }

    /**
     * Enviar comunicado masivo por correo
     */
    public static function enviarComunicadoMasivo($titulo, $contenido) {
        $config = self::getConfig();
        $pdo = Database::obtenerConexion();

        $stmt = $pdo->query("SELECT nombres, correo FROM usuarios WHERE estado = 'ACTIVO' AND correo IS NOT NULL AND correo != ''");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $enviados = 0;
        $fallidos = 0;
        $detalles = [];

        $htmlBody = self::templateComunicado($titulo, $contenido);

        foreach ($usuarios as $user) {
            if (empty($user['correo'])) continue;

            $resultado = self::enviar($user['correo'], "VALLERMOSSO II - " . $titulo, $htmlBody);

            // Log
            $logStmt = $pdo->prepare("INSERT INTO notificaciones_log (canal, titulo, destinatario_nombre, destinatario_correo, estado, error_detalle) VALUES ('EMAIL', :titulo, :nombre, :correo, :estado, :error)");
            $logStmt->execute([
                ':titulo' => $titulo,
                ':nombre' => $user['nombres'],
                ':correo' => $user['correo'],
                ':estado' => $resultado['exito'] ? 'ENVIADO' : 'FALLIDO',
                ':error' => $resultado['exito'] ? null : $resultado['mensaje']
            ]);

            if ($resultado['exito']) {
                $enviados++;
            } else {
                $fallidos++;
                $detalles[] = $user['nombres'] . ': ' . $resultado['mensaje'];
            }
        }

        return ['enviados' => $enviados, 'fallidos' => $fallidos, 'total' => count($usuarios), 'detalles' => $detalles];
    }

    /**
     * Template HTML para comunicados
     */
    private static function templateComunicado($titulo, $contenido) {
        return '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="margin:0; padding:0; background:#F7F5F0; font-family:Arial,sans-serif;">
        <div style="max-width:600px; margin:20px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08);">
            <div style="background:#2E2A27; color:#fff; padding:24px; text-align:center;">
                <h1 style="margin:0; font-size:22px; letter-spacing:2px;">VALLERMOSSO II</h1>
                <p style="margin:4px 0 0; font-size:12px; color:#C8BFB4;">Conjunto Habitacional</p>
            </div>
            <div style="padding:28px;">
                <h2 style="color:#7D6B56; margin:0 0 16px; font-size:18px;">' . htmlspecialchars($titulo) . '</h2>
                <div style="color:#36322E; line-height:1.7; font-size:14px;">' . nl2br(htmlspecialchars($contenido)) . '</div>
                <hr style="border:none; border-top:1px solid #D8CFC4; margin:24px 0;">
                <p style="color:#7A7268; font-size:11px; margin:0;">Este es un mensaje automatico del sistema de gestion del Conjunto Vallermosso II.</p>
            </div>
        </div>
        </body>
        </html>';
    }
}
