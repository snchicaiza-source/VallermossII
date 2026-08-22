<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

// Auto-reparacion de esquema (misma que el controlador) para que la pagina nunca falle
try {
    $col = $db->query("SHOW COLUMNS FROM activos LIKE 'costo_aproximado'")->fetch();
    if (!$col) { $db->exec("ALTER TABLE activos ADD COLUMN costo_aproximado DECIMAL(10,2) DEFAULT NULL"); }
} catch (PDOException $e) { /* noop */ }

try {
    $col = $db->query("SHOW COLUMNS FROM proveedores LIKE 'telefono'")->fetch();
    if (!$col) {
        $db->exec("ALTER TABLE proveedores ADD COLUMN telefono VARCHAR(50) DEFAULT NULL");
        $db->exec("UPDATE proveedores SET telefono = contacto WHERE (telefono IS NULL OR telefono = '') AND contacto IS NOT NULL AND contacto != ''");
    }
    $col = $db->query("SHOW COLUMNS FROM proveedores LIKE 'email'")->fetch();
    if (!$col) { $db->exec("ALTER TABLE proveedores ADD COLUMN email VARCHAR(100) DEFAULT NULL"); }
} catch (PDOException $e) { /* noop */ }

$db->exec("CREATE TABLE IF NOT EXISTS contratos (
    id_contrato INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT NOT NULL,
    servicio VARCHAR(200) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE DEFAULT NULL,
    monto DECIMAL(10,2) NOT NULL,
    tipo_monto ENUM('MENSUAL','TOTAL') DEFAULT 'TOTAL',
    documento_pdf VARCHAR(255) DEFAULT NULL,
    estado_orden ENUM('PENDIENTE_ACTA','LISTO_PAGO','PAGADO') DEFAULT 'PENDIENTE_ACTA',
    estado ENUM('VIGENTE','FINALIZADO','CANCELADO') DEFAULT 'VIGENTE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$db->exec("CREATE TABLE IF NOT EXISTS actas_recepcion (
    id_acta INT AUTO_INCREMENT PRIMARY KEY,
    id_contrato INT NOT NULL,
    conforme TINYINT(1) NOT NULL DEFAULT 1,
    detalle TEXT DEFAULT NULL,
    recibido_por VARCHAR(150) NOT NULL,
    fecha_acta TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$db->exec("CREATE TABLE IF NOT EXISTS pagos_proveedores (
    id_pago_prov INT AUTO_INCREMENT PRIMARY KEY,
    id_contrato INT NOT NULL,
    numero_factura VARCHAR(50) NOT NULL,
    metodo_pago ENUM('EFECTIVO','TRANSFERENCIA','CHEQUE') NOT NULL,
    cuenta_origen VARCHAR(100) DEFAULT NULL,
    monto_pagado DECIMAL(10,2) NOT NULL,
    fecha_pago TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$proveedores = $db->query("SELECT * FROM proveedores ORDER BY nombre_empresa")->fetchAll(PDO::FETCH_ASSOC);

$contratos = $db->query("SELECT c.*, p.nombre_empresa
                         FROM contratos c LEFT JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                         ORDER BY c.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$actas = $db->query("SELECT a.*, c.servicio, p.nombre_empresa
                     FROM actas_recepcion a
                     LEFT JOIN contratos c ON a.id_contrato = c.id_contrato
                     LEFT JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                     ORDER BY a.fecha_acta DESC")->fetchAll(PDO::FETCH_ASSOC);

$pagosProv = $db->query("SELECT g.*, c.servicio, p.nombre_empresa
                         FROM pagos_proveedores g
                         LEFT JOIN contratos c ON g.id_contrato = c.id_contrato
                         LEFT JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                         ORDER BY g.fecha_pago DESC")->fetchAll(PDO::FETCH_ASSOC);

$contratosPendientesActa = array_filter($contratos, function ($c) { return $c['estado_orden'] === 'PENDIENTE_ACTA'; });
$contratosListosPago = array_filter($contratos, function ($c) { return $c['estado_orden'] === 'LISTO_PAGO'; });

function badgeOrden($e) {
    if ($e === 'PAGADO') return '<span class="badge badge-success">PAGADO</span>';
    if ($e === 'LISTO_PAGO') return '<span class="badge badge-info">LISTO PARA PAGO</span>';
    return '<span class="badge badge-warning">PENDIENTE DE ACTA</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratacion de Proveedores - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/tablas.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-truck-field"></i> Contratacion con Proveedores</h1>
        </header>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_warning'])): ?>
            <div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation"></i> <?= $_SESSION['flash_warning']; unset($_SESSION['flash_warning']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <!-- ============ 1. PROVEEDORES (REGISTRO) ============ -->
        <section class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h2><i class="fa-solid fa-address-book"></i> 1. Registro de Proveedores</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_proveedor">
                    <div class="form-group">
                        <label for="nombre_empresa">Nombre / Razón Social (empresa o persona)</label>
                        <input type="text" id="nombre_empresa" name="nombre_empresa" class="form-control" placeholder="Ej. Seguridad S.A. o Juan Perez" required>
                    </div>
                    <div class="form-group">
                        <label for="servicio_rubro">Servicio / Rubro</label>
                        <input type="text" id="servicio_rubro" name="servicio_rubro" class="form-control" placeholder="Ej. Vigilancia" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control" placeholder="Ej. 0987654321" maxlength="13" inputmode="tel" data-validar="telefono" data-solo-digitos="13" data-permite-mas>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Ej. contacto@empresa.com">
                    </div>
                    <div class="form-group">
                        <label for="estado_pago">Estado de Pago</label>
                        <select id="estado_pago" name="estado_pago" class="form-control" required>
                            <option value="AL_DIA">AL DÍA</option>
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="EN_PROCESO">EN PROCESO</option>
                        </select>
                    </div>
                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Registrar Proveedor</button>
                    </div>
                </form>

                <div class="table-responsive" style="margin-top:1rem;">
                    <div class="tabla-toolbar">
                        <div class="filtro-grupo"><span class="filtro-etiqueta">Buscar</span><div class="buscador-tabla">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" data-buscar="tablaProveedores" placeholder="Buscar proveedor, rubro, teléfono..."></div></div>
                    </div>
                    <table class="table" id="tablaProveedores" data-por-pagina="8">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>Servicio / Rubro</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Estado Pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($proveedores)): ?>
                                <tr><td colspan="6" class="text-center">No hay proveedores registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($proveedores as $prov): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($prov['nombre_empresa']) ?></strong></td>
                                        <td><?= htmlspecialchars($prov['servicio_rubro']) ?></td>
                                        <td><?= htmlspecialchars($prov['telefono'] ?? $prov['contacto'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($prov['email'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($prov['estado_pago'] === 'AL_DIA'): ?>
                                                <span class="badge badge-success">AL DÍA</span>
                                            <?php elseif ($prov['estado_pago'] === 'PENDIENTE'): ?>
                                                <span class="badge badge-danger">PENDIENTE</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">EN PROCESO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($prov['estado_pago'] !== 'AL_DIA'): ?>
                                                <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="cambiar_estado_proveedor">
                                                    <input type="hidden" name="id_proveedor" value="<?= $prov['id_proveedor'] ?>">
                                                    <button type="submit" name="nuevo_estado" value="AL_DIA" class="btn btn-sm btn-outline-success" title="Marcar AL DÍA"><i class="fa-solid fa-check"></i></button>
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

        <!-- ============ 2. CONTRATOS ============ -->
        <section class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h2><i class="fa-solid fa-file-signature"></i> 2. Contratos</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" enctype="multipart/form-data" class="grid-form">
                    <input type="hidden" name="action" value="crear_contrato">
                    <div class="form-group">
                        <label for="id_proveedor">Proveedor *</label>
                        <select id="id_proveedor" name="id_proveedor" class="form-control" required>
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($proveedores as $p): ?>
                                <option value="<?= $p['id_proveedor'] ?>"><?= htmlspecialchars($p['nombre_empresa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="servicio">Servicio del Contrato *</label>
                        <input type="text" id="servicio" name="servicio" class="form-control" placeholder="Ej. Mantenimiento de elevadores" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha Inicio *</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin (vencimiento)</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="monto">Monto ($) *</label>
                        <input type="text" inputmode="decimal" pattern="^\d+(\.\d{1,2})?$" title="Número positivo con máximo 2 decimales. Ej: 150 o 150.50" id="monto" name="monto" class="form-control" placeholder="Ej. 150.50" maxlength="13" required data-validar="dinero">
                    </div>
                    <div class="form-group">
                        <label for="tipo_monto">Tipo de Monto</label>
                        <select id="tipo_monto" name="tipo_monto" class="form-control">
                            <option value="TOTAL">TOTAL</option>
                            <option value="MENSUAL">MENSUAL</option>
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label for="documento_pdf">Contrato firmado (PDF)</label>
                        <input type="file" id="documento_pdf" name="documento_pdf" accept=".pdf" class="form-control">
                    </div>
                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-file-signature"></i> Registrar Contrato</button>
                    </div>
                </form>

                <div class="table-responsive" style="margin-top:1rem;">
                    <div class="tabla-toolbar">
                        <div class="filtro-grupo"><span class="filtro-etiqueta">Buscar</span><div class="buscador-tabla">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" data-buscar="tablaContratos" placeholder="Buscar contrato, servicio, proveedor..."></div></div>
                    </div>
                    <table class="table" id="tablaContratos" data-por-pagina="8">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Proveedor</th>
                                <th>Servicio</th>
                                <th>Vigencia</th>
                                <th>Monto</th>
                                <th>Estado Orden</th>
                                <th>PDF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contratos)): ?>
                                <tr><td colspan="7" class="text-center">No hay contratos registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($contratos as $c): ?>
                                    <?php
                                        $diasRestantes = null;
                                        if (!empty($c['fecha_fin'])) {
                                            $diasRestantes = (int)floor((strtotime($c['fecha_fin']) - time()) / 86400);
                                        }
                                    ?>
                                    <tr>
                                        <td><?= $c['id_contrato'] ?></td>
                                        <td><strong><?= htmlspecialchars($c['nombre_empresa'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($c['servicio']) ?></td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($c['fecha_inicio'])) ?>
                                            <?php if (!empty($c['fecha_fin'])): ?>
                                                &rarr; <?= date('d/m/Y', strtotime($c['fecha_fin'])) ?>
                                                <?php if ($diasRestantes !== null && $diasRestantes <= 30 && $c['estado'] === 'VIGENTE'): ?>
                                                    <br><span class="badge badge-danger"><i class="fa-solid fa-bell"></i> vence en <?= max($diasRestantes, 0) ?> día(s)</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>$<?= number_format($c['monto'], 2) ?> <small class="text-muted"><?= $c['tipo_monto'] ?></small></td>
                                        <td><?= badgeOrden($c['estado_orden']) ?></td>
                                        <td>
                                            <?php if (!empty($c['documento_pdf'])): ?>
                                                <a href="<?= calcularRaizProyecto() ?>/<?= htmlspecialchars($c['documento_pdf']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file-pdf"></i> Ver</a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
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

        <!-- ============ 3. ACTA DE RECEPCION ============ -->
        <section class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h2><i class="fa-solid fa-clipboard-check"></i> 3. Acta de Recepción (Conformidad)</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="registrar_acta">
                    <div class="form-group span-2">
                        <label for="acta_contrato">Selecciona el contrato cuyo trabajo/bien ya verificaste *</label>
                        <select id="acta_contrato" name="id_contrato" class="form-control" required>
                            <option value="">-- Contratos esperando acta --</option>
                            <?php foreach ($contratosPendientesActa as $c): ?>
                                <option value="<?= $c['id_contrato'] ?>">#<?= $c['id_contrato'] ?> &middot; <?= htmlspecialchars($c['nombre_empresa'] ?? '') ?> &middot; <?= htmlspecialchars($c['servicio']) ?> ($<?= number_format($c['monto'], 2) ?> <?= $c['tipo_monto'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($contratosPendientesActa)): ?>
                            <small style="color:var(--text-muted);">No hay contratos pendientes de acta. Primero registra un contrato en la sección 2.</small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="recibido_por">Recibido / Verificado por *</label>
                        <input type="text" id="recibido_por" name="recibido_por" class="form-control" placeholder="Ej. Juan Conserje" required>
                    </div>
                    <div class="form-group span-2">
                        <label for="detalle">Detalle de conformidad</label>
                        <input type="text" id="detalle" name="detalle" class="form-control" placeholder="Ej. Si, se recibieron los 10 galones de cloro para la piscina">
                    </div>
                    <div class="form-group span-full">
                        <label style="margin-right:1rem;"><input type="radio" name="conforme" value="1" checked> <i class="fa-solid fa-circle-check" style="color:green;"></i> Conforme (habilita el pago)</label>
                        <label><input type="radio" name="conforme" value="0"> <i class="fa-solid fa-circle-xmark" style="color:red;"></i> NO conforme (queda observado)</label>
                    </div>
                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-clipboard-check"></i> Firmar Acta de Recepción</button>
                    </div>
                </form>

                <div class="table-responsive" style="margin-top:1rem;">
                    <div class="tabla-toolbar">
                        <div class="filtro-grupo"><span class="filtro-etiqueta">Buscar</span><div class="buscador-tabla">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" data-buscar="tablaActas" placeholder="Buscar acta por contrato, proveedor o receptor..."></div></div>
                    </div>
                    <table class="table" id="tablaActas" data-por-pagina="8">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Contrato</th>
                                <th>Proveedor</th>
                                <th>Recibido por</th>
                                <th>Resultado</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($actas)): ?>
                                <tr><td colspan="6" class="text-center">No hay actas registradas.</td></tr>
                            <?php else: ?>
                                <?php foreach ($actas as $a): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($a['fecha_acta'])) ?></td>
                                        <td>#<?= $a['id_contrato'] ?> (<?= htmlspecialchars($a['servicio'] ?? '') ?>)</td>
                                        <td><strong><?= htmlspecialchars($a['nombre_empresa'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($a['recibido_por']) ?></td>
                                        <td>
                                            <?php if ($a['conforme']): ?>
                                                <span class="badge badge-success">CONFORME</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">NO CONFORME</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($a['detalle'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ============ 4. PAGOS A PROVEEDORES ============ -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-money-bill-wave"></i> 4. Pagos a Proveedores</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="registrar_pago_proveedor">
                    <div class="form-group span-2">
                        <label for="pago_contrato">Selecciona el contrato aprobado en el acta *</label>
                        <select id="pago_contrato" name="id_contrato" class="form-control" required>
                            <option value="">-- Contratos listos para pago --</option>
                            <?php foreach ($contratosListosPago as $c): ?>
                                <option value="<?= $c['id_contrato'] ?>">#<?= $c['id_contrato'] ?> &middot; <?= htmlspecialchars($c['nombre_empresa'] ?? '') ?> ($<?= number_format($c['monto'], 2) ?> <?= $c['tipo_monto'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($contratosListosPago)): ?>
                            <small style="color:var(--text-muted);">No hay contratos listos para pago. Primero firma un acta conforme en la sección 3.</small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="numero_factura">Número de Factura (opcional)</label>
                        <input type="text" id="numero_factura" name="numero_factura" class="form-control" placeholder="Dejar vacio si no hay factura">
                    </div>
                    <div class="form-group">
                        <label for="metodo_pago">Método de Pago *</label>
                        <select id="metodo_pago" name="metodo_pago" class="form-control" required>
                            <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                            <option value="EFECTIVO">EFECTIVO</option>
                            <option value="CHEQUE">CHEQUE</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cuenta_origen">Cuenta Bancaria de Origen</label>
                        <input type="text" id="cuenta_origen" name="cuenta_origen" class="form-control" placeholder="Ej. Banesco Ahorros ****1234">
                    </div>
                    <div class="form-group">
                        <label for="monto_pagado">Monto Pagado ($) *</label>
                        <input type="text" inputmode="decimal" pattern="^\d+(\.\d{1,2})?$" title="Número positivo con máximo 2 decimales. Ej: 150 o 150.50" id="monto_pagado" name="monto_pagado" class="form-control" placeholder="Ej. 150.50" maxlength="13" required data-validar="dinero">
                    </div>
                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-money-bill-wave"></i> Registrar Pago</button>
                    </div>
                </form>

                <div class="table-responsive" style="margin-top:1rem;">
                    <div class="tabla-toolbar">
                        <div class="filtro-grupo"><span class="filtro-etiqueta">Buscar</span><div class="buscador-tabla">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" data-buscar="tablaPagosProv" placeholder="Buscar pago por factura, proveedor o método..."></div></div>
                    </div>
                    <table class="table" id="tablaPagosProv" data-por-pagina="8">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Contrato</th>
                                <th>Proveedor</th>
                                <th>Factura</th>
                                <th>Método</th>
                                <th>Cuenta Origen</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pagosProv)): ?>
                                <tr><td colspan="7" class="text-center">No hay pagos registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pagosProv as $g): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($g['fecha_pago'])) ?></td>
                                        <td>#<?= $g['id_contrato'] ?> (<?= htmlspecialchars($g['servicio'] ?? '') ?>)</td>
                                        <td><strong><?= htmlspecialchars($g['nombre_empresa'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($g['numero_factura']) ?></td>
                                        <td><?= htmlspecialchars($g['metodo_pago']) ?></td>
                                        <td><?= htmlspecialchars($g['cuenta_origen'] ?? '-') ?></td>
                                        <td>$<?= number_format($g['monto_pagado'], 2) ?></td>
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
<script src="../../public/js/tablas.js?v=3"></script>
</body>
</html>
