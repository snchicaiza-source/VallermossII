<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['RESIDENTE']);

$id_usuario = $_SESSION['id_usuario'];
$pdo = Database::obtenerConexion();

// Auto-reparacion: crea la tabla de recibos si falta
$pdo->exec("CREATE TABLE IF NOT EXISTS recibos_pago (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pago INT NOT NULL,
    id_usuario INT NOT NULL,
    numero_recibo VARCHAR(20) NOT NULL,
    monto_pagado DECIMAL(10,2) NOT NULL,
    concepto VARCHAR(150) NOT NULL,
    fecha_emision TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Genera recibos para pagos aprobados que aun no tengan (pagos antiguos)
$pendientes = $pdo->prepare("SELECT p.id_pago, p.concepto, p.monto FROM pagos p
                             LEFT JOIN recibos_pago r ON r.id_pago = p.id_pago
                             WHERE p.id_usuario = :u AND p.estado = 'PAGADO' AND r.id IS NULL");
$pendientes->execute([':u' => $id_usuario]);
foreach ($pendientes->fetchAll(PDO::FETCH_ASSOC) as $p) {
    $ins = $pdo->prepare("INSERT INTO recibos_pago (id_pago, id_usuario, numero_recibo, monto_pagado, concepto) VALUES (:p, :u, :n, :m, :c)");
    $ins->execute([
        ':p' => (int)$p['id_pago'],
        ':u' => $id_usuario,
        ':n' => 'REC-' . str_pad((string)$p['id_pago'], 6, '0', STR_PAD_LEFT),
        ':m' => (float)$p['monto'],
        ':c' => $p['concepto']
    ]);
}

$nombre = $_SESSION['nombres'] ?? $_SESSION['usuario_nombres'] ?? 'N/A';
$vivienda = $_SESSION['numero_vivienda'] ?? $_SESSION['usuario_vivienda'] ?? 'N/A';
$cedula = $_SESSION['cedula'] ?? 'N/A';

// Modo recibo individual imprimible
$verPago = (int)($_GET['ver'] ?? 0);
if ($verPago > 0) {
    $stmt = $pdo->prepare("SELECT p.*, r.numero_recibo, r.fecha_emision AS fecha_recibo
                           FROM pagos p LEFT JOIN recibos_pago r ON r.id_pago = p.id_pago
                           WHERE p.id_pago = :p AND p.id_usuario = :u AND p.estado = 'PAGADO'");
    $stmt->execute([':p' => $verPago, ':u' => $id_usuario]);
    $recibo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recibo) {
        header('Location: recibos_pago.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo <?= htmlspecialchars($recibo['numero_recibo'] ?? '') ?> - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .sidebar, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 20px !important; }
            .app-layout { display: block !important; }
            body { background: #fff !important; }
            .recibo-header { border-bottom: 2px solid #000; }
        }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <div class="no-print" style="margin-bottom: 16px; display:flex; gap:0.5rem;">
            <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print"></i> Imprimir / Guardar PDF</button>
            <a href="recibos_pago.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Volver</a>
        </div>

        <section class="card">
            <div class="card-body" style="padding: 40px;">
                <div class="recibo-header" style="text-align:center; padding-bottom:20px; margin-bottom:30px;">
                    <h2 style="margin:0;">CONJUNTO HABITACIONAL VALLERMOSSO II</h2>
                    <p style="color:var(--text-muted); margin:4px 0 0 0;">Recibo Oficial de Pago</p>
                    <p style="margin:8px 0 0 0;"><strong><?= htmlspecialchars($recibo['numero_recibo'] ?? 'REC-' . str_pad((string)$recibo['id_pago'], 6, '0', STR_PAD_LEFT)) ?></strong></p>
                </div>

                <div style="margin-bottom:30px;">
                    <p><strong>Fecha de emision:</strong> <?= date('d/m/Y H:i', strtotime($recibo['fecha_recibo'] ?? $recibo['created_at'])) ?></p>
                    <p><strong>Recibido de:</strong> <?= htmlspecialchars($nombre) ?></p>
                    <p><strong>Cédula:</strong> <?= htmlspecialchars($cedula) ?></p>
                    <p><strong>Vivienda:</strong> <?= htmlspecialchars($vivienda) ?></p>
                </div>

                <table class="table">
                    <thead>
                        <tr><th>Concepto</th><th>Fecha de Pago</th><th>Monto</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($recibo['concepto'] ?? 'N/A') ?></td>
                            <td><?= date('d/m/Y', strtotime($recibo['created_at'])) ?></td>
                            <td><strong>$<?= number_format((float)$recibo['monto'], 2) ?></strong></td>
                        </tr>
                    </tbody>
                </table>

                <div style="text-align:right; margin-top:20px;">
                    <h3>TOTAL PAGADO: $<?= number_format((float)$recibo['monto'], 2) ?></h3>
                </div>

                <p style="margin-top:40px; color:var(--text-muted);">
                    <i class="fa-solid fa-circle-check" style="color:#22c55e;"></i>
                    Pago verificado y aprobado por la administración del conjunto.
                </p>

                <div style="display:flex; justify-content:space-between; margin-top:60px;">
                    <div style="text-align:center; width:40%;">
                        <div style="border-top:1px solid #333;"></div>
                        <small>Firma del residente</small>
                    </div>
                    <div style="text-align:center; width:40%;">
                        <div style="border-top:1px solid #333;"></div>
                        <small>Administración</small>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="../../public/js/sidebar.js"></script>
</body>
</html>
<?php
    exit;
}

// Modo listado
$stmt = $pdo->prepare("SELECT p.id_pago, p.concepto, p.monto, p.created_at, r.numero_recibo
                       FROM pagos p LEFT JOIN recibos_pago r ON r.id_pago = p.id_pago
                       WHERE p.id_usuario = :u AND p.estado = 'PAGADO'
                       ORDER BY p.created_at DESC");
$stmt->execute([':u' => $id_usuario]);
$recibos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalRecibado = 0;
foreach ($recibos as $r) { $totalRecibado += (float)$r['monto']; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibos de Pago - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-money-check-dollar"></i> Recibos de Pago</h1>
            <p class="subtitle">Comprobantes oficiales de tus pagos aprobados por la administración.</p>
        </header>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <section class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body" style="display:flex; gap:2rem; flex-wrap:wrap;">
                <div><i class="fa-solid fa-scroll"></i> Total recibos: <strong><?= count($recibos) ?></strong></div>
                <div style="color:#22c55e;"><i class="fa-solid fa-sack-dollar"></i> Total pagado: <strong>$<?= number_format($totalRecibado, 2) ?></strong></div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list"></i> Mis Recibos</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N Recibo</th>
                                <th>Fecha de Pago</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recibos)): ?>
                                <tr><td colspan="5" class="text-center">Aun no tienes recibos. Apareceran cuando la administración apruebe tus pagos.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recibos as $r): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($r['numero_recibo'] ?? 'REC-' . str_pad((string)$r['id_pago'], 6, '0', STR_PAD_LEFT)) ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($r['concepto'] ?? 'N/A') ?></td>
                                        <td><strong>$<?= number_format((float)$r['monto'], 2) ?></strong></td>
                                        <td>
                                            <a href="recibos_pago.php?ver=<?= (int)$r['id_pago'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fa-solid fa-file-lines"></i> Ver Recibo
                                            </a>
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
