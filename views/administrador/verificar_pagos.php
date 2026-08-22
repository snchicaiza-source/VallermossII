<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Pago.php';

verificarRol(['ADMINISTRADOR']);

$pagoModel = new Pago();
$pagosPendientes = $pagoModel->obtenerPendientes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditar Pagos - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-file-invoice-dollar"></i> Auditoria de Pagos y Alicuotas</h1>
            <p class="subtitle">Revisa y valida las transferencias bancarias enviadas por los copropietarios.</p>
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

        <!-- Tabla de Pagos Pendientes -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-clock-rotate-left"></i> Comprobantes Pendientes de Verificacion</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Residente / Inmueble</th>
                                <th>Monto</th>
                                <th>Mes / Concepto</th>
                                <th>Comprobante</th>
                                <th>Fecha Envio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pagosPendientes)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay transferencias pendientes de revisión.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pagosPendientes as $pago): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($pago['nombres'] ?? 'Usuario Desconocido') ?></strong>
                                            <br>
                                            <small class="text-muted">Vivienda: <?= htmlspecialchars($pago['numero_vivienda'] ?? 'S/N') ?></small>
                                        </td>
                                        <td><strong>$<?= number_format($pago['monto'] ?? 0, 2) ?></strong></td>
                                        <td><?= htmlspecialchars($pago['concepto'] ?? 'Sin concepto') ?></td>
                                        <td>
                                            <?php $archivo = $pago['comprobante_url'] ?? ''; ?>
                                            <?php if (!empty($archivo)): ?>
                                                <a href="<?= calcularRaizProyecto() ?>/<?= htmlspecialchars($archivo) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-paperclip"></i> Ver
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Sin archivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $fechaRaw = $pago['created_at'] ?? $pago['fecha_subida'] ?? '';
                                                $fechaFormateada = !empty($fechaRaw) ? date('d/m/Y', strtotime($fechaRaw)) : 'N/A';
                                            ?>
                                            <?= $fechaFormateada ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <form action="../../controllers/PagoController.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="aprobar">
                                                    <input type="hidden" name="id_pago" value="<?= $pago['id_pago'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Aprobar Pago">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="../../controllers/PagoController.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="rechazar">
                                                    <input type="hidden" name="id_pago" value="<?= $pago['id_pago'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Rechazar Pago">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </form>
                                            </div>
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
