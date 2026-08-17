<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Usuario.php';

verificarRol(['ADMINISTRADOR']);

$usuarioModel = new Usuario();
$usuarios = $usuarioModel->obtenerTodos();

$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Accesos - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <!-- Sidebar con todos los módulos de navegación -->
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
                <a href="usuarios.php" class="nav-link active"><i class="fa-solid fa-users-gear"></i> <span>Control de Accesos</span></a>
            </li>
            <li class="nav-item">
                <a href="activos.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> <span>Bienes y Activos</span></a>
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
            <h1><i class="fa-solid fa-shield-halved"></i> Control de Accesos y Usuarios</h1>
            <p class="subtitle">Gestión de credenciales, asignación de puestos/casas y registro de residentes.</p>
        </header>

        <?php if ($msg === 'creado'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-check"></i> Usuario registrado exitosamente.</div>
        <?php elseif ($error === 'campos_vacios'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Todos los campos marcados son obligatorios.</div>
        <?php endif; ?>

        <!-- Formulario para Registrar Residentes / Directiva -->
        <section class="card form-card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fa-solid fa-user-plus"></i> Registrar Nuevo Usuario / Residente</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/UsuarioController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_usuario">

                    <div class="form-group">
                        <label for="nombres">Nombres Completos</label>
                        <input type="text" id="nombres" name="nombres" class="form-control" placeholder="Ej. Carlos Mendoza" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico / Usuario</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="usuario@correo.com" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="********" required>
                    </div>

                    <div class="form-group">
                        <label for="rol">Rol en el Sistema</label>
                        <select id="rol" name="rol" class="form-control" required>
                            <option value="RESIDENTE">RESIDENTE (Copropietario)</option>
                            <option value="DIRECTIVA">DIRECTIVA</option>
                            <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="puesto_casa">Puesto / N° Casa o Dpto</label>
                        <input type="text" id="puesto_casa" name="puesto_casa" class="form-control" placeholder="Ej. Casa 12 / Dpto 302">
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-check"></i> Registrar Usuario</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Listado de Usuarios -->
        <section class="card table-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-users"></i> Listado de Usuarios Registrados</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Usuario / Nombre</th>
                                <th>Correo</th>
                                <th>Puesto / Casa</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay usuarios registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $u): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($u['nombres'] ?? $u['usuario'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($u['email'] ?? 'N/A') ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($u['puesto_casa'] ?? 'Sin Asignar') ?></span></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($u['rol'] ?? 'RESIDENTE') ?></span></td>
                                        <td>
                                            <span class="badge <?= ($u['estado'] ?? 'ACTIVO') === 'ACTIVO' ? 'badge-success' : 'badge-danger' ?>">
                                                <?= htmlspecialchars($u['estado'] ?? 'ACTIVO') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-key"></i> Restablecer</button>
                                            <button class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-ban"></i> Bloquear</button>
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