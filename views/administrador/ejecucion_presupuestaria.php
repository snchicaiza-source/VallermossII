<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

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

$stmt = $db->query("SELECT * FROM presupuesto ORDER BY periodo DESC, rubro ASC");
$presupuestos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- KPIs ---
$totalAsignado = 0;
$totalEjecutado = 0;
foreach ($presupuestos as $p) {
    $totalAsignado += (float)$p['monto_asignado'];
    $totalEjecutado += (float)$p['monto_ejecutado'];
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

// --- Modo edicion ---
$editando = null;
$idEditar = (int)($_GET['editar'] ?? 0);
if ($idEditar > 0) {
    $st = $db->prepare("SELECT * FROM presupuesto WHERE id_presupuesto = :id");
    $st->execute([':id' => $idEditar]);
    $editando = $st->fetch(PDO::FETCH_ASSOC);
}

$anioActual = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejecución Presupuestaria - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/tablas.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .kpi-card { background: var(--card-bg, #fff); border: 1px solid var(--border-color, #e5e7eb); border-radius: 10px; padding: 1rem 1.25rem; }
        .kpi-card .kpi-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted, #6b7280); display: flex; align-items: center; gap: 0.4rem; }
        .kpi-card .kpi-value { font-size: 1.45rem; font-weight: 700; margin-top: 0.35rem; }
        .progress-track { background: var(--border-color, #e5e7eb); border-radius: 999px; height: 10px; overflow: hidden; margin-top: 0.6rem; }
        .progress-fill { height: 100%; border-radius: 999px; transition: width 0.3s ease; }
        .kpi-pct { font-size: 0.85rem; font-weight: 600; margin-top: 0.3rem; }
        .pres-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; }
        @media (max-width: 800px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } .pres-grid { grid-template-columns: 1fr; } }
        .input-money { display: flex; align-items: center; border: 1px solid var(--border-color, #ced4da); border-radius: 6px; overflow: hidden; background: #fff; }
        .input-money > span { padding: 0.55rem 0.75rem; background: var(--bg-secondary, #f3f4f6); color: var(--text-muted, #495057); font-weight: 600; border-right: 1px solid var(--border-color, #ced4da); }
        .input-money > input { border: none; outline: none; flex: 1; padding: 0.55rem 0.75rem; font: inherit; min-width: 0; }
        .bar-cell { min-width: 130px; }
        .bar-cell .progress-track { height: 8px; margin-top: 0.25rem; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-chart-pie"></i> Ejecución Presupuestaria</h1>
            <p class="subtitle">Control de presupuesto por rubros: asignación, ejecución y saldos disponibles.</p>
        </header>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

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

        <!-- FORMULARIO -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-plus-circle"></i> <?= $editando ? 'Editar Rubro Presupuestario' : 'Agregar Rubro Presupuestario' ?></h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="pres-grid">
                    <input type="hidden" name="action" value="<?= $editando ? 'editar_presupuesto' : 'crear_presupuesto' ?>">
                    <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id_presupuesto'] ?>"><?php endif; ?>

                    <div class="form-group">
                        <label for="rubro">Rubro / Concepto</label>
                        <input type="text" id="rubro" name="rubro" class="form-control" placeholder="Ej. Mantenimiento de áreas comunes" value="<?= $editando ? htmlspecialchars($editando['rubro']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="monto_asignado">Monto Asignado</label>
                        <div class="input-money">
                            <span>$</span>
                            <input type="number" step="0.01" min="0" id="monto_asignado" name="monto_asignado" placeholder="0.00" value="<?= $editando ? htmlspecialchars($editando['monto_asignado']) : '' ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="periodo">Periodo</label>
                        <select id="periodo" name="periodo" class="form-control" required>
                            <option value="">Selecciona el ano...</option>
                            <?php for ($a = $anioActual - 2; $a <= $anioActual + 2; $a++): ?>
                                <option value="<?= $a ?>" <?= ($editando && $editando['periodo'] == $a) ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-actions" style="grid-column: 1 / -1; display:flex; gap:0.75rem;">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $editando ? 'Guardar Cambios' : '+ Guardar Rubro' ?></button>
                        <?php if ($editando): ?>
                            <a href="ejecucion_presupuestaria.php" class="btn btn-outline">Cancelar</a>
                        <?php else: ?>
                            <button type="reset" class="btn btn-outline"><i class="fa-solid fa-eraser"></i> Limpiar</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <!-- SEGUIMIENTO -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-table"></i> Seguimiento por Rubros</h2>
            </div>
            <div class="card-body">
                <div class="tabla-toolbar">
                    <div class="filtro-grupo">
                        <span class="filtro-etiqueta">Buscar</span>
                        <div class="buscador-tabla">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" data-buscar="tablaPresupuesto" placeholder="Buscar rubro o periodo...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="tablaPresupuesto" data-por-pagina="10">
                        <thead>
                            <tr>
                                <th>Rubro / Concepto</th>
                                <th>Periodo</th>
                                <th>Monto Asignado</th>
                                <th>Ejecutado</th>
                                <th>Saldo Disponible</th>
                                <th>% Ejecución</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($presupuestos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay rubros presupuestarios registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($presupuestos as $p):
                                    $asignado = (float)$p['monto_asignado'];
                                    $ejecutado = (float)$p['monto_ejecutado'];
                                    $saldo = $asignado - $ejecutado;
                                    $porcentaje = ($asignado > 0) ? round(($ejecutado / $asignado) * 100, 1) : 0;
                                    [$claseBadge, $textoBadge] = badgeEjecucion($porcentaje);
                                ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($p['rubro']) ?></strong></td>
                                        <td><?= htmlspecialchars($p['periodo']) ?></td>
                                        <td>$<?= number_format($asignado, 2) ?></td>
                                        <td>$<?= number_format($ejecutado, 2) ?></td>
                                        <td>
                                            <strong style="color: <?= $saldo >= 0 ? '#28a745' : '#dc3545' ?>;">
                                                $<?= number_format($saldo, 2) ?>
                                            </strong>
                                        </td>
                                        <td class="bar-cell">
                                            <span class="badge <?= $claseBadge ?>"><?= $textoBadge ?> · <?= $porcentaje ?>%</span>
                                            <div class="progress-track">
                                                <div class="progress-fill" style="width: <?= min($porcentaje, 100) ?>%; background: <?= colorEjecucion($porcentaje) ?>;"></div>
                                            </div>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <a href="?editar=<?= $p['id_presupuesto'] ?>" class="btn btn-sm btn-outline" title="Editar rubro"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar este rubro?');">
                                                <input type="hidden" name="action" value="eliminar_presupuesto">
                                                <input type="hidden" name="id" value="<?= $p['id_presupuesto'] ?>">
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
<script src="../../public/js/tablas.js?v=3"></script>
</body>
</html>
