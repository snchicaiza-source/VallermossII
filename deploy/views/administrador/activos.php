<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

// Obtener lista de activos
$stmt = $db->query("SELECT * FROM activos ORDER BY id DESC");
$activos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienes y Activos - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/tablas.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-boxes-stacked"></i> Inventario de Bienes y Activos</h1>
            <p class="subtitle">Control y seguimiento del estado del equipamiento e instalaciones comunitarias.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <!-- Formulario para Registrar Activos -->
        <section class="card form-card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fa-solid fa-plus"></i> Registrar Nuevo Activo / Bien Común</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_activo">

                    <div class="form-group span-2">
                        <label for="nombre">Nombre / Descripción del Activo</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ej. Bombas de agua del tanque de reserva" required>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado Conservación</label>
                        <select id="estado" name="estado" class="form-control" required>
                            <option value="EXCELENTE">EXCELENTE</option>
                            <option value="BUENO" selected>BUENO</option>
                            <option value="REGULAR">REGULAR</option>
                            <option value="MALO">MALO / MANTENIMIENTO</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="costo_aproximado">Costo Aproximado ($)</label>
                        <input type="number" step="0.01" id="costo_aproximado" name="costo_aproximado" class="form-control" placeholder="0.00">
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Activo</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Tabla de Bienes -->
        <section class="card table-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list-check"></i> Listado de Activos Registrados</h2>
            </div>
            <div class="card-body">
                <div class="tabla-toolbar">
                    <div class="filtro-grupo">
                        <span class="filtro-etiqueta">Buscar</span>
                        <div class="buscador-tabla">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" data-buscar="tablaActivos" placeholder="Buscar activo por nombre o estado...">
                        </div>
                    </div>
                    <div class="filtro-grupo">
                        <span class="filtro-etiqueta">Estado</span>
                        <select class="filtro-tabla" data-filtro-tabla="tablaActivos" data-filtro-col="2">
                            <option value="">Todos los estados</option>
                            <option value="EXCELENTE">Excelente</option>
                            <option value="BUENO">Bueno</option>
                            <option value="REGULAR">Regular</option>
                            <option value="MALO">Malo</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="tablaActivos" data-por-pagina="10">
                        <thead>
                            <tr>
                                <th> ID</th>
                                <th>Nombre / Item</th>
                                <th>Estado</th>
                                <th>Costo Aprox.</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay activos o bienes registrados actualmente.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($activos as $act): ?>
                                    <tr>
                                        <td><?= $act['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($act['nombre']) ?></strong></td>
                                        <td>
                                            <?php 
                                                $badgeClass = 'badge-info';
                                                if ($act['estado'] === 'EXCELENTE' || $act['estado'] === 'BUENO') $badgeClass = 'badge-success';
                                                elseif ($act['estado'] === 'REGULAR') $badgeClass = 'badge-warning';
                                                elseif ($act['estado'] === 'MALO') $badgeClass = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($act['estado']) ?></span>
                                        </td>
                                        <td>$<?= number_format($act['costo_aproximado'] ?? 0, 2) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($act['created_at'])) ?></td>
                                        <td>
                                            <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Confirma eliminar este activo?');">
                                                <input type="hidden" name="action" value="eliminar_activo">
                                                <input type="hidden" name="id" value="<?= $act['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Eliminar</button>
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
<script src="../../public/js/tablas.js?v=3"></script>
</body>
</html>