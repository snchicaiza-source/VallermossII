<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Presupuesto.php';

verificarRol(['DIRECTIVA', 'ADMINISTRADOR']);

$db = Database::obtenerConexion();

// Auto-reparacion: crea la tabla si no existe en produccion
try {
    $db->exec("CREATE TABLE IF NOT EXISTS presupuesto (
        id_presupuesto INT AUTO_INCREMENT PRIMARY KEY,
        rubro VARCHAR(150) NOT NULL,
        monto_asignado DECIMAL(12,2) DEFAULT 0,
        monto_ejecutado DECIMAL(12,2) DEFAULT 0,
        periodo VARCHAR(20),
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) { /* ya existe */ }

$presupuestoModel = new Presupuesto();
$ejecucion = $presupuestoModel->obtenerResumenAnual();

// --- KPIs ---
$totalAsignado = 0;
$totalEjecutado = 0;
foreach ($ejecucion as $row) {
    $totalAsignado += (float)$row['monto_asignado'];
    $totalEjecutado += (float)$row['monto_ejecutado'];
}
$saldoDisponible = $totalAsignado - $totalEjecutado;
$pctGlobal = $totalAsignado > 0 ? round(($totalEjecutado / $totalAsignado) * 100, 1) : 0;

function colorEjecucion($pct) {
    if ($pct > 100) return '#dc3545';
    if ($pct >= 85) return '#ffc107';
    return '#28a745';
}
function badgeEjecucion($pct) {
    if ($pct > 100) return ['badge-danger', 'Excedido'];
    if ($pct >= 85) return ['badge-warning', 'En limite'];
    return ['badge-success', 'Normal'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejecución Presupuestaria - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .kpi-card { background: var(--card-bg, #fff); border: 1px solid var(--border-color, #e5e7eb); border-radius: 10px; padding: 1rem 1.25rem; }
        .kpi-card .kpi-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted, #6b7280); display: flex; align-items: center; gap: 0.4rem; }
        .kpi-card .kpi-value { font-size: 1.45rem; font-weight: 700; margin-top: 0.35rem; }
        .progress-track { background: var(--border-color, #e5e7eb); border-radius: 999px; height: 10px; overflow: hidden; margin-top: 0.6rem; }
        .progress-fill { height: 100%; border-radius: 999px; transition: width 0.3s ease; }
        .kpi-pct { font-size: 0.85rem; font-weight: 600; margin-top: 0.3rem; }
        @media (max-width: 800px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    </style>
</head>
<body>

<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-chart-line"></i> Seguimiento de Ejecución Presupuestaria</h1>
            <p class="subtitle">Monitoreo de ingresos recaudados vs. gastos e inversiones autorizadas por la Asamblea.</p>
        </header>

        <!-- RESUMEN / KPIs -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa-solid fa-wallet"></i> Presupuesto Total Asignado</div>
                <div class="kpi-value">$<?= number_format($totalAsignado, 2) ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa-solid fa-money-bill-trend-up"></i> Total Ejecutado</div>
                <div class="kpi-value" style="color:#dc3545;">$<?= number_format($totalEjecutado, 2) ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa-solid fa-piggy-bank"></i> Saldo Disponible</div>
                <div class="kpi-value" style="color: <?= $saldoDisponible >= 0 ? '#28a745' : '#dc3545' ?>;">$<?= number_format($saldoDisponible, 2) ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa-solid fa-percent"></i> % de Ejecución</div>
                <div class="kpi-value"><?= $pctGlobal ?>%</div>
                <div class="progress-track">
                    <div class="progress-fill" style="width: <?= min($pctGlobal, 100) ?>%; background: <?= colorEjecucion($pctGlobal) ?>;"></div>
                </div>
                <div class="kpi-pct" style="color: <?= colorEjecucion($pctGlobal) ?>;"><?= badgeEjecucion($pctGlobal)[1] ?></div>
            </div>
        </div>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-wallet"></i> Resumen de Flujo de Caja Mensual</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rubro / Concepto</th>
                                <th>Presupuestado</th>
                                <th>Ejecutado</th>
                                <th>Diferencia / Saldo</th>
                                <th>Cumplimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ejecucion)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay datos de ejecución presupuestaria disponibles.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ejecucion as $row):
                                    [$claseBadge, $textoBadge] = badgeEjecucion((float)$row['porcentaje']);
                                ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['concepto']) ?></strong></td>
                                        <td>$<?= number_format($row['monto_asignado'], 2) ?></td>
                                        <td>$<?= number_format($row['monto_ejecutado'], 2) ?></td>
                                        <td>$<?= number_format($row['monto_asignado'] - $row['monto_ejecutado'], 2) ?></td>
                                        <td><span class="badge <?= $claseBadge ?>"><?= $textoBadge ?> · <?= $row['porcentaje'] ?>%</span></td>
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
