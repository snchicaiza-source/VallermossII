<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

$stmtUser = $db->query("SELECT id_usuario, nombres, correo, numero_vivienda FROM usuarios WHERE rol = 'RESIDENTE'");
$usuarios = $stmtUser->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT c.*, u.nombres, u.numero_vivienda 
        FROM convenios c 
        INNER JOIN usuarios u ON c.id_usuario = u.id_usuario 
        ORDER BY c.id DESC";
$stmtConv = $db->query($sql);
$convenios = $stmtConv->fetchAll(PDO::FETCH_ASSOC);

$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convenios de Pago - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-handshake"></i> Convenios de Pago</h1>
            <p class="subtitle">Generacion y seguimiento de compromisos de pago para regularizar alicuotas vencidas.</p>
        </header>


        <?php if ($msg === 'creado'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Convenio de pago registrado correctamente.</div>
        <?php elseif ($msg === 'actualizado'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Estado del convenio actualizado.</div>
        <?php elseif ($error === 'campos_vacios'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Por favor llene todos los datos obligatorios.</div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-file-signature"></i> Establecer Nuevo Convenio</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_convenio">

                    <div class="form-group">
                        <label for="id_usuario">Seleccionar Copropietario / Residente</label>
                        <select id="id_usuario" name="id_usuario" class="form-control" required>
                            <option value="">-- Seleccionar Usuario --</option>
                            <?php foreach ($usuarios as $u): ?>
                                <?php $vivienda = !empty($u['numero_vivienda']) ? $u['numero_vivienda'] : 'S/N'; ?>
                                <option value="<?= $u['id_usuario'] ?>">
                                    <?= htmlspecialchars($u['nombres']) ?> (<?= htmlspecialchars($vivienda) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="monto_total">Monto Acordado ($)</label>
                        <input type="number" step="0.01" id="monto_total" name="monto_total" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label for="num_cuotas">Numero de Cuotas / Meses</label>
                        <input type="number" min="1" max="24" id="num_cuotas" name="num_cuotas" class="form-control" placeholder="Ej. 6" required>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-handshake-simple"></i> Registrar Convenio</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list-ol"></i> Registro de Convenios Activos</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Residente</th>
                                <th>Ubicacion</th>
                                <th>Monto Total</th>
                                <th>N Cuotas</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($convenios)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No existen convenios registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($convenios as $c): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($c['nombres']) ?></strong></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($c['numero_vivienda'] ?: 'S/N') ?></span></td>
                                        <td>$<?= number_format($c['monto_total'], 2) ?></td>
                                        <td><?= $c['num_cuotas'] ?> cuota(s)</td>
                                        <td>
                                            <?php 
                                                $stBadge = 'badge-warning';
                                                if ($c['estado'] === 'CUMPLIDO') $stBadge = 'badge-success';
                                                elseif ($c['estado'] === 'INCUMPLIDO') $stBadge = 'badge-danger';
                                                else $stBadge = 'badge-info';
                                            ?>
                                            <span class="badge <?= $stBadge ?>"><?= htmlspecialchars($c['estado']) ?></span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                                        <td>
                                            <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline-block;">
                                                <input type="hidden" name="action" value="cambiar_estado_convenio">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <?php if ($c['estado'] === 'ACTIVO'): ?>
                                                    <button type="submit" name="nuevo_estado" value="CUMPLIDO" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check"></i></button>
                                                    <button type="submit" name="nuevo_estado" value="INCUMPLIDO" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i></button>
                                                <?php else: ?>
                                                    <span class="text-muted">Finalizado</span>
                                                <?php endif; ?>
                                            </form>
                                            <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar este convenio?');">
                                                <input type="hidden" name="action" value="eliminar_convenio">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
