<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Comunicado.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../services/WhatsAppService.php';

verificarRol(['ADMINISTRADOR', 'DIRECTIVA']);

$comunicadoModel = new Comunicado();
$historial = $comunicadoModel->obtenerTodos();

$db = Database::connect();
$stmtUsuarios = $db->query("SELECT nombres, numero_vivienda, telefono_whatsapp, correo FROM usuarios WHERE estado = 'ACTIVO' AND rol = 'RESIDENTE'");
$residentes = $stmtUsuarios->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunicados - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>

<div class="app-layout">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="sidebar-title">Vallermosso II</h2>
        <p style="font-size: 0.85rem; color: var(--primary); margin-bottom: 20px;">
            <?= htmlspecialchars($_SESSION['usuario_nombres']) ?> (<?= $_SESSION['usuario_rol'] ?>)
        </p>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="comunicados.php" class="nav-link active">📢 Comunicados</a>
            </li>
            <li class="nav-item">
                <a href="verificar_pagos.php" class="nav-link">🔍 Verificar Pagos</a>
            </li>
            <li class="nav-item" style="margin-top: 30px;">
                <form action="../../controllers/AuthController.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-danger" style="width: 100%;">Cerrar Sesión</button>
                </form>
            </li>
            <li class="nav-item">
                <a href="usuarios.php" class="nav-link">👥 Control de Accesos</a>
            </li>
        </ul>
    </div>

    <!-- Contenido Principal -->
    <div class="main-content">
        <h1 style="color: var(--primary-dark); margin-bottom: 20px;">📢 Gestión de Comunicados Multicanal</h1>

        <!-- Alertas Flash -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de Redacción POST -->
        <div class="card">
            <h2 class="card-title">Redactar Nuevo Comunicado</h2>
            <form action="../../controllers/ComunicadoController.php" method="POST">
                
                <div class="form-group">
                    <label for="titulo">Título del Comunicado:</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ej: Mantenimiento del área comunal" required>
                </div>

                <div class="form-group">
                    <label for="mensaje">Mensaje Informativo:</label>
                    <textarea id="mensaje" name="mensaje" class="form-control" rows="4" placeholder="Escriba aquí los detalles para los residentes..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="canal">Canal de Notificación:</label>
                    <select id="canal" name="canal" class="form-select" required>
                        <option value="AMBOS">Correo Electrónico + WhatsApp (Recomendado)</option>
                        <option value="WHATSAPP">Solo WhatsApp Directo</option>
                        <option value="EMAIL">Solo Correo Electrónico</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                    🚀 Registrar y Enviar Comunicado
                </button>
            </form>
        </div>

        <!-- Módulo para envío inmediato de WhatsApp a los residentes -->
        <?php if (isset($_SESSION['ultimo_comunicado'])): 
            $ultimo = $_SESSION['ultimo_comunicado'];
            unset($_SESSION['ultimo_comunicado']);
        ?>
            <div class="card" style="border: 2px solid var(--accent);">
                <h2 class="card-title" style="color: var(--accent);">📲 Enviar por WhatsApp a Residentes</h2>
                <p style="font-size: 0.9rem; margin-bottom: 15px;">Haga clic en el botón de cada residente para abrir el chat de WhatsApp con el mensaje cargado automáticamente:</p>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vivienda</th>
                                <th>Residente</th>
                                <th>Teléfono</th>
                                <th>Acción Directa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($residentes as $residente): 
                                $urlWa = WhatsAppService::generarEnlaceDirecto($residente['telefono_whatsapp'], $ultimo['titulo'], $ultimo['mensaje']);
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($residente['numero_vivienda']) ?></td>
                                    <td><?= htmlspecialchars($residente['nombres']) ?></td>
                                    <td><?= htmlspecialchars($residente['telefono_whatsapp']) ?></td>
                                    <td>
                                        <a href="<?= $urlWa ?>" target="_blank" class="btn btn-success" style="font-size: 0.8rem; padding: 6px 12px;">
                                            💬 Enviar por WhatsApp
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Historial de Comunicados -->
        <div class="card">
            <h2 class="card-title">📋 Historial de Comunicados Emitidos</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Título</th>
                            <th>Canal</th>
                            <th>Emitido por</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historial)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted);">No se han registrado comunicados aún.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historial as $com): ?>
                                <tr>
                                    <td><?= htmlspecialchars($com['fecha_envio']) ?></td>
                                    <td><strong><?= htmlspecialchars($com['titulo']) ?></strong></td>
                                    <td>
                                        <span style="background-color: var(--secondary); padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">
                                            <?= htmlspecialchars($com['canal']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($com['remitente']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>