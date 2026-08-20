<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

$stmt = $db->query("
    SELECT 
        u.id_usuario,
        u.nombres,
        u.numero_vivienda,
        COALESCE(SUM(CASE WHEN p.estado = 'APROBADO' OR p.estado = 'PAGADO' THEN p.monto ELSE 0 END), 0) AS total_pagado,
        COALESCE(SUM(CASE WHEN p.estado = 'PENDIENTE' OR p.estado = 'VENCIDO' THEN p.monto ELSE 0 END), 0) AS total_pendiente,
        COUNT(CASE WHEN p.estado = 'PENDIENTE' OR p.estado = 'VENCIDO' THEN 1 END) AS pagos_pendientes
    FROM usuarios u
    LEFT JOIN pagos p ON u.id_usuario = p.id_usuario
    WHERE u.rol = 'RESIDENTE'
    GROUP BY u.id_usuario, u.nombres, u.numero_vivienda
    ORDER BY u.nombres
");
$estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <h1><i class="fa-solid fa-file-invoice"></i> Estado de Cuenta de Residentes</h1>
            <p class="subtitle">Resumen de pagos y saldos pendientes de todos los copropietarios.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-users"></i> Resumen de Cuenta por Residente</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Residente</th>
                                <th>Vivienda</th>
                                <th>Total Pagado</th>
                                <th>Total Pendiente</th>
                                <th> Pagos Pendientes</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($estados)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay residentes registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($estados as $e): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($e['nombres']) ?></strong></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($e['numero_vivienda'] ?: 'S/N') ?></span></td>
                                        <td><strong style="color: var(--success, #28a745);">$<?= number_format($e['total_pagado'], 2) ?></strong></td>
                                        <td><strong style="color: var(--danger, #dc3545);">$<?= number_format($e['total_pendiente'], 2) ?></strong></td>
                                        <td><?= $e['pagos_pendientes'] ?></td>
                                        <td>
                                            <?php if ($e['total_pendiente'] <= 0): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-check-circle"></i> AL DIA</span>
                                            <?php elseif ($e['pagos_pendientes'] > 0): ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-exclamation-circle"></i> PENDIENTE</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">SIN MOVIMIENTOS</span>
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
