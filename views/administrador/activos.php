<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

// Obtener lista de activos
$stmt = $db->query("SELECT * FROM activos ORDER BY id DESC");
$activos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienes y Activos - Vallermosso II</title>
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
                <a href="activos.php" class="nav-link active"><i class="fa-solid fa-boxes-stacked"></i> <span>Bienes y Activos</span></a>
            </li>
            <li class="nav-item">
                <a href="convenios.php" class="nav-link"><i class="fa-solid fa-handshake"></i> <span>Convenios</span></a>
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
            <h1><i class="fa-solid fa-boxes-stacked"></i> Inventario de Bienes y Activos</h1>
            <p class="subtitle">Control y seguimiento del estado del equipamiento e instalaciones comunitarias.</p>
        </header>

        <?php if ($msg === 'creado'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-check"></i> Activo registrado con éxito.</div>
        <?php elseif ($msg === 'eliminado'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-trash"></i> Registro eliminado correctamente.</div>
        <?php elseif ($error === 'campos_vacios'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Por favor complete todos los campos requeridos.</div>
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
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th># ID</th>
                                <th>Nombre / Item</th>
                                <th>Estado</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay activos o bienes registrados actualmente.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($activos as $act): ?>
                                    <tr>
                                        <td>#<?= $act['id'] ?></td>
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

</body>
</html>