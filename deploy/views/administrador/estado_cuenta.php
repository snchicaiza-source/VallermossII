<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

// Filtros: estado de cuenta (AL_DIA / PENDIENTE / SIN_MOVIMIENTOS) y busqueda por nombre/vivienda
$filtroEstadoCuenta = strtoupper(trim($_GET['filtro_estado'] ?? ''));
$buscarResidente = trim($_GET['buscar'] ?? '');

// Filtro por estado aplicado en HAVING sobre los agregados
$havingSql = '';
$paramsFiltro = [];
if ($filtroEstadoCuenta === 'AL_DIA') { $havingSql = 'HAVING COALESCE(SUM(CASE WHEN m.pagado = 0 THEN m.monto ELSE 0 END), 0) <= 0'; }
elseif ($filtroEstadoCuenta === 'PENDIENTE') { $havingSql = 'HAVING COALESCE(SUM(CASE WHEN m.pagado = 0 THEN m.monto ELSE 0 END), 0) > 0'; }

$whereBuscar = '';
if ($buscarResidente !== '') {
    $whereBuscar = "AND (u.nombres LIKE :bb OR u.numero_vivienda LIKE :bb)";
    $paramsFiltro[':bb'] = '%' . $buscarResidente . '%';
}

$sqlBase = "
    FROM usuarios u
    LEFT JOIN (
        -- Pagos subidos por residentes (tabla pagos)
        SELECT id_usuario, monto, 1 AS pagado FROM pagos WHERE estado = 'PAGADO'
        UNION ALL
        SELECT id_usuario, monto, 0 AS pagado FROM pagos WHERE estado IN ('PENDIENTE', 'EN_REVISION')
        UNION ALL
        -- Recaudaciones registradas por administracion (tabla recaudaciones)
        SELECT id_usuario, monto, 1 AS pagado FROM recaudaciones WHERE estado_pago = 'APROBADO'
        UNION ALL
        SELECT id_usuario, monto, 0 AS pagado FROM recaudaciones WHERE estado_pago = 'PENDIENTE'
    ) m ON u.id_usuario = m.id_usuario
    WHERE u.rol = 'RESIDENTE' $whereBuscar
    GROUP BY u.id_usuario, u.nombres, u.numero_vivienda
";

try {
    // Total para la paginacion (respeta el filtro de estado)
    $stmtTotal = $db->prepare("SELECT COUNT(*) FROM (SELECT u.id_usuario $sqlBase $havingSql) t");
    $stmtTotal->execute($paramsFiltro);
    $totalEstados = (int)$stmtTotal->fetchColumn();

    $porPagina = 10;
    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $offset = ($pagina - 1) * $porPagina;

    $stmt = $db->prepare("SELECT 
            u.id_usuario,
            u.nombres,
            u.numero_vivienda,
            COALESCE(SUM(CASE WHEN m.pagado = 1 THEN m.monto ELSE 0 END), 0) AS total_pagado,
            COALESCE(SUM(CASE WHEN m.pagado = 0 THEN m.monto ELSE 0 END), 0) AS total_pendiente,
            SUM(CASE WHEN m.pagado = 0 THEN 1 ELSE 0 END) AS pagos_pendientes
        $sqlBase $havingSql ORDER BY u.nombres LIMIT :limite OFFSET :offset");
    foreach ($paramsFiltro as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $totalEstados = 0;
    $estados = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Cuenta - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/tablas.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-file-invoice"></i> Estado de Cuenta de Residentes</h1>
            <p class="subtitle">Resumen de pagos y saldos pendientes de todos los copropietarios.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-users"></i> Resumen de Cuenta por Residente</h2>
            </div>
            <div class="card-body">
                <form method="GET" class="tabla-toolbar">
                    <div class="filtro-grupo" style="max-width:320px;">
                        <span class="filtro-etiqueta">Buscar</span>
                        <div class="buscador-tabla">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="buscar" value="<?= htmlspecialchars($buscarResidente) ?>" placeholder="Buscar residente o vivienda...">
                        </div>
                    </div>
                    <div class="filtro-grupo">
                        <span class="filtro-etiqueta">Estado de cuenta</span>
                        <select name="filtro_estado" class="filtro-tabla">
                            <option value="">Todos los estados</option>
                            <option value="AL_DIA" <?= $filtroEstadoCuenta === 'AL_DIA' ? 'selected' : '' ?>>Al día</option>
                            <option value="PENDIENTE" <?= $filtroEstadoCuenta === 'PENDIENTE' ? 'selected' : '' ?>>Con pendientes</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filtrar</button>
                    <a href="estado_cuenta.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-eraser"></i> Limpiar</a>
                </form>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Residente</th>
                                <th>Vivienda</th>
                                <th>Total Pagado</th>
                                <th>Total Pendiente</th>
                                <th> Pagos Pendientes</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($estados)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay residentes registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($estados as $e): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($e['nombres']) ?></strong></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($e['numero_vivienda'] ?: 'S/N') ?></span></td>
                                        <td><strong style="color: var(--success, #28a745);">$<?= number_format($e['total_pagado'], 2) ?></strong></td>
                                        <td><strong style="color: var(--danger, #dc3545);">$<?= number_format($e['total_pendiente'], 2) ?></strong></td>
                                        <td><?= $e['pagos_pendientes'] ?></td>
                                        <td>
                                            <?php if ($e['total_pendiente'] <= 0): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-check-circle"></i> AL DÍA</span>
                                            <?php elseif ($e['pagos_pendientes'] > 0): ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-exclamation-circle"></i> PENDIENTE</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">SIN MOVIMIENTOS</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php
                $total = $totalEstados;
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
