<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Incidencia.php';

verificarRol(['RESIDENTE']);

$incidenciaModel = new Incidencia();
$misIncidencias = $incidenciaModel->obtenerPorUsuario($_SESSION['id_usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Daños y Quejas - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Vallermosso II</h2>
            <span class="user-badge"><i class="fa-solid fa-house-user"></i> <?= htmlspecialchars($_SESSION['usuario_nombres']) ?></span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> <span>Mi Panel</span></a>
            </li>
            <li class="nav-item">
                <a href="reportar_danos.php" class="nav-link active"><i class="fa-solid fa-screwdriver-wrench"></i> <span>Reporte de Daños / Quejas</span></a>
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
            <h1><i class="fa-solid fa-wrench"></i> Reporte de Daños y Quejas</h1>
            <p class="subtitle">Registra eventualidades o sugerencias para la administración del condominio.</p>
        </header>

        <section class="card form-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-pen"></i> Nuevo Reporte</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/IncidenciaController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_reporte">

                    <div class="form-group">
                        <label for="tipo">Tipo de Incidencia</label>
                        <select id="tipo" name="tipo" class="form-control" required>
                            <option value="DAÑO">Daño en Áreas Comunes</option>
                            <option value="QUEJA">Queja de Convivencia</option>
                            <option value="RESERVACIÓN">Reserva o Requerimiento</option>
                        </select>
                    </div>

                    <div class="form-group span-full">
                        <label for="descripcion">Descripción detallada</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="4" placeholder="Detalla el problema o requerimiento..." required></textarea>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Enviar Reporte</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card table-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list-check"></i> Estado de mis Reportes</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($misIncidencias)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No has registrado reportes.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($misIncidencias as $inc): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($inc['fecha'])) ?></td>
                                        <td><strong><?= htmlspecialchars($inc['tipo']) ?></strong></td>
                                        <td><?= htmlspecialchars($inc['descripcion']) ?></td>
                                        <td>
                                            <span class="badge badge-info"><?= htmlspecialchars($inc['estado'] ?? 'EN REVISIÓN') ?></span>
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