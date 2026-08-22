<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['DIRECTIVA']);

$db = Database::obtenerConexion();

$stmt = $db->query("SELECT * FROM proveedores ORDER BY created_at DESC");
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Auto-reparacion: crea las tablas del flujo de contratacion si faltan
$db->exec("CREATE TABLE IF NOT EXISTS contratos (
    id_contrato INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT NOT NULL,
    servicio VARCHAR(200) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE DEFAULT NULL,
    monto DECIMAL(10,2) NOT NULL,
    tipo_monto ENUM('MENSUAL','TOTAL') DEFAULT 'TOTAL',
    documento_pdf VARCHAR(255) DEFAULT NULL,
    estado_orden ENUM('PENDIENTE_ACTA','LISTO_PAGO','PAGADO') DEFAULT 'PENDIENTE_ACTA',
    estado ENUM('VIGENTE','FINALIZADO','CANCELADO') DEFAULT 'VIGENTE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$db->exec("CREATE TABLE IF NOT EXISTS actas_recepcion (
    id_acta INT AUTO_INCREMENT PRIMARY KEY,
    id_contrato INT NOT NULL,
    conforme TINYINT(1) NOT NULL DEFAULT 1,
    detalle TEXT DEFAULT NULL,
    recibido_por VARCHAR(150) NOT NULL,
    fecha_acta TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$db->exec("CREATE TABLE IF NOT EXISTS pagos_proveedores (
    id_pago_prov INT AUTO_INCREMENT PRIMARY KEY,
    id_contrato INT NOT NULL,
    numero_factura VARCHAR(50) NOT NULL,
    metodo_pago ENUM('EFECTIVO','TRANSFERENCIA','CHEQUE') NOT NULL,
    cuenta_origen VARCHAR(100) DEFAULT NULL,
    monto_pagado DECIMAL(10,2) NOT NULL,
    fecha_pago TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$contratos = $db->query("SELECT c.*, p.nombre_empresa
                         FROM contratos c LEFT JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                         ORDER BY c.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$pagosProv = $db->query("SELECT g.*, p.nombre_empresa
                         FROM pagos_proveedores g
                         LEFT JOIN contratos c ON g.id_contrato = c.id_contrato
                         LEFT JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                         ORDER BY g.fecha_pago DESC")->fetchAll(PDO::FETCH_ASSOC);

$total = count($proveedores);
$alDia = count(array_filter($proveedores, function ($p) { return $p['estado_pago'] === 'AL_DIA'; }));
$pendientes = count(array_filter($proveedores, function ($p) { return $p['estado_pago'] === 'PENDIENTE'; }));
$enProceso = $total - $alDia - $pendientes;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Proveedores - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-truck-field"></i> Estado de Proveedores</h1>
            <p class="subtitle">Consulta del estado de pagos y contratos de los proveedores del conjunto. La contratacion y gestión la realiza la administración.</p>
        </header>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <section class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body" style="display:flex; gap:2rem; flex-wrap:wrap;">
                <div><i class="fa-solid fa-list"></i> Total: <strong><?= $total ?></strong></div>
                <div style="color:var(--success,#2e7d32);"><i class="fa-solid fa-circle-check"></i> Al día: <strong><?= $alDia ?></strong></div>
                <div style="color:#f57c00;"><i class="fa-solid fa-clock"></i> En proceso: <strong><?= $enProceso ?></strong></div>
                <div style="color:#c62828;"><i class="fa-solid fa-triangle-exclamation"></i> Pendientes: <strong><?= $pendientes ?></strong></div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list"></i> Contratos y Estado de Pago</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Servicio / Rubro</th>
                                <th>Monto Contrato</th>
                                <th>Estado de Pago</th>
                                <th>Fecha Alta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($proveedores)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay proveedores registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($proveedores as $prov): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($prov['nombre_empresa']) ?></strong></td>
                                        <td><?= htmlspecialchars($prov['servicio_rubro']) ?></td>
                                        <td><?= (float)$prov['monto_contrato'] > 0 ? '$' . number_format($prov['monto_contrato'], 2) : '-' ?></td>
                                        <td>
                                            <?php if ($prov['estado_pago'] === 'AL_DIA'): ?>
                                                <span class="badge badge-success">AL DÍA</span>
                                            <?php elseif ($prov['estado_pago'] === 'PENDIENTE'): ?>
                                                <span class="badge badge-danger">PENDIENTE</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">EN PROCESO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($prov['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h2><i class="fa-solid fa-file-signature"></i> Contratos y Estado de Orden</h2>
                <span class="text-muted">Flujo: Pendiente de acta &rarr; Listo para pago &rarr; Pagado.</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Proveedor</th>
                                <th>Servicio</th>
                                <th>Vigencia</th>
                                <th>Monto</th>
                                <th>Estado de Orden</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contratos)): ?>
                                <tr><td colspan="6" class="text-center">No hay contratos registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($contratos as $c): ?>
                                    <tr>
                                        <td><?= $c['id_contrato'] ?></td>
                                        <td><strong><?= htmlspecialchars($c['nombre_empresa'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($c['servicio']) ?></td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($c['fecha_inicio'])) ?>
                                            <?php if (!empty($c['fecha_fin'])): ?>
                                                &rarr; <?= date('d/m/Y', strtotime($c['fecha_fin'])) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>$<?= number_format($c['monto'], 2) ?> <small class="text-muted"><?= $c['tipo_monto'] ?></small></td>
                                        <td>
                                            <?php if ($c['estado_orden'] === 'PAGADO'): ?>
                                                <span class="badge badge-success">PAGADO</span>
                                            <?php elseif ($c['estado_orden'] === 'LISTO_PAGO'): ?>
                                                <span class="badge badge-info">LISTO PARA PAGO</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">PENDIENTE DE ACTA</span>
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

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-money-bill-wave"></i> Pagos Realizados a Proveedores</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Factura</th>
                                <th>Método</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pagosProv)): ?>
                                <tr><td colspan="5" class="text-center">No hay pagos registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pagosProv as $g): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($g['fecha_pago'])) ?></td>
                                        <td><strong><?= htmlspecialchars($g['nombre_empresa'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($g['numero_factura']) ?></td>
                                        <td><?= htmlspecialchars($g['metodo_pago']) ?></td>
                                        <td>$<?= number_format($g['monto_pagado'], 2) ?></td>
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
