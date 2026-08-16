<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Usuario.php';

verificarRol(['ADMINISTRADOR']);

$usuarioModel = new Usuario();
$usuarios = $usuarioModel->obtenerTodos();
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
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Vallermosso II</h2>
            <span class="user-badge"><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['usuario_nombres']) ?> (ADMIN)</span>
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
            <li class="nav-item logout-section">
                <form action="../../controllers/AuthController.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-danger btn-block"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</button>
                </form>
            </li>
        </ul>
    </aside>

    <!-- Contenido Principal -->
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-user-lock"></i> Gestión y Control de Accesos</h1>
            <p class="subtitle">Administra los roles, inmuebles y privilegios del personal y residentes del condominio.</p>
        </header>

        <!-- Mensajes Flash -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de Registro de Usuario -->
        <section class="card form-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-user-plus"></i> Registrar Nuevo Usuario</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/UsuarioController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_usuario">
                    
                    <div class="form-group">
                        <label for="nombres">Nombres Completos</label>
                        <input type="text" id="nombres" name="nombres" class="form-control" placeholder="Ej. Carlos Pérez" required>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" class="form-control" placeholder="ejemplo@vallermosso.com" required>
                    </div>

                    <div class="form-group">
                        <label for="rol">Rol Asignado</label>
                        <select id="rol" name="rol" class="form-control" required>
                            <option value="RESIDENTE">Residente / Copropietario</option>
                            <option value="DIRECTIVA">Miembro de la Directiva</option>
                            <option value="ADMINISTRADOR">Administrador de Sistema</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="numero_vivienda">Inmueble / Vivienda</label>
                        <input type="text" id="numero_vivienda" name="numero_vivienda" class="form-control" placeholder="Ej. Casa 12 / Dpto 3B">
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña Temporal</label>
                        <input type="text" id="password" name="password" class="form-control" value="123456" required>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Usuario</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Tabla de Usuarios -->
        <section class="card table-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-address-book"></i> Usuarios Registrados</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Correo Electrónico</th>
                                <th>Rol</th>
                                <th>Vivienda</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay usuarios registrados en la base de datos.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $u): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($u['nombres']) ?></strong></td>
                                        <td><?= htmlspecialchars($u['correo']) ?></td>
                                        <td>
                                            <span class="badge badge-role">
                                                <?= htmlspecialchars($u['rol']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($u['numero_vivienda'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($u['estado'] === 'ACTIVO'): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> ACTIVO</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-circle-minus"></i> BLOQUEADO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="../../controllers/UsuarioController.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="cambiar_estado">
                                                <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                                                <?php if ($u['estado'] === 'ACTIVO'): ?>
                                                    <input type="hidden" name="nuevo_estado" value="BLOQUEADO">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas bloquear el acceso a este usuario?');">
                                                        <i class="fa-solid fa-lock"></i> Bloquear
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="nuevo_estado" value="ACTIVO">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="fa-solid fa-lock-open"></i> Activar
                                                    </button>
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