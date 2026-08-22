<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../models/Pago.php';
require_once __DIR__ . '/../../config/db.php';

$id_usuario = $_SESSION['id_usuario'];
$pagoModel = new Pago();
$misPagos = $pagoModel->obtenerPorUsuario($id_usuario);

// Obtener comunicados recientes
$comunicados = [];
try {
    $pdo = Database::obtenerConexion();
    $stmt = $pdo->query("SELECT * FROM comunicados ORDER BY fecha_envio DESC LIMIT 5");
    $comunicados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$nombreUsuario = $_SESSION['nombres'] ?? $_SESSION['usuario_nombres'] ?? 'Residente';
$vivienda = $_SESSION['numero_vivienda'] ?? $_SESSION['usuario_vivienda'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Residente - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1>Bienvenido(a), <?= htmlspecialchars($nombreUsuario) ?></h1>
            <p class="subtitle">
                <?php if (!empty($vivienda)): ?>
                    <i class="fa-solid fa-house"></i> <?= htmlspecialchars($vivienda) ?> - 
                <?php endif; ?>
                Consulta los avisos del condominio y reporta tus comprobantes de alicuotas.
            </p>
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

        <!-- Seccion Subir Comprobante -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-upload"></i> Reportar Pago de Alícuota</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/ResidenteController.php" method="POST" enctype="multipart/form-data" class="grid-form">
                    <input type="hidden" name="action" value="subir_pago">

                    <div class="form-group">
                        <label for="monto">Monto ($)</label>
                        <input type="number" step="0.01" id="monto" name="monto" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label for="concepto">Concepto / Mes</label>
                        <input type="text" id="concepto" name="concepto" class="form-control" placeholder="Ej. Alícuota Agosto 2026" required>
                    </div>

                    <div class="form-group span-full">
                        <label for="comprobante">Comprobante (Imagen o PDF - Max 5MB)</label>
                        <input type="file" id="comprobante" name="comprobante" class="form-control" accept="image/jpeg,image/png,image/gif,application/pdf" required>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up"></i> Registrar Pago</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Comunicados Recientes -->
        <?php if (!empty($comunicados)): ?>
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-bullhorn"></i> Ultimos Comunicados</h2>
            </div>
            <div class="card-body">
                <?php foreach ($comunicados as $c): ?>
                    <div style="padding: 12px 0; border-bottom: 1px solid var(--secondary);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <strong><?= htmlspecialchars($c['titulo']) ?></strong>
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($c['fecha_envio'])) ?></small>
                        </div>
                        <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0;">
                            <?= htmlspecialchars(substr($c['mensaje'], 0, 150)) ?><?= strlen($c['mensaje']) > 150 ? '...' : '' ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Historial de Pagos -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-history"></i> Mis Pagos Registrados</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($misPagos)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No has registrado transferencias.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($misPagos as $p): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                                $fecha = $p['fecha_vencimiento'] ?? $p['created_at'] ?? '';
                                                echo !empty($fecha) ? date('d/m/Y', strtotime($fecha)) : 'N/A';
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($p['concepto'] ?? 'Sin concepto') ?></td>
                                        <td><strong>$<?= number_format($p['monto'] ?? 0, 2) ?></strong></td>
                                        <td>
                                            <?php $estado = $p['estado'] ?? 'PENDIENTE'; ?>
                                            <?php if ($estado === 'PAGADO'): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> PAGADO</span>
                                            <?php elseif ($estado === 'EN_REVISION'): ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> EN REVISIÓN</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-hourglass"></i> PENDIENTE</span>
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
</body>
</html>
