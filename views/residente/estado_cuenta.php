<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['RESIDENTE']);

$id_usuario = $_SESSION['id_usuario'];
$pdo = Database::obtenerConexion();

$stmt = $pdo->prepare("SELECT * FROM pagos WHERE id_usuario = :id ORDER BY created_at DESC");
$stmt->execute([':id' => $id_usuario]);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recaudaciones registradas por la administracion para este residente
$stmt = $pdo->prepare("SELECT * FROM recaudaciones WHERE id_usuario = :id ORDER BY fecha_registro DESC");
$stmt->execute([':id' => $id_usuario]);
$recaudaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Une ambos origenes en una sola lista con formato comun
$movimientos = [];
foreach ($pagos as $p) {
    $estado = strtoupper($p['estado']);
    $movimientos[] = [
        'concepto'  => $p['concepto'],
        'monto'     => $p['monto'],
        'fecha_ref' => $p['fecha_vencimiento'] ?? null,
        'estado'    => $estado === 'PAGADO' ? 'PAGADO' : ($estado === 'EN_REVISION' ? 'EN_REVISION' : ($estado === 'RECHAZADO' ? 'RECHAZADO' : 'PENDIENTE')),
        'origen'    => 'Pago residente',
        'fecha_reg' => $p['created_at']
    ];
}
foreach ($recaudaciones as $r) {
    $estado = strtoupper($r['estado_pago']);
    $movimientos[] = [
        'concepto'  => $r['concepto'],
        'monto'     => $r['monto'],
        'fecha_ref' => $r['fecha_pago'] ?? null,
        'estado'    => $estado === 'APROBADO' ? 'PAGADO' : ($estado === 'RECHAZADO' ? 'RECHAZADO' : 'PENDIENTE'),
        'origen'    => 'Recaudación administración',
        'fecha_reg' => $r['fecha_registro']
    ];
}
usort($movimientos, function ($a, $b) {
    return strtotime($b['fecha_reg']) <=> strtotime($a['fecha_reg']);
});

$totalPendiente = 0;
$totalPagado = 0;
$ultimoPago = null;

foreach ($movimientos as $m) {
    if ($m['estado'] === 'PAGADO') {
        $totalPagado += (float)$m['monto'];
        if (!$ultimoPago) $ultimoPago = $m;
    } elseif ($m['estado'] === 'PENDIENTE' || $m['estado'] === 'EN_REVISION') {
        $totalPendiente += (float)$m['monto'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Cuenta - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-wallet"></i> Estado de Cuenta</h1>
            <p class="subtitle">Consulta el resumen de tus pagos y saldos pendientes.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;"><i class="fa-solid fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <span class="stat-value">$<?= number_format($totalPendiente, 2) ?></span>
                    <span class="stat-label">Total Pendiente</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(34,197,94,0.1); color: #22c55e;"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-info">
                    <span class="stat-value">$<?= number_format($totalPagado, 2) ?></span>
                    <span class="stat-label">Total Pagado</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $ultimoPago ? date('d/m/Y', strtotime($ultimoPago['fecha_reg'])) : 'N/A' ?></span>
                    <span class="stat-label">Último Pago</span>
                </div>
            </div>
        </div>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list"></i> Detalle de Pagos</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Origen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movimientos)): ?>
                                <tr><td colspan="5" class="text-center">No hay pagos registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($movimientos as $m): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($m['concepto'] ?? 'N/A') ?></td>
                                        <td><strong>$<?= number_format($m['monto'], 2) ?></strong></td>
                                        <td><?= $m['fecha_ref'] ? date('d/m/Y', strtotime($m['fecha_ref'])) : 'N/A' ?></td>
                                        <td>
                                            <?php if ($m['estado'] === 'PAGADO'): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> PAGADO</span>
                                            <?php elseif ($m['estado'] === 'EN_REVISION'): ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> EN REVISIÓN</span>
                                            <?php elseif ($m['estado'] === 'RECHAZADO'): ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> RECHAZADO</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-hourglass"></i> PENDIENTE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($m['origen']) ?><br><small class="text-muted"><?= date('d/m/Y', strtotime($m['fecha_reg'])) ?></small></td>
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
