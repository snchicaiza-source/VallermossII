<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Presupuesto.php';

verificarRol(['DIRECTIVA', 'ADMINISTRADOR']);

$presupuestoModel = new Presupuesto();
$ejecucion = $presupuestoModel->obtenerResumenAnual();
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
            <h1><i class="fa-solid fa-chart-line"></i> Seguimiento de Ejecucion Presupuestaria</h1>
            <p class="subtitle">Monitoreo de ingresos recaudados vs. gastos e inversiones autorizadas por la Asamblea.</p>
        </header>


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
                                    <td colspan="5" class="text-center">No hay datos de ejecucion presupuestaria disponibles.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ejecucion as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['concepto']) ?></strong></td>
                                        <td>$<?= number_format($row['monto_asignado'], 2) ?></td>
                                        <td>$<?= number_format($row['monto_ejecutado'], 2) ?></td>
                                        <td>$<?= number_format($row['monto_asignado'] - $row['monto_ejecutado'], 2) ?></td>
                                        <td><span class="badge <?= ($row['porcentaje'] > 90) ? 'badge-danger' : 'badge-info' ?>"><?= $row['porcentaje'] ?>%</span></td>
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
