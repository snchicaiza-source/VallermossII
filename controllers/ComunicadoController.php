<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/WhatsAppService.php';
require_once __DIR__ . '/../services/EmailService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear_comunicado') {
        $titulo = trim($_POST['titulo'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        $canal = $_POST['canal'] ?? 'AMBOS';

        if (empty($titulo) || empty($contenido)) {
            $_SESSION['flash_error'] = "Complete todos los campos.";
            header('Location: ../views/administrador/comunicados.php');
            exit;
        }

        try {
            $pdo = Database::obtenerConexion();
            $id_usuario = $_SESSION['id_usuario'] ?? 1;

            $stmt = $pdo->prepare("INSERT INTO comunicados (titulo, mensaje, canal, enviado_por) VALUES (:titulo, :mensaje, :canal, :enviado_por)");
            $stmt->execute([
                ':titulo' => $titulo,
                ':mensaje' => $contenido,
                ':canal' => $canal,
                ':enviado_por' => $id_usuario
            ]);

            $idComunicado = (int)$pdo->lastInsertId();

            $resultadoEmail = ['enviados' => 0, 'fallidos' => 0];
            $whatsappLinks = [];

            if ($canal === 'EMAIL' || $canal === 'AMBOS') {
                $resultadoEmail = EmailService::enviarComunicadoMasivo($titulo, $contenido);
            }

            if ($canal === 'WHATSAPP' || $canal === 'AMBOS') {
                $stmtUsers = $pdo->query("SELECT id_usuario, nombres, telefono_whatsapp FROM usuarios WHERE estado = 'ACTIVO' AND telefono_whatsapp IS NOT NULL AND telefono_whatsapp != ''");
                $usuariosWA = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

                $mensajeWA = "VALLERMOSSO II - COMUNICADO OFICIAL\n\n" . $titulo . "\n\n" . $contenido;

                foreach ($usuariosWA as $user) {
                    $resultadoWA = WhatsAppService::enviarMensaje($user['telefono_whatsapp'], $mensajeWA);

                    if ($resultadoWA['exito']) {
                        $logStmt = $pdo->prepare("INSERT INTO notificaciones_log (canal, titulo, destinatario_nombre, destinatario_telefono, estado) VALUES ('WHATSAPP', :titulo, :nombre, :telefono, 'ENVIADO')");
                        $logStmt->execute([':titulo' => $titulo, ':nombre' => $user['nombres'], ':telefono' => $user['telefono_whatsapp']]);
                    } else {
                        $link = WhatsAppService::generarEnlaceDirecto($user['telefono_whatsapp'], $titulo, $contenido);
                        $whatsappLinks[] = [
                            'nombre' => $user['nombres'],
                            'telefono' => $user['telefono_whatsapp'],
                            'link' => $link
                        ];

                        $logStmt = $pdo->prepare("INSERT INTO notificaciones_log (canal, titulo, destinatario_nombre, destinatario_telefono, estado) VALUES ('WHATSAPP', :titulo, :nombre, :telefono, 'PENDIENTE')");
                        $logStmt->execute([':titulo' => $titulo, ':nombre' => $user['nombres'], ':telefono' => $user['telefono_whatsapp']]);
                    }
                }

                if (!empty($whatsappLinks)) {
                    $_SESSION['whatsapp_links'] = $whatsappLinks;
                }
            }

            // Crear notificacion personalizada para cada usuario activo
            $stmtAll = $pdo->query("SELECT id_usuario FROM usuarios WHERE estado = 'ACTIVO'");
            $todosUsuarios = $stmtAll->fetchAll(PDO::FETCH_COLUMN);
            $stmtNotif = $pdo->prepare("INSERT INTO notificaciones_usuario (id_usuario, tipo, titulo, mensaje, referencia_id, referencia_tipo, leida, fecha_creacion) VALUES (:uid, 'COMUNICADO', :titulo, :mensaje, :rid, 'comunicado', 0, NOW())");
            foreach ($todosUsuarios as $uid) {
                $stmtNotif->execute([
                    ':uid' => $uid,
                    ':titulo' => $titulo,
                    ':mensaje' => substr($contenido, 0, 200),
                    ':rid' => $idComunicado
                ]);
            }

            $msgParts[] = "Comunicado guardado.";
            if (($canal === 'EMAIL' || $canal === 'AMBOS') && $resultadoEmail['enviados'] > 0) {
                $msgParts[] = "Email: {$resultadoEmail['enviados']} enviados";
            }
            if (($canal === 'EMAIL' || $canal === 'AMBOS') && $resultadoEmail['fallidos'] > 0) {
                $msgParts[] = "{$resultadoEmail['fallidos']} correos fallidos";
            }
            if (($canal === 'EMAIL' || $canal === 'AMBOS') && $resultadoEmail['enviados'] === 0 && $resultadoEmail['fallidos'] === 0) {
                $msgParts[] = "Email: no configurado (SMTP deshabilitado)";
            }
            if ($canal === 'WHATSAPP' || $canal === 'AMBOS') {
                $waEnviados = count($usuariosWA ?? []) - count($whatsappLinks);
                if ($waEnviados > 0) $msgParts[] = "WhatsApp: {$waEnviados} directos";
                if (count($whatsappLinks) > 0) $msgParts[] = count($whatsappLinks) . " enlaces wa.me";
            }

            $_SESSION['flash_success'] = "Comunicado publicado. " . implode(' | ', $msgParts);
            header('Location: ../views/administrador/comunicados.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Error al publicar: " . $e->getMessage();
            header('Location: ../views/administrador/comunicados.php');
            exit;
        }
    }

    if ($action === 'eliminar_comunicado') {
        $id_comunicado = (int)($_POST['id_comunicado'] ?? 0);
        if ($id_comunicado > 0) {
            try {
                $pdo = Database::obtenerConexion();
                $pdo->prepare("DELETE FROM notificaciones_usuario WHERE referencia_tipo = 'comunicado' AND referencia_id = :id")->execute([':id' => $id_comunicado]);
                $pdo->prepare("DELETE FROM comunicados WHERE id_comunicado = :id")->execute([':id' => $id_comunicado]);
                $_SESSION['flash_success'] = "Comunicado eliminado.";
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Error al eliminar.";
            }
        }
        header('Location: ../views/administrador/comunicados.php');
        exit();
    }
}
