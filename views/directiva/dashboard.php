<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Directiva.php';

verificarRol(['DIRECTIVA', 'ADMINISTRADOR']);

$directivaModel = new Directiva();
$presupuestos = $directivaModel->obtenerPresupuesto();
$proveedores = $directivaModel->obtenerProveedores();
$documentos = $directivaModel->obtenerDocumentos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Directiva - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-landmark"></i> Dashboard de la Directiva</h1>
            <p class="subtitle">Gestión de presupuestos, contrataciones y acervo documental del conjunto.</p>
        </header>


        <!-- Resumen rapido -->
        <div class="grid-form" style="margin-bottom: 24px;">
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="text-align: center;">
                    <i class="fa-solid fa-wallet" style="font-size: 2rem; color: var(--accent);"></i>
                    <h3 style="margin: 8px 0 4px; color: var(--primary-dark);">Presupuesto 2026</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Ejecución activa por rubros</p>
                </div>
            </div>
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="text-align: center;">
                    <i class="fa-solid fa-handshake" style="font-size: 2rem; color: var(--primary);"></i>
                    <h3 style="margin: 8px 0 4px; color: var(--primary-dark);">Proveedores</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted);"><?= count($proveedores) ?> contratados</p>
                </div>
            </div>
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="text-align: center;">
                    <i class="fa-solid fa-folder-tree" style="font-size: 2rem; color: var(--danger);"></i>
                    <h3 style="margin: 8px 0 4px; color: var(--primary-dark);">Documentos</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Acervo disponible</p>
                </div>
            </div>
        </div>

        <!-- Ejecucion Presupuestaria -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-chart-line"></i> Seguimiento de Ejecución Presupuestaria (2026)</h2>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($presupuestos as $p): 
                                $saldo = $p['monto_asignado'] - $p['monto_ejecutado'];
                                $porcentaje = ($p['monto_asignado'] > 0) ? round(($p['monto_ejecutado'] / $p['monto_asignado']) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['rubro']) ?></strong></td>
                                    <td>$<?= number_format($p['monto_asignado'], 2) ?></td>
                                    <td>$<?= number_format($p['monto_ejecutado'], 2) ?></td>
                                    <td>$<?= number_format($saldo, 2) ?></td>
                                    <td>
                                        <span class="badge <?= ($porcentaje > 90) ? 'badge-danger' : ($porcentaje >= 50 ? 'badge-warning' : 'badge-success') ?>">
                                            <?= $porcentaje ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Proveedores -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-handshake"></i> Estado de Contratacion y Proveedores</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Empresa / Proveedor</th>
                                <th>Servicio / Rubro</th>
                                <th>Contacto</th>
                                <th>Monto Contrato</th>
                                <th>Estado de Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedores as $prov): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($prov['nombre_empresa']) ?></strong></td>
                                    <td><?= htmlspecialchars($prov['servicio_rubro']) ?></td>
                                    <td><?= htmlspecialchars($prov['contacto'] ?? 'N/A') ?></td>
                                    <td>$<?= number_format($prov['monto_contrato'], 2) ?></td>
                                    <td>
                                        <?php if ($prov['estado_pago'] === 'AL_DIA'): ?>
                                            <span class="badge badge-success">AL DÍA</span>
                                        <?php elseif ($prov['estado_pago'] === 'PENDIENTE'): ?>
                                            <span class="badge badge-danger">PENDIENTE</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">EN PROCESO</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Documentos -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-folder-tree"></i> Leyes, Actas y Propiedad Horizontal</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($documentos)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th>Título</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documentos as $doc): ?>
                                    <tr>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($doc['categoria'] ?? 'Documento') ?></span></td>
                                        <td><strong><?= htmlspecialchars($doc['titulo']) ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($doc['fecha_publicacion'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0; margin: 0; line-height: 2.4;">
                        <li><i class="fa-solid fa-scroll text-primary"></i> <strong>Leyes & Reglamento General:</strong> Ley de Propiedad Horizontal Vigente.</li>
                        <li><i class="fa-solid fa-file-signature text-primary"></i> <strong>Actas de Asamblea:</strong> Acta No. 04 - Asamblea General Ordinaria 2026.</li>
                        <li><i class="fa-solid fa-building-user text-primary"></i> <strong>Actas de Directiva:</strong> Sesión Extraordinaria de Directiva - Julio 2026.</li>
                        <li><i class="fa-solid fa-file-contract text-primary"></i> <strong>Declaratoria:</strong> Escritura matriz y alicuotas del conjunto.</li>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<script src="../../public/js/sidebar.js"></script>
</body>
</html>
