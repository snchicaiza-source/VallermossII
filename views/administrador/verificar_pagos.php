<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Pago.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

$pagoModel = new Pago();
$todosPagos = $pagoModel->obtenerTodosConUsuario();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Pagos - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>

<div class="app-layout">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="sidebar-title">Vallermosso II</h2>
        <p style="font-size: 0.85rem; color: var(--primary); margin-bottom: 20px;">
            <?= htmlspecialchars($_SESSION['usuario_nombres']) ?> (<?= $_SESSION['usuario_rol'] ?>)
        </p>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="comunicados.php" class="nav-link">📢 Comunicados</a>
            </li>
            <li class="nav-item">
                <a href="verificar_pagos.php" class="nav-link active">🔍 Verificar Pagos</a>
            </li>
            <li class="nav-item" style="margin-top: 30px;">
                <form action="../../controllers/AuthController.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-danger" style="width: 100%;">Cerrar Sesión</button>
                </form>
            </li>
            <li class="nav-item">
                <a href="usuarios.php" class="nav-link">👥 Control de Accesos</a>
            </li>
        </ul>
    </div>

    <!-- Contenido Principal -->
    <div class="main-content">
        <h1 style="color: var(--primary-dark); margin-bottom: 20px;">🔍 Auditoría y Verificación de Comprobantes</h1>

        <!-- Alertas Flash -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2 class="card-title">📋 Alícuotas y Comprobantes Recibidos</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Vivienda</th>
                            <th>Residente</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Voucher</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($todosPagos)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted);">No existen registros de pagos.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($todosPagos as $p): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['numero_vivienda']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['nombres']) ?></td>
                                    <td><?= htmlspecialchars($p['concepto']) ?></td>
                                    <td>$<?= number_format($p['monto'], 2) ?></td>
                                    <td>
                                        <?php if ($p['estado'] === 'PAGADO'): ?>
                                            <span style="background-color: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem;">PAGADO</span>
                                        <?php elseif ($p['estado'] === 'EN_REVISION'): ?>
                                            <span style="background-color: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem;">EN REVISIÓN</span>
                                        <?php else: ?>
                                            <span style="background-color: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem;">PENDIENTE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($p['comprobante_url'])): ?>
                                            <a href="../../<?= htmlspecialchars($p['comprobante_url']) ?>" target="_blank" class="btn btn-secondary" style="font-size: 0.75rem; padding: 4px 8px;">
                                                📄 Ver Comprobante
                                            </a>
                                        <?php else: ?>
                                            <small style="color: var(--text-muted);">Sin comprobante</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['estado'] === 'EN_REVISION'): ?>
                                            <div style="display: flex; gap: 5px;">
                                                <form action="../../controllers/PagoController.php" method="POST">
                                                    <input type="hidden" name="action" value="aprobar">
                                                    <input type="hidden" name="id_pago" value="<?= $p['id_pago'] ?>">
                                                    <button type="submit" class="btn btn-success" style="font-size: 0.75rem; padding: 4px 8px;">✓ Aprobar</button>
                                                </form>
                                                <form action="../../controllers/PagoController.php" method="POST">
                                                    <input type="hidden" name="action" value="rechazar">
                                                    <input type="hidden" name="id_pago" value="<?= $p['id_pago'] ?>">
                                                    <button type="submit" class="btn btn-danger" style="font-size: 0.75rem; padding: 4px 8px;">✕ Rechazar</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <small style="color: var(--text-muted);">Procesado</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>