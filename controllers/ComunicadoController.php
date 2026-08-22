<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/Validador.php';
require_once __DIR__ . '/../models/Logger.php';
require_once __DIR__ . '/../services/WhatsAppService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear_comunicado') {
        $titulo = trim($_POST['titulo'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        // Canales seleccionados mediante checkboxes (WHATSAPP y/o INTERNA)
        $canales = array_values(array_intersect(
            array_map('trim', (array)($_POST['canales'] ?? [])),
            ['WHATSAPP', 'INTERNA']
        ));

        // Validacion de campos (limites de caracteres y obligatorios)
        $errorValidacion = Validador::primerError([
            'titulo'    => Validador::texto($titulo, 'El titulo', 1, 150),
            'contenido' => Validador::texto($contenido, 'El detalle/mensaje', 1, 1000),
        ]);

        if ($errorValidacion !== null) {
            $_SESSION['flash_error'] = $errorValidacion;
            header('Location: ../views/administrador/comunicados.php');
            exit;
        }

        if (empty($canales)) {
            $_SESSION['flash_error'] = "Seleccione al menos un canal de notificación (WhatsApp y/o Notificación interna).";
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
                ':canal' => implode(',', $canales),
                ':enviado_por' => $id_usuario
            ]);

            $idComunicado = (int)$pdo->lastInsertId();

            $whatsappLinks = [];
            $usuariosWA = [];

            if (in_array('WHATSAPP', $canales, true)) {
                $stmtUsers = $pdo->query("SELECT id_usuario, nombres, telefono_whatsapp FROM usuarios WHERE estado = 'ACTIVO' AND telefono_whatsapp IS NOT NULL AND telefono_whatsapp != ''");
                $usuariosWA = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

                $mensajeWA = "VALLERMOSSO II - COMUNICADO OFICIAL\n\n" . $titulo . "\n\n" . $contenido;

                foreach ($usuariosWA as $user) {
                    $resultadoWA = WhatsAppService::enviarMensaje($user['telefono_whatsapp'], $mensajeWA);

                    if ($resultadoWA['exito']) {
                        $logStmt = $pdo->prepare("INSERT INTO notificaciones_log (canal, titulo, destinatario_nombre, destinatario_telefono, estado) VALUES ('WHATSAPP', :titulo, :nombre, :telefono, 'ENVIADO')");
                        $logStmt->execute([':titulo' => $titulo, ':nombre' => $user['nombres'], ':telefono' => $user['telefono_whatsapp']]);
                    } else {
                        // Enlace limpio tipo /ws/vmX7k2: el telefono y mensaje no se exponen en la URL
                        $link = WhatsAppService::registrarEnlaceLimpio($user['telefono_whatsapp'], $titulo, $contenido);
                        $whatsappLinks[] = [
                            'nombre' => $user['nombres'],
                            'telefono' => $user['telefono_whatsapp'],
                            'link' => $link
                        ];

                        $logStmt = $pdo->prepare("INSERT INTO notificaciones_log (canal, titulo, destinatario_nombre, destinatario_telefono, estado) VALUES ('WHATSAPP', :titulo, :nombre, :telefono, 'PENDIENTE')");
                        $logStmt->execute([':titulo' => $titulo, ':nombre' => $user['nombres'], ':telefono' => $user['telefono_whatsapp']]);
                    }
                }
            }

            if (!empty($whatsappLinks)) {
                $_SESSION['whatsapp_links'] = $whatsappLinks;
            }

            // Notificacion interna solo si se selecciono ese canal
            if (in_array('INTERNA', $canales, true)) {
                $stmtAll = $pdo->query("SELECT id_usuario FROM usuarios WHERE estado = 'ACTIVO'");
                $todosUsuarios = $stmtAll->fetchAll(PDO::FETCH_COLUMN);
                $stmtNotif = $pdo->prepare("INSERT INTO notificaciones_usuario (id_usuario, tipo, titulo, mensaje, referencia_id, referencia_tipo, leida, fecha_creacion) VALUES (:uid, 'COMUNICADO', :titulo, :mensaje, :rid, 'comunicado', 0, NOW())");
                foreach ($todosUsuarios as $uid) {
                    $stmtNotif->execute([
                        ':uid' => $uid,
                        ':titulo' => $titulo,
                        ':mensaje' => mb_substr($contenido, 0, 200),
                        ':rid' => $idComunicado
                    ]);
                }
            }

            $msgParts[] = "Comunicado guardado.";
            if (in_array('WHATSAPP', $canales, true)) {
                $waEnviados = count($usuariosWA ?? []) - count($whatsappLinks);
                if ($waEnviados > 0) $msgParts[] = "WhatsApp: {$waEnviados} directos";
                if (count($whatsappLinks) > 0) $msgParts[] = count($whatsappLinks) . " enlaces wa.me";
            }
            if (!in_array('INTERNA', $canales, true)) {
                $msgParts[] = "Sin notificación interna";
            } else {
                $msgParts[] = "notificación interna enviada";
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

                // Captura datos antes de eliminar para el log
                $q = $pdo->prepare("SELECT titulo FROM comunicados WHERE id_comunicado = :id");
                $q->execute([':id' => $id_comunicado]);
                $tituloEliminado = (string)$q->fetchColumn();

                $pdo->prepare("DELETE FROM notificaciones_usuario WHERE referencia_tipo = 'comunicado' AND referencia_id = :id")->execute([':id' => $id_comunicado]);
                $pdo->prepare("DELETE FROM comunicados WHERE id_comunicado = :id")->execute([':id' => $id_comunicado]);

                Logger::eliminacion('Comunicados', "Comunicado #{$id_comunicado} \"{$tituloEliminado}\"");

                $_SESSION['flash_success'] = "Comunicado eliminado.";
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Error al eliminar.";
            }
        }
        header('Location: ../views/administrador/comunicados.php');
        exit();
    }
}
