<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

$stmt = $db->query("SELECT * FROM proveedores ORDER BY created_at DESC");
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-truck-field"></i> Gestion de Proveedores</h1>
            <p class="subtitle">Administracion de contratos y proveedores del conjunto.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-plus-circle"></i> Registrar Nuevo Proveedor</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_proveedor">

                    <div class="form-group">
                        <label for="nombre_empresa">Nombre de la Empresa</label>
                        <input type="text" id="nombre_empresa" name="nombre_empresa" class="form-control" placeholder="Ej. Mantenimientos del Sur C.A." required>
                    </div>

                    <div class="form-group">
                        <label for="servicio_rubro">Servicio / Rubro</label>
                        <input type="text" id="servicio_rubro" name="servicio_rubro" class="form-control" placeholder="Ej. Mantenimiento de elevadores" required>
                    </div>

                    <div class="form-group">
                        <label for="contacto">Contacto (Telefono / Email)</label>
                        <input type="text" id="contacto" name="contacto" class="form-control" placeholder="Ej. 0414-1234567">
                    </div>

                    <div class="form-group">
                        <label for="monto_contrato">Monto del Contrato ($)</label>
                        <input type="number" step="0.01" id="monto_contrato" name="monto_contrato" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label for="estado_pago">Estado de Pago</label>
                        <select id="estado_pago" name="estado_pago" class="form-control" required>
                            <option value="AL_DIA">AL DIA</option>
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="EN_PROCESO">EN PROCESO</option>
                        </select>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Registrar Proveedor</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list"></i> Proveedores Registrados</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Servicio / Rubro</th>
                                <th>Contacto</th>
                                <th>Monto Contrato</th>
                                <th>Estado Pago</th>
                                <th>Fecha Alta</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($proveedores)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay proveedores registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($proveedores as $prov): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($prov['nombre_empresa']) ?></strong></td>
                                        <td><?= htmlspecialchars($prov['servicio_rubro']) ?></td>
                                        <td><?= htmlspecialchars($prov['contacto'] ?? 'N/A') ?></td>
                                        <td>$<?= number_format($prov['monto_contrato'], 2) ?></td>
                                        <td>
                                            <?php if ($prov['estado_pago'] === 'AL_DIA'): ?>
                                                <span class="badge badge-success">AL DIA</span>
                                            <?php elseif ($prov['estado_pago'] === 'PENDIENTE'): ?>
                                                <span class="badge badge-danger">PENDIENTE</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">EN PROCESO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($prov['created_at'])) ?></td>
                                        <td>
                                            <?php if ($prov['estado_pago'] !== 'AL_DIA'): ?>
                                                <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="cambiar_estado_proveedor">
                                                    <input type="hidden" name="id_proveedor" value="<?= $prov['id_proveedor'] ?>">
                                                    <button type="submit" name="nuevo_estado" value="AL_DIA" class="btn btn-sm btn-outline-success" title="Marcar AL DIA"><i class="fa-solid fa-check"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($prov['estado_pago'] !== 'PENDIENTE'): ?>
                                                <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="cambiar_estado_proveedor">
                                                    <input type="hidden" name="id_proveedor" value="<?= $prov['id_proveedor'] ?>">
                                                    <button type="submit" name="nuevo_estado" value="PENDIENTE" class="btn btn-sm btn-outline-danger" title="Marcar PENDIENTE"><i class="fa-solid fa-xmark"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($prov['estado_pago'] !== 'EN_PROCESO'): ?>
                                                <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="cambiar_estado_proveedor">
                                                    <input type="hidden" name="id_proveedor" value="<?= $prov['id_proveedor'] ?>">
                                                    <button type="submit" name="nuevo_estado" value="EN_PROCESO" class="btn btn-sm btn-outline-warning" title="Marcar EN PROCESO"><i class="fa-solid fa-clock"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar este proveedor?');">
                                                <input type="hidden" name="action" value="eliminar_proveedor">
                                                <input type="hidden" name="id_proveedor" value="<?= $prov['id_proveedor'] ?>">
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
