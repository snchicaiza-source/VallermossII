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
    <title>Ejecución Presupuestaria - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Vallermosso II</h2>
            <span class="user-badge"><i class="fa-solid fa-user-group"></i> Directiva</span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="ejecucion_presupuestaria.php" class="nav-link active"><i class="fa-solid fa-chart-pie"></i> <span>Ejecución Presupuestaria</span></a>
            </li>
            <li class="nav-item">
                <a href="proveedores.php" class="nav-link"><i class="fa-solid fa-handshake"></i> <span>Estado de Proveedores</span></a>
            </li>
            <li class="nav-item">
                <a href="normativa.php" class="nav-link"><i class="fa-solid fa-scale-balanced"></i> <span>Leyes y Actas</span></a>
            </li>
            <li class="nav-item logout-section">
                <form action="../../controllers/AuthController.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-danger btn-block"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</button>
                </form>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-chart-line"></i> Seguimiento de Ejecución Presupuestaria</h1>
            <p class="subtitle">Monitoreo de ingresos recaudados vs. gastos e inversiones autorizadas por la Asamblea.</p>
        </header>

        <section class="card table-card">
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
                                    <td>Mantenimiento de Jardines y Áreas Verdes</td>
                                    <td>$500.00</td>
                                    <td>$450.00</td>
                                    <td>+$50.00</td>
                                    <td><span class="badge badge-info">90%</span></td>
                                </tr>
                                <tr>
                                    <td>Seguridad y Monitoreo 24/7</td>
                                    <td>$1,200.00</td>
                                    <td>$1,200.00</td>
                                    <td>$0.00</td>
                                    <td><span class="badge badge-info">100%</span></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ejecucion as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['concepto']) ?></strong></td>
                                        <td>$<?= number_format($row['monto_presupuestado'], 2) ?></td>
                                        <td>$<?= number_format($row['monto_ejecutado'], 2) ?></td>
                                        <td>$<?= number_format($row['monto_presupuestado'] - $row['monto_ejecutado'], 2) ?></td>
                                        <td><span class="badge badge-info"><?= $row['porcentaje'] ?>%</span></td>
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