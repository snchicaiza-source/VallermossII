<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['RESIDENTE']);

$id_usuario = $_SESSION['id_usuario'];
$pdo = Database::obtenerConexion();

$stmt = $pdo->prepare("SELECT * FROM pagos WHERE id_usuario = :id AND estado != 'PAGADO' ORDER BY fecha_vencimiento ASC");
$stmt->execute([':id' => $id_usuario]);
$deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalDeuda = 0;
foreach ($deudas as $d) {
    $totalDeuda += (float)$d['monto'];
}

$nombre = $_SESSION['nombres'] ?? $_SESSION['usuario_nombres'] ?? 'N/A';
$vivienda = $_SESSION['numero_vivienda'] ?? $_SESSION['usuario_vivienda'] ?? 'N/A';
$cedula = $_SESSION['cedula'] ?? 'N/A';
$fechaCertificado = date('d \d\e F \d\e Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Deuda - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .sidebar, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 20px !important; }
            .app-layout { display: block !important; }
            body { background: #fff !important; }
            .cert-header { border-bottom: 2px solid #000; }
        }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-file-invoice"></i> Certificado de Deuda</h1>
            <p class="subtitle">Constancia de saldo pendiente de pago.</p>
        </header>


        <div class="no-print" style="margin-bottom: 16px;">
            <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print"></i> Imprimir Certificado</button>
        </div>

        <section class="card">
            <div class="card-body" style="padding: 40px;">
                <div class="cert-header" style="text-align: center; padding-bottom: 20px; margin-bottom: 30px;">
                    <h2 style="margin: 0;">CONJUNTO HABITACIONAL VALLERMOSSO II</h2>
                    <p style="color: var(--text-muted); margin: 4px 0 0 0;">Certificado de Deuda</p>
                </div>

                <div style="margin-bottom: 30px;">
                    <p><strong>Fecha de emision:</strong> <?= $fechaCertificado ?></p>
                    <p><strong>Residente:</strong> <?= htmlspecialchars($nombre) ?></p>
                    <p><strong>Cédula:</strong> <?= htmlspecialchars($cedula) ?></p>
                    <p><strong>Vivienda:</strong> <?= htmlspecialchars($vivienda) ?></p>
                </div>

                <?php if (empty($deudas)): ?>
                    <div style="text-align: center; padding: 30px;">
                        <i class="fa-solid fa-circle-check" style="font-size: 3rem; color: #22c55e;"></i>
                        <p style="margin-top: 16px; font-size: 1.1rem;"><strong>No presenta deudas pendientes.</strong></p>
                    </div>
                <?php else: ?>
                    <h3 style="margin-bottom: 12px;">Obligaciones Pendientes</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deudas as $d): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($d['concepto'] ?? 'N/A') ?></td>
                                        <td><strong>$<?= number_format($d['monto'], 2) ?></strong></td>
                                        <td><?= $d['fecha_vencimiento'] ? date('d/m/Y', strtotime($d['fecha_vencimiento'])) : 'N/A' ?></td>
                                        <td>
                                            <?php if ($d['estado'] === 'EN_REVISION'): ?>
                                                <span class="badge badge-warning">EN REVISIÓN</span>
                                            <?php elseif ($d['estado'] === 'RECHAZADO'): ?>
                                                <span class="badge badge-danger">RECHAZADO</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">PENDIENTE</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="1"></td>
                                    <td><strong style="font-size: 1.1rem;">TOTAL: $<?= number_format($totalDeuda, 2) ?></strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
<script src="../../public/js/sidebar.js"></script>
</body>
</html>
