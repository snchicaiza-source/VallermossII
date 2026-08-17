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
    <!-- Sidebar Actualizado con todos los módulos -->
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
                                <?php foreach ($pagosPendientes as $p): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($p['nombres']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($p['numero_vivienda'] ?? 'N/A') ?></small>
                                        </td>
                                        <td><strong>$<?= number_format($p['monto'], 2) ?></strong></td>
                                        <td><?= htmlspecialchars($p['concepto']) ?></td>
                                        <td>
                                            <?php if (!empty($p['comprobante_url'])): ?>
                                                <a href="../../public/uploads/<?= htmlspecialchars($p['comprobante_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-paperclip"></i> Ver Imagen
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Sin archivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($p['fecha_registro'])) ?></td>
                                        <td>
                                            <form action="../../controllers/PagoController.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="procesar_pago">
                                                <input type="hidden" name="id_pago" value="<?= $p['id_pago'] ?>">
                                                <button type="submit" name="estado" value="APROBADO" class="btn btn-sm btn-success">
                                                    <i class="fa-solid fa-check"></i> Aprobar
                                                </button>
                                                <button type="submit" name="estado" value="RECHAZADO" class="btn btn-sm btn-danger" onclick="return confirm('¿Deseas rechazar este pago?');">
                                                    <i class="fa-solid fa-xmark"></i> Rechazar
                                                </button>
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

</body>
</html>