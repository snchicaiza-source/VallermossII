<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

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
            <p class="subtitle">Gestión y carga de documentos oficiales del conjunto residencial.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-file-circle-plus"></i> Agregar Documento Legal</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" enctype="multipart/form-data" class="grid-form">
                    <input type="hidden" name="action" value="crear_documento_legal">

                    <div class="form-group">
                        <label for="titulo">Título del Documento</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ej. Acta Asamblea General 2026" required>
                    </div>

                    <div class="form-group">
                        <label for="categoria">Categoria</label>
                        <select id="categoria" name="categoria" class="form-control" required>
                            <option value="LEYES">LEYES</option>
                            <option value="ACTAS_ASAMBLEA">ACTAS ASAMBLEA</option>
                            <option value="ACTAS_DIRECTIVA">ACTAS DIRECTIVA</option>
                            <option value="DECLARATORIA_PH">DECLARATORIA PH</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="archivo"><i class="fa-solid fa-paperclip"></i> Seleccionar Archivo (PDF, imagen, etc.)</label>
                        <input type="file" id="archivo" name="archivo" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif" required>
                        <small style="color: var(--text-muted);">Formatos aceptados: PDF, Word, Excel, imagenes. Máximo 10MB.</small>
                    </div>

                    <div class="form-group">
                        <label for="fecha_publicacion">Fecha de Publicacion</label>
                        <input type="date" id="fecha_publicacion" name="fecha_publicacion" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Subir y Guardar Documento</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-folder-open"></i> Documentos Registrados</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Categoria</th>
                                <th>Archivo</th>
                                <th>Fecha Publicacion</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($documentos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay documentos legales registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($documentos as $doc): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($doc['titulo']) ?></strong></td>
                                        <td>
                                            <?php
                                                $catBadge = 'badge-info';
                                                if ($doc['categoria'] === 'LEYES') $catBadge = 'badge-danger';
                                                elseif ($doc['categoria'] === 'ACTAS_ASAMBLEA') $catBadge = 'badge-success';
                                                elseif ($doc['categoria'] === 'ACTAS_DIRECTIVA') $catBadge = 'badge-warning';
                                                elseif ($doc['categoria'] === 'DECLARATORIA_PH') $catBadge = 'badge-info';
                                            ?>
                                            <span class="badge <?= $catBadge ?>"><?= htmlspecialchars($doc['categoria']) ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($doc['archivo_url'])): ?>
                                                <a href="../../<?= htmlspecialchars($doc['archivo_url']) ?>" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fa-solid fa-eye"></i> Ver
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted);">Sin archivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($doc['fecha_publicacion'])) ?></td>
                                        <td>
                                            <?php if (!empty($doc['archivo_url'])): ?>
                                                <a href="../../<?= htmlspecialchars($doc['archivo_url']) ?>" download class="btn btn-sm btn-outline" title="Descargar archivo">
                                                    <i class="fa-solid fa-download"></i> Descargar
                                                </a>
                                            <?php endif; ?>
                                            <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar este documento?');">
                                                <input type="hidden" name="action" value="eliminar_documento_legal">
                                                <input type="hidden" name="id_documento" value="<?= $doc['id_documento'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
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
<script src="../../public/js/sidebar.js"></script>
</body>
</html>
