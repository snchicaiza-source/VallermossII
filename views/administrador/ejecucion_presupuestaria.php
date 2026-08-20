<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

$stmt = $db->query("SELECT * FROM presupuesto ORDER BY periodo DESC, rubro ASC");
$presupuestos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejecucion Presupuestaria - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-chart-pie"></i> Ejecucion Presupuestaria</h1>
            <p class="subtitle">Control de presupuesto por rubros: asignacion, ejecucion y saldos disponibles.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-plus-circle"></i> Agregar Rubro Presupuestario</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_presupuesto">

                    <div class="form-group">
                        <label for="rubro">Rubro / Concepto</label>
                        <input type="text" id="rubro" name="rubro" class="form-control" placeholder="Ej. Mantenimiento de areas comunes" required>
                    </div>

                    <div class="form-group">
                        <label for="monto_asignado">Monto Asignado ($)</label>
                        <input type="number" step="0.01" id="monto_asignado" name="monto_asignado" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label for="periodo">Periodo (Ej. 2026)</label>
                        <input type="text" id="periodo" name="periodo" class="form-control" placeholder="Ej. 2026" required>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Crear Rubro</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-table"></i> Seguimiento por Rubros</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rubro</th>
                                <th>Monto Asignado</th>
                                <th>Monto Ejecutado</th>
                                <th>Saldo Disponible</th>
                                <th>% Ejecutado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($presupuestos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay rubros presupuestarios registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($presupuestos as $p):
                                    $saldo = $p['monto_asignado'] - $p['monto_ejecutado'];
                                    $porcentaje = ($p['monto_asignado'] > 0) ? round(($p['monto_ejecutado'] / $p['monto_asignado']) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($p['rubro']) ?></strong></td>
                                        <td>$<?= number_format($p['monto_asignado'], 2) ?></td>
                                        <td>$<?= number_format($p['monto_ejecutado'], 2) ?></td>
                                        <td>
                                            <strong style="color: <?= $saldo >= 0 ? 'var(--success, #28a745)' : 'var(--danger, #dc3545)' ?>">
                                                $<?= number_format($saldo, 2) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php
                                                $badge = 'badge-success';
                                                if ($porcentaje > 90) $badge = 'badge-danger';
                                                elseif ($porcentaje >= 50) $badge = 'badge-warning';
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $porcentaje ?>%</span>
                                        </td>
                                        <td>
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
</body>
</html>
