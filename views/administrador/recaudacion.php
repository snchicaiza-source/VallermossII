<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

$stmtUsuarios = $db->query("SELECT id_usuario, nombres, numero_vivienda FROM usuarios WHERE rol = 'RESIDENTE' ORDER BY nombres");
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

// Filtros server-side (residente, estado, rango de fechas) y paginacion
$filtroResidente = (int)($_GET['filtro_residente'] ?? 0);
$filtroEstado = strtoupper(trim($_GET['filtro_estado'] ?? ''));
$filtroDesde = trim($_GET['filtro_desde'] ?? '');
$filtroHasta = trim($_GET['filtro_hasta'] ?? '');

$condiciones = [];
$paramsFiltro = [];
if ($filtroResidente > 0) { $condiciones[] = "r.id_usuario = :fres"; $paramsFiltro[':fres'] = $filtroResidente; }
if (in_array($filtroEstado, ['PENDIENTE', 'APROBADO', 'RECHAZADO'], true)) { $condiciones[] = "r.estado_pago = :fest"; $paramsFiltro[':fest'] = $filtroEstado; }
if ($filtroDesde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtroDesde)) { $condiciones[] = "r.fecha_pago >= :fdesde"; $paramsFiltro[':fdesde'] = $filtroDesde; }
if ($filtroHasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtroHasta)) { $condiciones[] = "r.fecha_pago <= :fhasta"; $paramsFiltro[':fhasta'] = $filtroHasta; }
$whereSql = empty($condiciones) ? '' : 'WHERE ' . implode(' AND ', $condiciones);

$porPagina = 10;
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($pagina - 1) * $porPagina;

