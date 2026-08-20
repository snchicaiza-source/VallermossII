<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

$stmtUsuarios = $db->query("SELECT id_usuario, nombres, numero_vivienda FROM usuarios WHERE rol = 'RESIDENTE' ORDER BY nombres");
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT r.*, u.nombres, u.numero_vivienda FROM recaudaciones r LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario ORDER BY r.fecha_registro DESC");
$recaudaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recaudaciones - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-cash-register"></i> Gestion de Recaudaciones</h1>
            <p class="subtitle">Registro y control de cobros realizados a los copropietarios.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-plus-circle"></i> Registrar Nueva Recaudacion</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_recaudacion">

                    <div class="form-group">
                        <label for="id_usuario">Residente</label>
                        <select id="id_usuario" name="id_usuario" class="form-control" required>
                            <option value="">-- Seleccionar Residente --</option>
                            <?php foreach ($usuarios as $u): ?>
                                <?php $vivienda = !empty($u['numero_vivienda']) ? $u['numero_vivienda'] : 'S/N'; ?>
                                <option value="<?= $u['id_usuario'] ?>">
                                    <?= htmlspecialchars($u['nombres']) ?> (<?= htmlspecialchars($vivienda) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="concepto">Concepto</label>
                        <input type="text" id="concepto" name="concepto" class="form-control" placeholder="Ej. Alicuota mensual Enero 2026" required>
                    </div>

                    <div class="form-group">
                        <label for="monto">Monto ($)</label>
                        <input type="number" step="0.01" id="monto" name="monto" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha_pago">Fecha de Pago</label>
                        <input type="date" id="fecha_pago" name="fecha_pago" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="observacion">Observacion</label>
                        <input type="text" id="observacion" name="observacion" class="form-control" placeholder="Opcional...">
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Registrar Recaudacion</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list"></i> Registro de Recaudaciones</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Residente</th>
                                <th>Vivienda</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Fecha Pago</th>
                                <th>Estado</th>
                                <th>Observacion</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recaudaciones)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No hay recaudaciones registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recaudaciones as $r): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($r['nombres'] ?? 'N/A') ?></strong></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($r['numero_vivienda'] ?? 'S/N') ?></span></td>
                                        <td><?= htmlspecialchars($r['concepto']) ?></td>
                                        <td>$<?= number_format($r['monto'], 2) ?></td>
                                        <td><?= date('d/m/Y', strtotime($r['fecha_pago'])) ?></td>
                                        <td>
                                            <?php
                                                $badge = 'badge-warning';
                                                if ($r['estado_pago'] === 'APROBADO') $badge = 'badge-success';
                                                elseif ($r['estado_pago'] === 'RECHAZADO') $badge = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= htmlspecialchars($r['estado_pago']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($r['observacion'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($r['estado_pago'] === 'PENDIENTE'): ?>
                                                <div class="btn-group">
                                                    <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="cambiar_estado_recaudacion">
                                                        <input type="hidden" name="id_pago" value="<?= $r['id_pago'] ?>">
                                                        <button type="submit" name="nuevo_estado" value="APROBADO" class="btn btn-sm btn-success" title="Aprobar"><i class="fa-solid fa-check"></i></button>
                                                    </form>
                                                    <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="cambiar_estado_recaudacion">
                                                        <input type="hidden" name="id_pago" value="<?= $r['id_pago'] ?>">
                                                        <button type="submit" name="nuevo_estado" value="RECHAZADO" class="btn btn-sm btn-danger" title="Rechazar"><i class="fa-solid fa-xmark"></i></button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Finalizado</span>
                                            <?php endif; ?>
                                            <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar esta recaudacion?');">
                                                <input type="hidden" name="action" value="eliminar_recaudacion">
                                                <input type="hidden" name="id_pago" value="<?= $r['id_pago'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
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
