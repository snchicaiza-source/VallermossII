<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['RESIDENTE']);

$id_usuario = $_SESSION['id_usuario'];
$pdo = Database::obtenerConexion();

$stmt = $pdo->prepare("SELECT * FROM pagos WHERE id_usuario = :id ORDER BY created_at DESC");
$stmt->execute([':id' => $id_usuario]);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pagos - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-file-invoice-dollar"></i> Reporte de Pagos</h1>
            <p class="subtitle">Historial completo de tus pagos registrados.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h2><i class="fa-solid fa-cloud-arrow-up"></i> Registrar Nuevo Pago</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/ResidenteController.php" method="POST" enctype="multipart/form-data" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <input type="hidden" name="action" value="subir_pago">
                    <div>
                        <label for="concepto">Concepto *</label>
                        <input type="text" id="concepto" name="concepto" required placeholder="Ej: Mantenimiento mensual" style="width:100%; padding:0.5rem; border:1px solid #ccc; border-radius:4px;">
                    </div>
                    <div>
                        <label for="monto">Monto *</label>
                        <input type="number" id="monto" name="monto" required step="0.01" min="0.01" placeholder="0.00" style="width:100%; padding:0.5rem; border:1px solid #ccc; border-radius:4px;">
                    </div>
                    <div>
                        <label for="comprobante">Comprobante de pago (jpg, png, pdf)</label>
                        <input type="file" id="comprobante" name="comprobante" accept=".jpg,.jpeg,.png,.pdf" style="width:100%; padding:0.5rem;">
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <button type="submit" class="btn btn-primary" style="padding:0.6rem 1.5rem; cursor:pointer;">
                            <i class="fa-solid fa-paper-plane"></i> Enviar Pago
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list"></i> Historial de Pagos</h2>
                <span class="badge badge-info"><?= count($pagos) ?> registro(s)</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha Registro</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Comprobante</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pagos)): ?>
                                <tr><td colspan="6" class="text-center">No tienes pagos registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pagos as $p): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($p['concepto'] ?? 'N/A') ?></td>
                                        <td><strong>$<?= number_format($p['monto'], 2) ?></strong></td>
                                        <td>
                                            <?php $est = $p['estado']; ?>
                                            <?php if ($est === 'PAGADO'): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> PAGADO</span>
                                            <?php elseif ($est === 'EN_REVISION'): ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> EN REVISIÓN</span>
                                            <?php elseif ($est === 'RECHAZADO'): ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> RECHAZADO</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-hourglass"></i> PENDIENTE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($p['comprobante_url'])): ?>
                                                <a href="../../<?= htmlspecialchars($p['comprobante_url']) ?>" target="_blank" class="btn btn-sm" style="font-size: 0.8rem;">
                                                    <i class="fa-solid fa-file-pdf"></i> Ver
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Sin comprobante</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="../../controllers/ResidenteController.php" method="POST" style="display:inline;" onsubmit="return confirm('Seguro que desea eliminar este pago?');">
                                                <input type="hidden" name="action" value="eliminar_pago">
                                                <input type="hidden" name="id_pago" value="<?= $p['id_pago'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" style="font-size:0.8rem;">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
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
