<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['DIRECTIVA']);

$db = Database::obtenerConexion();

$stmt = $db->query("SELECT * FROM documentos_directiva ORDER BY fecha_publicacion DESC");
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación Legal - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-scale-balanced"></i> Documentación Legal</h1>
            <p class="subtitle">Consulta de documentos oficiales, leyes, actas y declaratoria del conjunto.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <!-- Leyes y Reglamentos -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-scroll"></i> Leyes y Reglamentos</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Categoria</th>
                                <th>Fecha Publicacion</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $leyes = array_filter($documentos, function ($d) { return $d['categoria'] === 'LEYES'; });
                            if (empty($leyes)): ?>
                                <tr><td colspan="4" class="text-center">No hay documentos de leyes disponibles.</td></tr>
                            <?php else: ?>
                                <?php foreach ($leyes as $doc): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($doc['titulo']) ?></strong></td>
                                        <td><span class="badge badge-danger"><?= htmlspecialchars($doc['categoria']) ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($doc['fecha_publicacion'])) ?></td>
                                        <td>
                                            <?php if (!empty($doc['archivo_url'])): ?>
                                                <a href="<?= calcularRaizProyecto() ?>/<?= htmlspecialchars($doc['archivo_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-download"></i> Descargar
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Sin archivo</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Actas de Asamblea -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-file-signature"></i> Actas de Asamblea</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Categoria</th>
                                <th>Fecha Publicacion</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $actasAsamblea = array_filter($documentos, function ($d) { return $d['categoria'] === 'ACTAS_ASAMBLEA'; });
                            if (empty($actasAsamblea)): ?>
                                <tr><td colspan="4" class="text-center">No hay actas de asamblea disponibles.</td></tr>
                            <?php else: ?>
                                <?php foreach ($actasAsamblea as $doc): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($doc['titulo']) ?></strong></td>
                                        <td><span class="badge badge-success"><?= htmlspecialchars($doc['categoria']) ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($doc['fecha_publicacion'])) ?></td>
                                        <td>
                                            <?php if (!empty($doc['archivo_url'])): ?>
                                                <a href="<?= calcularRaizProyecto() ?>/<?= htmlspecialchars($doc['archivo_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-download"></i> Descargar
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Sin archivo</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Actas de Directiva -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-building-user"></i> Actas de Directiva</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Categoria</th>
                                <th>Fecha Publicacion</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $actasDirectiva = array_filter($documentos, function ($d) { return $d['categoria'] === 'ACTAS_DIRECTIVA'; });
                            if (empty($actasDirectiva)): ?>
                                <tr><td colspan="4" class="text-center">No hay actas de directiva disponibles.</td></tr>
                            <?php else: ?>
                                <?php foreach ($actasDirectiva as $doc): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($doc['titulo']) ?></strong></td>
                                        <td><span class="badge badge-warning"><?= htmlspecialchars($doc['categoria']) ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($doc['fecha_publicacion'])) ?></td>
                                        <td>
                                            <?php if (!empty($doc['archivo_url'])): ?>
                                                <a href="<?= calcularRaizProyecto() ?>/<?= htmlspecialchars($doc['archivo_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-download"></i> Descargar
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Sin archivo</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Declaratoria PH -->
        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-file-contract"></i> Declaratoria de Propiedad Horizontal</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Categoria</th>
                                <th>Fecha Publicacion</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $declaratoria = array_filter($documentos, function ($d) { return $d['categoria'] === 'DECLARATORIA_PH'; });
                            if (empty($declaratoria)): ?>
                                <tr><td colspan="4" class="text-center">No hay documentos de declaratoria PH disponibles.</td></tr>
                            <?php else: ?>
                                <?php foreach ($declaratoria as $doc): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($doc['titulo']) ?></strong></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($doc['categoria']) ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($doc['fecha_publicacion'])) ?></td>
                                        <td>
                                            <?php if (!empty($doc['archivo_url'])): ?>
                                                <a href="<?= calcularRaizProyecto() ?>/<?= htmlspecialchars($doc['archivo_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-download"></i> Descargar
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Sin archivo</span>
                                            <?php endif; ?>
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
