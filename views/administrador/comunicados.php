<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Comunicado.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

$comunicadoModel = new Comunicado();
$comunicados = $comunicadoModel->obtenerTodos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunicados - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Vallermosso II</h2>
            <span class="user-badge"><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($_SESSION['usuario_nombres']) ?></span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="comunicados.php" class="nav-link active"><i class="fa-solid fa-bullhorn"></i> <span>Comunicados</span></a>
            </li>
            <li class="nav-item">
                <a href="verificar_pagos.php" class="nav-link"><i class="fa-solid fa-receipt"></i> <span>Auditar Pagos</span></a>
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

    <!-- Contenido Principal -->
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-bullhorn"></i> Gestión de Comunicados y Avisos</h1>
            <p class="subtitle">Publica boletines, informativos y alertas para la comunidad del condominio.</p>
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

        <!-- Formulario para Nuevo Comunicado -->
        <section class="card form-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-pen-to-square"></i> Crear Nuevo Comunicado</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/ComunicadoController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_comunicado">

                    <div class="form-group span-full">
                        <label for="titulo">Título del Comunicado</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ej. Convocatoria a Asamblea General Extraordinaria" required>
                    </div>

                    <div class="form-group span-full">
                        <label for="contenido">Detalle / Mensaje</label>
                        <textarea id="contenido" name="contenido" class="form-control" rows="5" placeholder="Escriba aquí los detalles del aviso..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="prioridad">Nivel de Prioridad</label>
                        <select id="prioridad" name="prioridad" class="form-control" required>
                            <option value="INFORMATIVO">Informativo</option>
                            <option value="IMPORTANTE">Importante</option>
                            <option value="URGENTE">Urgente</option>
                        </select>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Publicar y Notificar</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Historial de Comunicados -->
        <section class="card table-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-newspaper"></i> Comunicados Emitidos</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Título</th>
                                <th>Prioridad</th>
                                <th>Publicado por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($comunicados)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay comunicados registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($comunicados as $c): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($c['fecha_publicacion'])) ?></td>
                                        <td><strong><?= htmlspecialchars($c['titulo']) ?></strong></td>
                                        <td>
                                            <?php if ($c['prioridad'] === 'URGENTE'): ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> URGENTE</span>
                                            <?php elseif ($c['prioridad'] === 'IMPORTANTE'): ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-circle-exclamation"></i> IMPORTANTE</span>
                                            <?php else: ?>
                                                <span class="badge badge-info"><i class="fa-solid fa-info-circle"></i> INFORMATIVO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($c['publicado_por'] ?? 'Administración') ?></td>
                                        <td>
                                            <form action="../../controllers/ComunicadoController.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="eliminar_comunicado">
                                                <input type="hidden" name="id_comunicado" value="<?= $c['id_comunicado'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Deseas eliminar este comunicado?');">
                                                    <i class="fa-solid fa-trash"></i> Eliminar
                                                </button>
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