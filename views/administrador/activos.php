<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Activo.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

$activoModel = new Activo();
$activos = $activoModel->obtenerTodos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Activos - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Vallermosso II</h2>
            <span class="user-badge"><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['usuario_nombres']) ?></span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="comunicados.php" class="nav-link"><i class="fa-solid fa-bullhorn"></i> <span>Comunicados</span></a>
            </li>
            <li class="nav-item">
                <a href="verificar_pagos.php" class="nav-link"><i class="fa-solid fa-receipt"></i> <span>Auditar Pagos</span></a>
            </li>
            <li class="nav-item">
                <a href="activos.php" class="nav-link active"><i class="fa-solid fa-boxes-stacked"></i> <span>Bienes / Activos</span></a>
            </li>
            <li class="nav-item">
                <a href="usuarios.php" class="nav-link"><i class="fa-solid fa-users-gear"></i> <span>Control de Accesos</span></a>
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
            <h1><i class="fa-solid fa-boxes-stacked"></i> Gestión de Activos y Bienes</h1>
            <p class="subtitle">Inventario de mobiliario y equipamiento del condominio.</p>
        </header>

        <section class="card form-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-plus"></i> Registrar Nuevo Bien</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/ActivoController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_activo">

                    <div class="form-group">
                        <label for="nombre">Nombre del Bien / Equipo</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ej. Cortadora de césped" required>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado del Bien</label>
                        <select id="estado" name="estado" class="form-control" required>
                            <option value="EXCELENTE">Excelente</option>
                            <option value="BUENO">Bueno</option>
                            <option value="REGULAR">Mantenimiento Requerido</option>
                            <option value="MALO">Malo / Baza</option>
                        </select>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Activo</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card table-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-warehouse"></i> Inventario Registrado</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activos)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">No hay bienes inventariados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($activos as $a): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($a['nombre']) ?></strong></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($a['estado']) ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Eliminar</button>
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