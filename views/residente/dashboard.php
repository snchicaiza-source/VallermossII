<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Pago.php';

verificarRol(['RESIDENTE']);

$pagoModel = new Pago();
$pagos = $pagoModel->obtenerPorUsuario($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Residente - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>

<div class="app-layout">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="sidebar-title">Vallermosso II</h2>
        <p style="font-size: 0.85rem; color: var(--primary); margin-bottom: 5px;">
            👋 <?= htmlspecialchars($_SESSION['usuario_nombres']) ?>
        </p>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 20px;">
            🏡 Vivienda: <strong><?= htmlspecialchars($_SESSION['usuario_vivienda']) ?></strong>
        </p>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">💳 Estado de Cuenta</a>
            </li>
            <li class="nav-item" style="margin-top: 30px;">
                <form action="../../controllers/AuthController.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-danger" style="width: 100%;">Cerrar Sesión</button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Contenido Principal -->
    <div class="main-content">
        <h1 style="color: var(--primary-dark); margin-bottom: 20px;">💳 Portal del Residentes & Copropietarios</h1>

        <!-- Mensajes Flash -->
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

        <!-- Estado de Cuenta / Alícuotas -->
        <div class="card">
            <h2 class="card-title">📌 Mis Obligaciones y Alícuotas</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pagos)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted);">No registra obligaciones pendientes ni pagos en el sistema.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pago['concepto']) ?></strong></td>
                                    <td>$<?= number_format($pago['monto'], 2) ?></td>
                                    <td><?= htmlspecialchars($pago['fecha_vencimiento']) ?></td>
                                    <td>
                                        <?php if ($pago['estado'] === 'PAGADO'): ?>
                                            <span style="background-color: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem;">PAGADO</span>
                                        <?php elseif ($pago['estado'] === 'EN_REVISION'): ?>
                                            <span style="background-color: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem;">EN REVISIÓN</span>
                                        <?php else: ?>
                                            <span style="background-color: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem;">PENDIENTE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($pago['estado'] === 'PENDIENTE'): ?>
                                            <!-- Formulario POST con Multipart para subir Voucher -->
                                            <form action="../../controllers/ResidenteController.php" method="POST" enctype="multipart/form-data" style="display: flex; gap: 5px; align-items: center;">
                                                <input type="hidden" name="action" value="subir_comprobante">
                                                <input type="hidden" name="id_pago" value="<?= $pago['id_pago'] ?>">
                                                <input type="file" name="comprobante" accept="image/*,.pdf" required style="font-size: 0.75rem; width: 140px;">
                                                <button type="submit" class="btn btn-primary" style="font-size: 0.75rem; padding: 5px 10px;">Subir</button>
                                            </form>
                                        <?php else: ?>
                                            <small style="color: var(--text-muted);">Comprobante registrado</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sección Informativa / Normativas -->
        <div class="card" style="margin-top: 20px;">
            <h2 class="card-title">📖 Normativas de Convivencia</h2>
            <ul style="font-size: 0.9rem; color: var(--text-color); line-height: 1.8; padding-left: 20px;">
                <li><strong>Horario de Ruido:</strong> Se solicita mantener bajo el nivel de ruido a partir de las 22:00.</li>
                <li><strong>Mascotas:</strong> El uso de correa en áreas comunales es obligatorio.</li>
                <li><strong>Pagos de Alícuotas:</strong> Deben realizarse dentro de los primeros 5 días de cada mes.</li>
            </ul>
        </div>

    </div>
</div>

</body>
</html>