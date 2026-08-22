<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['DIRECTIVA']);

$db = Database::obtenerConexion();

$stmtComunicados = $db->query("SELECT c.*, u.nombres as autor FROM comunicados c LEFT JOIN usuarios u ON c.enviado_por = u.id_usuario ORDER BY c.fecha_envio DESC");
$comunicados = $stmtComunicados->fetchAll(PDO::FETCH_ASSOC);

$stmtLog = $db->query("SELECT * FROM notificaciones_log ORDER BY fecha_envio DESC LIMIT 50");
$notificacionesLog = $stmtLog->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-bell"></i> Notificaciones y Comunicados</h1>
            <p class="subtitle">Envio de comunicados y seguimiento de notificaciones a los residentes.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['whatsapp_links'])): ?>
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-brands fa-whatsapp"></i> Enlaces de WhatsApp para Enviar</h2>
            </div>
            <div class="card-body">
                <p style="margin-bottom: 12px; color: var(--text-muted); font-size: 0.88rem;">Haga clic en cada enlace para abrir WhatsApp y enviar el mensaje al residente:</p>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>Residente</th><th>Teléfono</th><th>Acción</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['whatsapp_links'] as $link): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($link['nombre']) ?></strong></td>
                                <td><?= htmlspecialchars($link['telefono']) ?></td>
                                <td><a href="<?= $link['link'] ?>" target="_blank" class="btn btn-sm btn-success"><i class="fa-brands fa-whatsapp"></i> Enviar WhatsApp</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php unset($_SESSION['whatsapp_links']); ?>
            </div>
        </section>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-pen-to-square"></i> Crear Nuevo Comunicado</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/ComunicadoController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_comunicado">

                    <div class="form-group span-full">
                        <label for="titulo"><strong>Título del Comunicado</strong></label>
                        <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ej. Convocatoria a Asamblea General Extraordinaria" required>
                    </div>

                    <div class="form-group">
                        <label for="canal"><strong>Canal de Notificación</strong></label>
                        <select id="canal" name="canal" class="form-control" required>
                            <option value="AMBOS">Correo + WhatsApp</option>
                            <option value="EMAIL">Solo Correo Electrónico</option>
                            <option value="WHATSAPP">Solo WhatsApp</option>
                        </select>
                    </div>

                    <div class="form-group span-full">
                        <label for="contenido"><strong>Detalle / Mensaje</strong></label>
                        <textarea id="contenido" name="contenido" class="form-control" rows="5" placeholder="Escriba aquí los detalles del aviso..." required></textarea>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Publicar y Notificar</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-newspaper"></i> Comunicados Emitidos</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Título</th>
                                <th>Canal</th>
                                <th>Publicado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($comunicados)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay comunicados registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($comunicados as $c): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($c['fecha_envio'])) ?></td>
                                        <td><strong><?= htmlspecialchars($c['titulo']) ?></strong></td>
                                        <td>
                                            <?php if ($c['canal'] === 'AMBOS'): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-envelope"></i> <i class="fa-brands fa-whatsapp"></i> Ambos</span>
                                            <?php elseif ($c['canal'] === 'EMAIL'): ?>
                                                <span class="badge badge-info"><i class="fa-solid fa-envelope"></i> Email</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning"><i class="fa-brands fa-whatsapp"></i> WhatsApp</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($c['autor'] ?? 'Directiva') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-clock-rotate-left"></i> Historial de Notificaciones Enviadas</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Canal</th>
                                <th>Título</th>
                                <th>Destinatario</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($notificacionesLog)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay notificaciones enviadas registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($notificacionesLog as $log): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($log['fecha_envio'])) ?></td>
                                        <td>
                                            <?php if ($log['canal'] === 'EMAIL'): ?>
                                                <span class="badge badge-info"><i class="fa-solid fa-envelope"></i> Email</span>
                                            <?php elseif ($log['canal'] === 'WHATSAPP'): ?>
                                                <span class="badge badge-success"><i class="fa-brands fa-whatsapp"></i> WhatsApp</span>
                                            <?php else: ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-bell"></i> <?= htmlspecialchars($log['canal']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($log['titulo']) ?></strong></td>
                                        <td><?= htmlspecialchars($log['destinatario_nombre']) ?></td>
                                        <td><?= htmlspecialchars($log['destinatario_correo'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($log['destinatario_telefono'] ?? '-') ?></td>
                                        <td>
                                            <?php
                                                $badgeLog = 'badge-warning';
                                                if ($log['estado'] === 'ENVIADO') $badgeLog = 'badge-success';
                                                elseif ($log['estado'] === 'ERROR') $badgeLog = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badgeLog ?>"><?= htmlspecialchars($log['estado']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="../../public/js/sidebar.js"></script>
</body>
</html>
