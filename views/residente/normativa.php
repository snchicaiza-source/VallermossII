<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Directiva.php';

verificarRol(['RESIDENTE', 'ADMINISTRADOR', 'DIRECTIVA']);

$directivaModel = new Directiva();
$documentos = $directivaModel->obtenerDocumentos();
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
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-scale-balanced"></i> Guia, Normativa y Repositorio Documental</h1>
            <p class="subtitle">Leyes, reglamentos internos y actas de asamblea del condominio.</p>
        </header>


        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-folder-open"></i> Documentos Institucionales</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th>Título del Documento</th>
                                <th>Archivo</th>
                                <th>Fecha Publicacion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($documentos)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">No hay documentos publicados aun.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($documentos as $d): ?>
                                    <tr>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($d['categoria'] ?? $d['tipo'] ?? 'Documento') ?></span></td>
                                        <td><strong><?= htmlspecialchars($d['titulo']) ?></strong></td>
                                        <td>
                                            <?php $urlDoc = trim((string)($d['archivo_url'] ?? '')); ?>
                                            <?php if ($urlDoc !== '' && $urlDoc !== '#'): ?>
                                                <a href="<?= calcularRaizProyecto() ?>/<?= htmlspecialchars($urlDoc) ?>" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fa-solid fa-download"></i> Descargar
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted);">Sin archivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($d['fecha_publicacion'] ?? date('Y-m-d')) ?></td>
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
