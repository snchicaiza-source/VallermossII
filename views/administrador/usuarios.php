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
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-shield-halved"></i> Control de Accesos y Usuarios</h1>
            <p class="subtitle">Gestion de credenciales, asignacion de unidades y registro de residentes.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <?php if ($msg === 'creado'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-check"></i> Usuario registrado exitosamente.</div>
        <?php elseif ($error === 'campos_vacios'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Todos los campos marcados son obligatorios.</div>
        <?php elseif ($error === 'db'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Error al registrar. Verifique que la cedula o correo no esten duplicados.</div>
        <?php endif; ?>

        <!-- Formulario para Registrar Residentes / Directiva -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-user-plus"></i> Registrar Nuevo Usuario</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/UsuarioController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_usuario">

                    <div class="form-group">
                        <label for="cedula">Cedula / Identificacion</label>
                        <input type="text" id="cedula" name="cedula" class="form-control" placeholder="Ej. 1700000001" required>
                    </div>

                    <div class="form-group">
                        <label for="nombres">Nombres Completos</label>
                        <input type="text" id="nombres" name="nombres" class="form-control" placeholder="Ej. Carlos Mendoza" required>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo Electronico</label>
                        <input type="email" id="correo" name="correo" class="form-control" placeholder="usuario@correo.com" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono_whatsapp">Telefono / WhatsApp</label>
                        <input type="text" id="telefono_whatsapp" name="telefono_whatsapp" class="form-control" placeholder="593999999999">
                    </div>

                    <div class="form-group">
                        <label for="numero_vivienda">N de Vivienda / Unidad</label>
                        <input type="text" id="numero_vivienda" name="numero_vivienda" class="form-control" placeholder="Ej. Casa 12 / Dpto 302">
                    </div>

                    <div class="form-group">
                        <label for="password">Contrasena</label>
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

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-check"></i> Registrar Usuario</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Listado de Usuarios -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-users"></i> Listado de Usuarios Registrados</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cedula</th>
                                <th>Nombres</th>
                                <th>Correo</th>
                                <th>Telefono</th>
                                <th>Vivienda</th>
                                <th>Rol</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay usuarios registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['cedula'] ?? 'N/A') ?></td>
                                        <td><strong><?= htmlspecialchars($u['nombres'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($u['correo'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($u['telefono_whatsapp'] ?? 'N/A') ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($u['numero_vivienda'] ?? 'Sin Asignar') ?></span></td>
                                        <td><span class="badge badge-warning"><?= htmlspecialchars($u['rol'] ?? 'RESIDENTE') ?></span></td>
                                        <td>
                                            <span class="badge <?= ($u['estado'] ?? 'ACTIVO') === 'ACTIVO' ? 'badge-success' : 'badge-danger' ?>">
                                                <?= htmlspecialchars($u['estado'] ?? 'ACTIVO') ?>
                                            </span>
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
