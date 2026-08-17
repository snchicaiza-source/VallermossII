<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

// Obtener usuarios residentes para el selector
$stmtUser = $db->query("SELECT id_usuario, nombres, correo FROM usuarios");
$usuarios = $stmtUser->fetchAll(PDO::FETCH_ASSOC);

// Obtener convenios con datos del usuario
$sql = "SELECT c.*, u.nombres, u.puesto_casa 
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
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Vallermosso II</h2>
            <span class="user-badge"><i class="fa-solid fa-user-shield"></i> Administrador</span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="comunicados.php" class="nav-link"><i class="fa-solid fa-bullhorn"></i> <span>Comunicados</span></a>
            </li>
            <li class="nav-item">
                <a href="verificar_pagos.php" class="nav-link"><i class="fa-solid fa-receipt"></i> <span>Auditar Pagos</span></a>
            </li>
            <li class="nav-item">
                <a href="usuarios.php" class="nav-link"><i class="fa-solid fa-users-gear"></i> <span>Control de Accesos</span></a>
            </li>
            <li class="nav-item">
                <a href="activos.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> <span>Bienes y Activos</span></a>
            </li>
            <li class="nav-item">
                <a href="convenios.php" class="nav-link active"><i class="fa-solid fa-handshake"></i> <span>Convenios</span></a>
            </li>
            <li class="nav-item">
                <a href="tramites.php" class="nav-link"><i class="fa-solid fa-folder-open"></i> <span>Trámites</span></a>
            </li>
            <li class="nav-item logout-section">
                <form action="../../controllers/AuthController.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-danger btn-block"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</button>
                </form>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-handshake"></i> Convenios de Pago</h1>
            <p class="subtitle">Generación y seguimiento de compromisos de pago para regularizar alícuotas vencidas.</p>
        </header>

        <?php if ($msg === 'creado'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-check"></i> Convenio de pago registrado correctamente.</div>
        <?php elseif ($msg === 'actualizado'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-check"></i> Estado del convenio actualizado.</div>
        <?php elseif ($error === 'campos_vacios'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Por favor llene todos los datos obligatorios.</div>
        <?php endif; ?>

        <!-- Formulario para Convenios -->
        <section class="card form-card" style="margin-bottom: 2rem;">
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
                                <?php
                                    $vivienda = !empty($u['numero_vivienda']) ? $u['numero_vivienda'] : 'S/N';
                                 ?>
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
                        <label for="num_cuotas">Número de Cuotas / Meses</label>
                        <input type="number" min="1" max="24" id="num_cuotas" name="num_cuotas" class="form-control" placeholder="Ej. 6" required>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-handshake-simple"></i> Registrar Convenio</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Tabla de Convenios -->
        <section class="card table-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list-ol"></i> Registro de Convenios Activos</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Residente</th>
                                <th>Ubicación</th>
                                <th>Monto Total</th>
                                <th>N° Cuotas</th>
                                <th>Estado</th>
                                <th>Fecha Acordada</th>
                                <th>Acción</th>
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
                                        <td><span class="badge badge-info"><?= htmlspecialchars($c['puesto_casa'] ?: 'S/N') ?></span></td>
                                        <td>$<?= number_format($c['monto_total'], 2) ?></td>
                                        <td><?= $c['num_cuotas'] ?> cuota(s)</td>
                                        <td>
                                            <?php 
                                                $stBadge = 'badge-warning';
                                                if ($c['estado'] === 'CUMPLIDO') $stBadge = 'badge-success';
                                                elseif ($c['estado'] === 'INCUMPLIDO') $stBadge = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $stBadge ?>"><?= $c['estado'] ?></span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                                        <td>
                                            <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline-block;">
                                                <input type="hidden" name="action" value="cambiar_estado_convenio">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <?php if ($c['estado'] === 'ACTIVO'): ?>
                                                    <button type="submit" name="nuevo_estado" value="CUMPLIDO" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check"></i> Cumplido</button>
                                                    <button type="submit" name="nuevo_estado" value="INCUMPLIDO" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i> Incumplido</button>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin acciones</span>
                                                <?php endif; ?>
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

</body>
</html>