try {
    $stmtTotal = $db->prepare("SELECT COUNT(*) FROM recaudaciones r $whereSql");
    $stmtTotal->execute($paramsFiltro);
    $totalRecaudaciones = (int)$stmtTotal->fetchColumn();

    $stmt = $db->prepare("SELECT r.*, u.nombres, u.numero_vivienda FROM recaudaciones r LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario $whereSql ORDER BY r.fecha_registro DESC LIMIT :limite OFFSET :offset");
    foreach ($paramsFiltro as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $recaudaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $totalRecaudaciones = 0;
    $recaudaciones = [];
}

// Valores restaurados tras un error de validacion
$formOldRecaudacion = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recaudaciones - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/tablas.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-cash-register"></i> Gestión de Recaudaciones</h1>
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
                <h2><i class="fa-solid fa-plus-circle"></i> Registrar Nueva Recaudación</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_recaudacion">

                    <div class="form-group">
                        <label for="id_usuario">Residente</label>
                        <?php $selResidente = (string)($formOldRecaudacion['id_usuario'] ?? ''); ?>
                        <select id="id_usuario" name="id_usuario" class="form-control" required>
                            <option value="">-- Seleccionar Residente --</option>
                            <?php foreach ($usuarios as $u): ?>
                                <?php $vivienda = !empty($u['numero_vivienda']) ? $u['numero_vivienda'] : 'S/N'; ?>
                                <option value="<?= $u['id_usuario'] ?>" <?= $selResidente === (string)$u['id_usuario'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['nombres']) ?> (<?= htmlspecialchars($vivienda) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="concepto">Concepto</label>
                        <input type="text" id="concepto" name="concepto" class="form-control" placeholder="Ej. Alícuota mensual Enero 2026" maxlength="150" required value="<?= htmlspecialchars($formOldRecaudacion['concepto'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="monto">Monto ($)</label>
                        <input type="text" inputmode="decimal" pattern="^\d+(\.\d{1,2})?$" title="Número positivo con máximo 2 decimales. Ej: 150 o 150.50" id="monto" name="monto" class="form-control" placeholder="Ej. 150.50" maxlength="13" required data-validar="dinero" value="<?= htmlspecialchars($formOldRecaudacion['monto'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="fecha_pago">Fecha de Pago</label>
                        <input type="date" id="fecha_pago" name="fecha_pago" class="form-control" required value="<?= htmlspecialchars($formOldRecaudacion['fecha_pago'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="observacion">Observación</label>
                        <input type="text" id="observacion" name="observacion" class="form-control" placeholder="Opcional..." maxlength="255" value="<?= htmlspecialchars($formOldRecaudacion['observacion'] ?? '') ?>">
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Registrar Recaudación</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-calendar-days"></i> Generar Cobros Mensuales (Alícuota)</h2>
            </div>
            <div class="card-body">
                <p class="text-muted" style="margin-top:0;">
                    Crea automáticamente el cobro mensual <strong>PENDIENTE</strong> para todos los residentes.
                    Se omiten las alícuotas que ya fueron generadas para el mes y año seleccionados.
                </p>
                <?php if (isset($_SESSION['flash_cuotas'])): ?>
                    <div class="alert <?= strpos($_SESSION['flash_cuotas'], 'No se generó') === 0 ? 'alert-warning' : 'alert-success' ?>">
                        <i class="fa-solid fa-circle-info"></i> <?= $_SESSION['flash_cuotas']; unset($_SESSION['flash_cuotas']); ?>
                    </div>
                <?php endif; ?>
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="generar_cuotas_mensuales">

                    <div class="form-group">
                        <label for="cuota_monto">Monto por residente ($)</label>
                        <input type="text" inputmode="decimal" id="cuota_monto" name="monto" class="form-control" placeholder="Ej. 35.00" maxlength="13" required data-validar="dinero">
                    </div>

                    <div class="form-group">
                        <label for="cuota_mes">Mes</label>
                        <select id="cuota_mes" name="mes" class="form-control" required>
                            <?php
                            $meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                            $mesActual = (int)date('n');
                            foreach ($meses as $num => $nombre): ?>
                                <option value="<?= $num ?>" <?= $num === $mesActual ? 'selected' : '' ?>><?= $nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cuota_anio">Año</label>
                        <input type="number" id="cuota_anio" name="anio" class="form-control" min="2020" max="2100" value="<?= date('Y') ?>" required data-validar="enteropositivo">
                    </div>

                    <div class="form-group">
                        <label for="cuota_fecha">Fecha de pago límite</label>
                        <input type="date" id="cuota_fecha" name="fecha_pago" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Se generará un cobro pendiente para cada residente. ¿Continuar?');">
                            <i class="fa-solid fa-bolt"></i> Generar Cobros del Mes
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list"></i> Registro de Recaudaciones</h2>
            </div>
            <div class="card-body">
                <form method="GET" class="tabla-toolbar">
                    <div class="filtro-grupo">
                        <span class="filtro-etiqueta">Residente</span>
                        <select name="filtro_residente" class="filtro-tabla">
                            <option value="">Todos los residentes</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u['id_usuario'] ?>" <?= $filtroResidente === (int)$u['id_usuario'] ? 'selected' : '' ?>><?= htmlspecialchars($u['nombres']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <span class="filtro-etiqueta">Estado</span>
                        <select name="filtro_estado" class="filtro-tabla">
                            <option value="">Todos los estados</option>
                            <option value="PENDIENTE" <?= $filtroEstado === 'PENDIENTE' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="APROBADO" <?= $filtroEstado === 'APROBADO' ? 'selected' : '' ?>>Aprobado</option>
                            <option value="RECHAZADO" <?= $filtroEstado === 'RECHAZADO' ? 'selected' : '' ?>>Rechazado</option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <span class="filtro-etiqueta">Fecha desde</span>
                        <input type="date" name="filtro_desde" class="filtro-tabla" value="<?= htmlspecialchars($filtroDesde) ?>">
                    </div>
                    <div class="filtro-grupo">
                        <span class="filtro-etiqueta">Fecha hasta</span>
                        <input type="date" name="filtro_hasta" class="filtro-tabla" value="<?= htmlspecialchars($filtroHasta) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filtrar</button>
                    <a href="recaudacion.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-eraser"></i> Limpiar</a>
                </form>
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
                                <th>Observación</th>
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
                <?php
                $total = $totalRecaudaciones;
                $ancla = '';
                include __DIR__ . '/../partials/paginacion.php';
                ?>
            </div>
        </section>
    </main>
</div>
<script src="../../public/js/sidebar.js"></script>
<script src="../../public/js/tablas.js?v=3"></script>
</body>
</html>
