<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

$pdo = Database::obtenerConexion();
$stmt = $pdo->query("SELECT c.*, u.nombres as autor FROM comunicados c LEFT JOIN usuarios u ON c.enviado_por = u.id_usuario ORDER BY c.fecha_envio DESC");
$comunicados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunicados - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-bullhorn"></i> Gestion de Comunicados y Avisos</h1>
            <p class="subtitle">Publica boletines, informativos y alertas para la comunidad del condominio.</p>
        </header>


        <!-- Mensajes Flash -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'campos_vacios'): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i> Por favor complete todos los campos obligatorios.
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['whatsapp_links'])): ?>
        <section class="card" style="border: 2px solid #25D366;">
            <div class="card-header">
                <h2><i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> WhatsApp - Mensajes Pendientes</h2>
                <button type="button" class="btn btn-success" onclick="enviarTodosWhatsApp()">
                    <i class="fa-solid fa-paper-plane"></i> Abrir Todos en WhatsApp
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-success" style="background: #E8F5E9; border-color: #25D366; color: #1B5E20;">
                    <i class="fa-solid fa-circle-info"></i>
                    <div>
                        <strong>Presione "Enviar" en cada chat de WhatsApp</strong> para enviar el mensaje pre-llenado.
                        Puede abrir todos a la vez con el boton de arriba.
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>#</th><th>Residente</th><th>Telefono</th><th>Accion</th></tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($_SESSION['whatsapp_links'] as $link): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><strong><?= htmlspecialchars($link['nombre']) ?></strong></td>
                                <td><i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> <?= htmlspecialchars($link['telefono']) ?></td>
                                <td><a href="<?= htmlspecialchars($link['link']) ?>" target="_blank" class="btn btn-sm btn-success wa-link"><i class="fa-brands fa-whatsapp"></> Enviar</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php unset($_SESSION['whatsapp_links']); ?>
            </div>
        </section>
        <script>
        function enviarTodosWhatsApp() {
            var links = document.querySelectorAll('.wa-link');
            links.forEach(function(link, index) {
                setTimeout(function() {
                    window.open(link.href, '_blank');
                }, index * 500);
            });
        }
        </script>
        <?php endif; ?>

        <!-- Formulario para Nuevo Comunicado -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-pen-to-square"></i> Crear Nuevo Comunicado</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/ComunicadoController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_comunicado">

                    <div class="form-group span-full">
                        <label for="titulo"><strong>Titulo del Comunicado</strong></label>
                        <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ej. Convocatoria a Asamblea General Extraordinaria" required>
                    </div>

                    <div class="form-group">
                        <label for="canal"><strong>Canal de Notificacion</strong></label>
                        <select id="canal" name="canal" class="form-control" required>
                            <option value="AMBOS">Correo + WhatsApp</option>
                            <option value="EMAIL">Solo Correo Electronico</option>
                            <option value="WHATSAPP">Solo WhatsApp</option>
                        </select>
                    </div>

                    <div class="form-group span-full">
                        <label for="contenido"><strong>Detalle / Mensaje</strong></label>
                        <textarea id="contenido" name="contenido" class="form-control" rows="5" placeholder="Escriba aqui los detalles del aviso..." required></textarea>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Publicar y Notificar</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Historial de Comunicados -->
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
                                <th>Titulo</th>
                                <th>Canal</th>
                                <th>Publicado por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($comunicados)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay comunicados registrados.</td>
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
                                        <td><?= htmlspecialchars($c['autor'] ?? 'Administracion') ?></td>
                                        <td>
                                            <form action="../../controllers/ComunicadoController.php" method="POST" style="display:inline;" onsubmit="return confirm('Deseas eliminar este comunicado?');">
                                                <input type="hidden" name="action" value="eliminar_comunicado">
                                                <input type="hidden" name="id_comunicado" value="<?= $c['id_comunicado'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                            </form>
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
