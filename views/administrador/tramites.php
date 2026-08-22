<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

// Obtener lista de trámites
$stmt = $db->query("SELECT * FROM tramites ORDER BY fecha DESC");
$tramites = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trámites - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <!-- Sidebar Modular Reutilizable -->
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <!-- Contenido Principal -->
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-folder-open"></i> Gestión de Trámites y Solicitudes</h1>
            <p class="subtitle">Administración de permisos, certificados de no adeudar y solicitudes de residentes.</p>
        </header>


        <!-- Mensajes del Sistema -->
        <?php if ($msg === 'creado'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Trámite ingresado al sistema.</div>
        <?php elseif ($msg === 'actualizado'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Estado del trámite modificado.</div>
        <?php elseif ($error === 'campos_vacios'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Debe completar los campos obligatorios.</div>
        <?php endif; ?>

        <!-- Formulario para Registrar Trámites -->
        <section class="card form-card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fa-solid fa-file-circle-plus"></i> Ingresar Nuevo Trámite</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_tramite">

                    <div class="form-group">
                        <label for="solicitante"><strong>Nombre del Solicitante</strong></label>
                        <input type="text" id="solicitante" name="solicitante" class="form-control" placeholder="Ej. Ana Lucía Pérez" required>
                    </div>

                    <div class="form-group">
                        <label for="asunto"><strong>Asunto / Tipo de Trámite</strong></label>
                        <input type="text" id="asunto" name="asunto" class="form-control" placeholder="Ej. Certificado de No Adeudar / Permiso Mudanza" required>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Registrar Trámite</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Listado de Trámites -->
        <section class="card table-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-clock-rotate-left"></i> Historial de Trámites</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th># ID</th>
                                <th>Solicitante</th>
                                <th>Asunto</th>
                                <th>Estado</th>
                                <th>Fecha Ingreso</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tramites)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay trámites registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tramites as $t): ?>
                                    <tr>
                                        <td>#<?= $t['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($t['solicitante']) ?></strong></td>
                                        <td><?= htmlspecialchars($t['asunto']) ?></td>
                                        <td>
                                            <?php 
                                                $tBadge = 'badge-warning';
                                                if ($t['estado'] === 'EN_PROCESO') $tBadge = 'badge-info';
                                                if ($t['estado'] === 'COMPLETADO') $tBadge = 'badge-success';
                                            ?>
                                            <span class="badge <?= $tBadge ?>"><?= htmlspecialchars(str_replace('_', ' ', $t['estado'])) ?></span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($t['fecha'])) ?></td>
                                        <td>
                                            <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline-block;">
                                                <input type="hidden" name="action" value="cambiar_estado_tramite">
                                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                
                                                <?php if ($t['estado'] === 'PENDIENTE'): ?>
                                                    <button type="submit" name="nuevo_estado" value="EN_PROCESO" class="btn btn-sm btn-outline-info"><i class="fa-solid fa-spinner"></i> En Proceso</button>
                                                <?php elseif ($t['estado'] === 'EN_PROCESO'): ?>
                                                    <button type="submit" name="nuevo_estado" value="COMPLETADO" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check"></i> Finalizar</button>
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="fa-solid fa-circle-check text-success"></i> Cerrado</span>
                                                <?php endif; ?>
                                            </form>
                                            <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar este trámite?');">
                                                <input type="hidden" name="action" value="eliminar_tramite">
                                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
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