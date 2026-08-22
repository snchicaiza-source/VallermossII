<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

$stmtUsuarios = $db->query("SELECT id_usuario, nombres, numero_vivienda FROM usuarios WHERE rol = 'RESIDENTE' ORDER BY nombres");
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

$usuarioSeleccionado = null;
$pagosUsuario = [];
$totalPagado = 0;
$totalPendiente = 0;
$numVivienda = '';

if (isset($_GET['id_usuario']) && $_GET['id_usuario'] > 0) {
    $idSel = (int)$_GET['id_usuario'];

    $stmtUser = $db->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
    $stmtUser->execute([':id' => $idSel]);
    $usuarioSeleccionado = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($usuarioSeleccionado) {
        $numVivienda = $usuarioSeleccionado['numero_vivienda'] ?? 'S/N';

        $stmtPagos = $db->prepare("SELECT * FROM pagos WHERE id_usuario = :id_usuario ORDER BY created_at DESC");
        $stmtPagos->execute([':id_usuario' => $idSel]);
        $pagosUsuario = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pagosUsuario as $p) {
            if ($p['estado'] === 'APROBADO' || $p['estado'] === 'PAGADO') {
                $totalPagado += $p['monto'];
            } else {
                $totalPendiente += $p['monto'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Expensas - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-certificate"></i> Certificado de Expensas</h1>
            <p class="subtitle">Generacion de certificados de pago y estado de cuenta individual.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-magnifying-glass"></i> Seleccionar Residente</h2>
            </div>
            <div class="card-body">
                <form method="GET" class="grid-form">
                    <div class="form-group">
                        <label for="id_usuario">Copropietario / Residente</label>
                        <select id="id_usuario" name="id_usuario" class="form-control" required>
                            <option value="">-- Seleccionar Residente --</option>
                            <?php foreach ($usuarios as $u): ?>
                                <?php $vivienda = !empty($u['numero_vivienda']) ? $u['numero_vivienda'] : 'S/N'; ?>
                                <option value="<?= $u['id_usuario'] ?>" <?= (isset($_GET['id_usuario']) && $_GET['id_usuario'] == $u['id_usuario']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['nombres']) ?> (<?= htmlspecialchars($vivienda) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Generar Certificado</button>
                    </div>
                </form>
            </div>
        </section>

        <?php if ($usuarioSeleccionado): ?>
        <div class="certificado-actions no-print" style="text-align: center; margin-bottom: 16px;">
            <button onclick="window.print()" class="btn btn-primary" style="padding: 12px 32px; font-size: 1.1rem;">
                <i class="fa-solid fa-print"></i> Imprimir Certificado
            </button>
        </div>
        <section class="card" id="certificado-area">
            <div class="card-header" style="justify-content: space-between;">
                <h2><i class="fa-solid fa-file-lines"></i> Certificado de Expensas - <?= htmlspecialchars($usuarioSeleccionado['nombres']) ?></h2>
                <button onclick="window.print()" class="btn btn-outline-primary" style="padding: 8px 20px; font-weight: 600;"><i class="fa-solid fa-print"></i> Imprimir</button>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <p><strong>Residente:</strong> <?= htmlspecialchars($usuarioSeleccionado['nombres']) ?></p>
                        <p><strong>Cédula:</strong> <?= htmlspecialchars($usuarioSeleccionado['cedula'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p><strong>Vivienda:</strong> <?= htmlspecialchars($numVivienda) ?></p>
                        <p><strong>Correo:</strong> <?= htmlspecialchars($usuarioSeleccionado['correo'] ?? 'N/A') ?></p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px; padding: 16px; background: var(--bg-secondary, #f8f9fa); border-radius: 8px;">
                    <div style="text-align: center;">
                        <p style="color: var(--text-muted); font-size: 0.85rem;">Total Pagado</p>
                        <h3 style="color: var(--success, #28a745);">$<?= number_format($totalPagado, 2) ?></h3>
                    </div>
                    <div style="text-align: center;">
                        <p style="color: var(--text-muted); font-size: 0.85rem;">Total Pendiente</p>
                        <h3 style="color: var(--danger, #dc3545);">$<?= number_format($totalPendiente, 2) ?></h3>
                    </div>
                    <div style="text-align: center;">
                        <p style="color: var(--text-muted); font-size: 0.85rem;">Alícuota</p>
                        <h3 style="color: var(--primary, #4a6cf7);"><?= htmlspecialchars($usuarioSeleccionado['alicuota'] ?? 'N/A') ?></h3>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pagosUsuario)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay registros de pagos para este residente.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pagosUsuario as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['concepto']) ?></td>
                                        <td>$<?= number_format($p['monto'], 2) ?></td>
                                        <td>
                                            <?php
                                                $badge = 'badge-warning';
                                                if ($p['estado'] === 'APROBADO' || $p['estado'] === 'PAGADO') $badge = 'badge-success';
                                                elseif ($p['estado'] === 'RECHAZADO') $badge = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= htmlspecialchars($p['estado']) ?></span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 24px; text-align: center; color: var(--text-muted); font-size: 0.8rem;">
                    <p>Certificado emitido el <?= date('d/m/Y H:i') ?> - Conjunto Residencial Vallermosso II</p>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>
</div>
<script src="../../public/js/sidebar.js"></script>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #certificado-area,
        #certificado-area * {
            visibility: visible;
        }
        #certificado-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            box-shadow: none;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        #certificado-area .card-header {
            display: none;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
</body>
</html>
