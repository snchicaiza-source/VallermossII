<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Directiva.php';

// Permitir acceso a DIRECTIVA y ADMINISTRADOR
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
</head>
<body>

<div class="app-layout">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="sidebar-title">Vallermosso II</h2>
        <p style="font-size: 0.85rem; color: var(--primary); margin-bottom: 20px;">
            🏛️ <?= htmlspecialchars($_SESSION['usuario_nombres']) ?> (DIRECTIVA)
        </p>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">📊 Panel Directiva</a>
            </li>
            <li class="nav-item">
                <a href="../administrador/comunicados.php" class="nav-link">📢 Notificaciones</a>
            </li>
            <li class="nav-item">
                <a href="../administrador/verificar_pagos.php" class="nav-link">🔍 Auditar Pagos</a>
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
        <h1 style="color: var(--primary-dark); margin-bottom: 20px;">🏛️ Dashboard de la Directiva</h1>

        <!-- 1. Seguimiento Ejecución Presupuestaria -->
        <div class="card" style="margin-bottom: 20px;">
            <h2 class="card-title">📈 Seguimiento de Ejecución Presupuestaria (2026)</h2>
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
                                    <span style="font-weight: bold; color: <?= ($porcentaje > 90) ? '#d9534f' : '#5cb85c' ?>;">
                                        <?= $porcentaje ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Estado con Proveedores -->
        <div class="card" style="margin-bottom: 20px;">
            <h2 class="card-title">🤝 Estado de Contratación y Proveedores</h2>
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
                                <td><?= htmlspecialchars($prov['contacto']) ?></td>
                                <td>$<?= number_format($prov['monto_contrato'], 2) ?></td>
                                <td>
                                    <?php if ($prov['estado_pago'] === 'AL_DIA'): ?>
                                        <span style="background-color: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">AL DÍA</span>
                                    <?php else: ?>
                                        <span style="background-color: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">PENDIENTE</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Documentos, Leyes y Actas -->
        <div class="card">
            <h2 class="card-title">📂 Leyes, Actas y Propiedad Horizontal</h2>
            <ul style="line-height: 2; font-size: 0.95rem; color: var(--text-color);">
                <li>📜 <strong>Leyes & Reglamento General:</strong> Ley de Propiedad Horizontal Vigente.</li>
                <li>📝 <strong>Actas de Asamblea:</strong> Acta No. 04 - Asamblea General Ordinaria 2026.</li>
                <li>🏛️ <strong>Actas de Directiva:</strong> Sesión Extraordinaria de Directiva - Julio 2026.</li>
                <li>🏢 <strong>Declaratoria de Propiedad Horizontal:</strong> Escritura matriz y alícuotas del conjunto.</li>
            </ul>
        </div>

    </div>
</div>

</body>
</html>