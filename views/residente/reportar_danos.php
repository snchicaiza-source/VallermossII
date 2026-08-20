<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Incidencia.php';

verificarRol(['RESIDENTE']);

$incidenciaModel = new Incidencia();
$misIncidencias = $incidenciaModel->obtenerPorUsuario($_SESSION['id_usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Danos y Quejas - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-wrench"></i> Reporte de Danos y Quejas</h1>
            <p class="subtitle">Registra eventualidades o sugerencias para la administracion del condominio.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-pen"></i> Nuevo Reporte</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/IncidenciaController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_reporte">

                    <div class="form-group">
                        <label for="tipo">Tipo de Incidencia</label>
                        <select id="tipo" name="tipo" class="form-control" required>
                            <option value="DANO">Dano en Areas Comunes</option>
                            <option value="QUEJA">Queja de Convivencia</option>
                            <option value="RESERVACION">Reserva o Requerimiento</option>
                        </select>
                    </div>

                    <div class="form-group span-full">
                        <label for="descripcion">Descripcion detallada</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="4" placeholder="Detalla el problema o requerimiento..." required></textarea>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Enviar Reporte</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list-check"></i> Estado de mis Reportes</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Descripcion</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($misIncidencias)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No has registrado reportes.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($misIncidencias as $inc): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($inc['fecha'])) ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($inc['tipo']) ?></span></td>
                                        <td><?= htmlspecialchars($inc['descripcion']) ?></td>
                                        <td>
                                            <?php $estado = $inc['estado'] ?? 'PENDIENTE'; ?>
                                            <?php if ($estado === 'RESUELTO'): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> RESUELTO</span>
                                            <?php elseif ($estado === 'EN_REVISION'): ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> EN REVISION</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-hourglass"></i> PENDIENTE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="../../controllers/IncidenciaController.php" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar este reporte?');">
                                                <input type="hidden" name="action" value="eliminar_incidencia">
                                                <input type="hidden" name="id_incidencia" value="<?= $inc['id_incidencia'] ?>">
                                                <input type="hidden" name="return_to" value="reportar_danos">
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
