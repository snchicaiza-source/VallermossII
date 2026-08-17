<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear_comunicado') {
        $titulo = trim($_POST['titulo'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        $prioridad = $_POST['prioridad'] ?? 'INFORMATIVO';
        
        $enviarEmail = isset($_POST['enviar_email']);
        $enviarWhatsapp = isset($_POST['enviar_whatsapp']);

        if (empty($titulo) || empty($contenido)) {
            header('Location: ../views/administrador/comunicados.php?error=campos_vacios');
            exit;
        }

        try {
            $pdo = Database::obtenerConexion();
            
            // 1. Guardar en la Base de Datos
            $stmt = $pdo->prepare("INSERT INTO comunicados (titulo, contenido, prioridad) VALUES (:titulo, :contenido, :prioridad)");
            $stmt->execute([
                ':titulo' => $titulo,
                ':contenido' => $contenido,
                ':prioridad' => $prioridad
            ]);

            // 2. Obtener lista de usuarios activos
            $stmtUsers = $pdo->query("SELECT email, telefono, nombres FROM usuarios WHERE estado = 'ACTIVO'");
            $usuarios = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            // 3. Enviar por Correo Electrónico (PHPMailer / mail native)
            if ($enviarEmail) {
                foreach ($usuarios as $user) {
                    if (!empty($user['email'])) {
                        $to = $user['email'];
                        $subject = "COMUNICADO VALLERMOSSO II: " . $titulo;
                        $message = "Estimado(a) " . $user['nombres'] . ",\n\n" . $contenido . "\n\nAtentamente,\nAdministración Vallermosso II";
                        $headers = "From: administracion@vallermosso.com\r\nReply-To: administracion@vallermosso.com";
                        
                        @mail($to, $subject, $message, $headers);
                    }
                }
            }

            // 4. Enviar por WhatsApp (Ejemplo de integración con API externa / UltraMsg / Twilio)
            if ($enviarWhatsapp) {
                foreach ($usuarios as $user) {
                    if (!empty($user['telefono'])) {
                        enviarNotificacionWhatsApp($user['telefono'], "*VALLERMOSSO II*\n\n*" . $titulo . "*\n\n" . $contenido);
                    }
                }
            }

            header('Location: ../views/administrador/comunicados.php?msg=publicado');
            exit;
        } catch (PDOException $e) {
            header('Location: ../views/administrador/comunicados.php?error=db');
            exit;
        }
    }
}

// Función auxiliar para integrar servicio de WhatsApp (Twilio/WATI/UltraMsg)
function enviarNotificacionWhatsApp($numero, $mensaje) {
    // Ejemplo de llamada HTTP a API de WhatsApp
    $apiUrl = "https://api.ultramsg.com/INSTANCE_ID/messages/chat";
    $token = "YOUR_TOKEN";

    $data = [
        'token' => $token,
        'to' => $numero,
        'body' => $mensaje
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}