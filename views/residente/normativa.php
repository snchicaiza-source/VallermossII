<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Documento.php';

verificarRol(['RESIDENTE', 'ADMINISTRADOR', 'DIRECTIVA']);

$docModel = new Documento();
$documentos = $docModel->obtenerTodos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Normativa y Documentos - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Vallermosso II</h2>
            <span class="user-badge"><i class="fa-solid fa-house-user"></i> <?= htmlspecialchars($_SESSION['usuario_nombres'] ?? 'Residente') ?></span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> <span>Mi Panel</span></a>
            </li>
            <li class="nav-item">
                <a href="normativa.php" class="nav-link active"><i class="fa-solid fa-book"></i> <span>Guía y Normativa</span></a>
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
            <h1><i class="fa-solid fa-scale-balanced"></i> Guía, Normativa y Repositorio Documental</h1>
            <p class="subtitle">Leyes, reglamentos internos y actas de asamblea del condominio.</p>
        </header>

        <section class="card table-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-folder-open"></i> Documentos Institucionales</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Título del Documento</th>
                                <th>Fecha de Publicación</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($documentos)): ?>
                                <tr>
                                    <td><i class="fa-solid fa-file-pdf text-danger"></i> Leyes</td>
                                    <td><strong>Ley de Propiedad Horizontal y Reglamento General</strong></td>
                                    <td>Vigente</td>
                                    <td><button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-download"></i> Descargar</button></td>
                                </tr>
                                <tr>
                                    <td><i class="fa-solid fa-file-contract text-primary"></i> Reglamentos</td>
                                    <td><strong>Reglamento Interno de Convivencia - Vallermosso II</strong></td>
                                    <td>Vigente</td>
                                    <td><button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-download"></i> Descargar</button></td>
                                </tr>
                                <tr>
                                    <td><i class="fa-solid fa-file-lines text-warning"></i> Actas</td>
                                    <td><strong>Acta de Asamblea General de Copropietarios</strong></td>
                                    <td>Reciente</td>
                                    <td><button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-download"></i> Descargar</button></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($documentos as $d): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($d['tipo'] ?? 'Documento') ?></strong></td>
                                        <td><?= htmlspecialchars($d['titulo']) ?></td>
                                        <td><?= htmlspecialchars($d['fecha'] ?? date('Y-m-d')) ?></td>
                                        <td>
                                            <a href="../../public/uploads/<?= htmlspecialchars($d['archivo'] ?? '#') ?>" class="btn btn-sm btn-outline-primary" download><i class="fa-solid fa-download"></i> Descargar</a>
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