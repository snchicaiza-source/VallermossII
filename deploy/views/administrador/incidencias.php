<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Incidencia.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

$db = Database::obtenerConexion();
Incidencia::asegurarTabla();
$stmt = $db->query("SELECT i.*, u.nombres, u.numero_vivienda FROM incidencias i LEFT JOIN usuarios u ON i.id_usuario = u.id_usuario ORDER BY i.fecha DESC");
$incidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Incidencias - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/tablas.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-triangle-exclamation"></i> Gestión de Incidencias y Reportes</h1>
            <p class="subtitle">Seguimiento de daños, quejas y solicitudes reportadas por los residentes.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <?php if ($msg === 'actualizado'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Estado de la incidencia actualizado.</div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list-check"></i> Reportes Recibidos</h2>
            </div>
            <div class="card-body">
                <div class="tabla-toolbar">
                    <div class="filtro-grupo">
                        <span class="filtro-etiqueta">Buscar</span>
                        <div class="buscador-tabla">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" data-buscar="tablaIncidencias" placeholder="Buscar por residente, vivienda o descripción...">
                        </div>
                    </div>
                    <div class="filtro-grupo">
                        <span class="filtro-etiqueta">Estado</span>
                        <select class="filtro-tabla" data-filtro-tabla="tablaIncidencias" data-filtro-col="5">
                            <option value="">Todos los estados</option>
                            <option value="PENDIENTE">Pendiente</option>
                            <option value="EN REVISION">En revisión</option>
                            <option value="RESUELTO">Resuelto</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="tablaIncidencias" data-por-pagina="10">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Residente</th>
                                <th>Vivienda</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($incidencias)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay incidencias reportadas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($incidencias as $inc): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($inc['fecha'])) ?></td>
                                        <td><strong><?= htmlspecialchars($inc['nombres'] ?? 'N/A') ?></strong></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($inc['numero_vivienda'] ?? 'S/N') ?></span></td>
                                        <td><span class="badge badge-warning"><?= htmlspecialchars($inc['tipo']) ?></span></td>
                                        <td><?= htmlspecialchars(substr($inc['descripcion'], 0, 100)) ?><?= strlen($inc['descripcion']) > 100 ? '...' : '' ?></td>
                                        <td>
                                            <?php $estado = $inc['estado']; ?>
                                            <?php if ($estado === 'RESUELTO'): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> RESUELTO</span>
                                            <?php elseif ($estado === 'EN_REVISION'): ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> EN REVISIÓN</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-hourglass"></i> PENDIENTE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (strtoupper($_SESSION['rol'] ?? $_SESSION['usuario_rol'] ?? '') === 'ADMINISTRADOR'): ?>
                                                <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="action" value="cambiar_estado_incidencia">
                                                    <input type="hidden" name="id" value="<?= $inc['id'] ?>">

                                                    <?php if ($inc['estado'] === 'PENDIENTE'): ?>
                                                        <button type="submit" name="nuevo_estado" value="EN_REVISION" class="btn btn-sm btn-outline-info"><i class="fa-solid fa-spinner"></i></button>
                                                    <?php elseif ($inc['estado'] === 'EN_REVISION'): ?>
                                                        <button type="submit" name="nuevo_estado" value="RESUELTO" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check"></i></button>
                                                    <?php else: ?>
                                                        <span class="text-muted"><i class="fa-solid fa-circle-check text-success"></i></span>
                                                    <?php endif; ?>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">Solo administración</span>
                                            <?php endif; ?>
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
<script src="../../public/js/tablas.js?v=3"></script>
</body>
</html>
