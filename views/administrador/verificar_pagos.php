<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Pago.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

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
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Vallermosso II</h2>
            <span class="user-badge"><i class="fa-solid fa-user-shield"></i> Administrador</span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="comunicados.php" class="nav-link"><i class="fa-solid fa-bullhorn"></i> <span>Comunicados</span></a>
            </li>
            <li class="nav-item">
                <a href="verificar_pagos.php" class="nav-link"><i class="fa-solid fa-receipt"></i> <span>Auditar Pagos</span></a>
            </li>
            <li class="nav-item">
                <a href="usuarios.php" class="nav-link"><i class="fa-solid fa-users-gear"></i> <span>Control de Accesos</span></a>
            </li>
            <li class="nav-item">
                <a href="activos.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> <span>Bienes y Activos</span></a>
            </li>
            <li class="nav-item">
                <a href="convenios.php" class="nav-link"><i class="fa-solid fa-handshake"></i> <span>Convenios</span></a>
            </li>
            <li class="nav-item">
                <a href="tramites.php" class="nav-link"><i class="fa-solid fa-folder-open"></i> <span>Trámites</span></a>
            </li>
            <li class="nav-item logout-section">
                <form action="../../controllers/AuthController.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-danger btn-block"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</button>
                </form>
            </li>
        </ul>
    </aside>

    <!-- Contenido Principal -->
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-file-invoice-dollar"></i> Auditoría de Pagos y Alícuotas</h1>
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
        <section class="card table-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-clock-history"></i> Comprobantes Pendientes de Verificación</h2>
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
                                <th>Fecha Envío</th>
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
                                        <!-- Residente e Inmueble -->
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($pago['nombres'] ?? $pago['usuario_nombre'] ?? 'Usuario Desconocido') ?>
                                            </strong>
                                            <br>
                                            <small class="text-muted">
                                                Vivienda: <?= htmlspecialchars($pago['numero_vivienda'] ?? 'S/N') ?>
                                            </small>
                                        </td>

                                        <!-- Monto -->
                                        <td>
                                            <strong>$<?= number_format($pago['monto'] ?? 0, 2) ?></strong>
                                        </td>

                                        <!-- Mes / Concepto -->
                                        <td>
                                            <?= htmlspecialchars($pago['concepto'] ?? 'Sin concepto') ?>
                                        </td>

                                        <!-- Comprobante -->
                                        <td>
                                            <?php $archivo = $pago['comprobante_url'] ?? $pago['comprobante'] ?? ''; ?>
                                            <?php if (!empty($archivo)): ?>
                                                <a href="../../public/uploads/<?= htmlspecialchars(basename($archivo)) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-paperclip"></i> Ver Imagen
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Sin archivo</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Fecha Envío -->
                                        <td>
                                            <?php 
                                                $fechaRaw = $pago['fecha_vencimiento'] ?? $pago['fecha_registro'] ?? '';
                                                $fechaFormateada = !empty($fechaRaw) ? date('d/m/Y', strtotime($fechaRaw)) : 'N/A';
                                            ?>
                                            <?= htmlspecialchars($fechaFormateada) ?>
                                        </td>

                                        <!-- Acciones -->
                                        <td>
                                            <div class="btn-group">
                                                <form action="../../controllers/PagoController.php" method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="action" value="aprobar">
                                                    <input type="hidden" name="id_pago" value="<?= $pago['id_pago'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Aprobar Pago">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="../../controllers/PagoController.php" method="POST" style="display:inline-block;">
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

</body>
</html>