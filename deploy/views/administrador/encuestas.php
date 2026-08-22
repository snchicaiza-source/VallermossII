<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

$stmt = $db->query("
    SELECT e.*, u.nombres as autor,
        (SELECT COUNT(*) FROM encuestas_votos WHERE id_encuesta = e.id) as total_votos
    FROM encuestas e LEFT JOIN usuarios u ON e.creada_por = u.id_usuario
    ORDER BY e.fecha_creacion DESC
");
$encuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($encuestas as &$enc) {
    $opciones = json_decode($enc['opciones'], true) ?? [];
    $enc['_counts'] = [];
    foreach ($opciones as $op) {
        $cntStmt = $db->prepare("SELECT COUNT(*) FROM encuestas_votos WHERE id_encuesta = :eid AND respuesta = :resp");
        $cntStmt->execute([':eid' => $enc['id'], ':resp' => $op]);
        $enc['_counts'][] = (int)$cntStmt->fetchColumn();
    }
}
unset($enc);

$usuarios = $db->query("SELECT id_usuario, nombres, correo, numero_vivienda FROM usuarios WHERE estado = 'ACTIVO' ORDER BY nombres")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuestas - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-square-poll-vertical"></i> Gestión de Encuestas</h1>
            <p class="subtitle">Crear, administrar y revisar resultados de encuestas comunitarias.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-plus-circle"></i> Crear Nueva Encuesta</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_encuesta">

                    <div class="form-group span-full">
                        <label for="titulo">Título de la Encuesta</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ej. Preferencia de horario para asamblea" required>
                    </div>

                    <div class="form-group span-full">
                        <label for="descripcion">Descripción (opcional)</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="2" placeholder="Detalle breve de la encuesta..."></textarea>
                    </div>

                    <div class="form-group span-full">
                        <label>Opciones de Respuesta (mínimo 2)</label>
                        <div id="opcionesContainer">
                            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                                <input type="text" name="opciones[]" class="form-control" placeholder="Opción 1" required>
                            </div>
                            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                                <input type="text" name="opciones[]" class="form-control" placeholder="Opción 2" required>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="agregarOpcion()"><i class="fa-solid fa-plus"></i> Agregar Opción</button>
                    </div>

                    <div class="form-group">
                        <label for="fecha_cierre">Fecha de Cierre (opcional)</label>
                        <input type="date" id="fecha_cierre" name="fecha_cierre" class="form-control" min="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-square-poll-vertical"></i> Crear Encuesta</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list"></i> Encuestas Registradas</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Opciones</th>
                                <th>Votos</th>
                                <th>Estado</th>
                                <th>Creada por</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($encuestas)): ?>
                                <tr><td colspan="7" class="text-center">No hay encuestas creadas.</td></tr>
                            <?php else: ?>
                                <?php foreach ($encuestas as $enc):
                                    $opciones = json_decode($enc['opciones'], true) ?? [];
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($enc['titulo']) ?></strong></td>
                                    <td>
                                        <?php foreach ($opciones as $op): ?>
                                            <span class="badge badge-info" style="margin: 1px;"><?= htmlspecialchars($op) ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td><strong><?= (int)$enc['total_votos'] ?></strong></td>
                                    <td>
                                        <?php if ($enc['activa']): ?>
                                            <span class="badge badge-success"><i class="fa-solid fa-circle"></i> Activa</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning"><i class="fa-solid fa-circle"></i> Cerrada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($enc['autor'] ?? 'Admin') ?></td>
                                    <td><?= date('d/m/Y', strtotime($enc['fecha_creacion'])) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" title="Ver Resultados" onclick="toggleResultado(<?= $enc['id'] ?>)">
                                            <i class="fa-solid fa-chart-bar"></i>
                                        </button>
                                        <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_encuesta">
                                            <input type="hidden" name="id_encuesta" value="<?= $enc['id'] ?>">
                                            <input type="hidden" name="nuevo_estado" value="<?= $enc['activa'] ? 0 : 1 ?>">
                                            <button type="submit" class="btn btn-sm <?= $enc['activa'] ? 'btn-outline-danger' : 'btn-success' ?>" title="<?= $enc['activa'] ? 'Cerrar encuesta' : 'Activar encuesta' ?>">
                                                <i class="fa-solid fa-<?= $enc['activa'] ? 'lock' : 'lock-open' ?>"></i>
                                            </button>
                                        </form>
                                        <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar esta encuesta y todos sus votos?');">
                                            <input type="hidden" name="action" value="eliminar_encuesta">
                                            <input type="hidden" name="id_encuesta" value="<?= $enc['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <tr id="resultado-<?= $enc['id'] ?>" style="display: none; background: #f9f6f1;">
                                    <td colspan="7" style="padding: 16px;">
                                        <strong><i class="fa-solid fa-chart-bar"></i> Resultados: <?= htmlspecialchars($enc['titulo']) ?></strong><br>
                                        <small style="color: #888;">Total de votos: <?= (int)$enc['total_votos'] ?></small>
                                        <div style="margin-top: 12px; max-width: 600px;">
                                        <?php
                                            $total = (int)$enc['total_votos'];
                                            foreach ($opciones as $i => $op):
                                                $count = $enc['_counts'][$i] ?? 0;
                                                $pct = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                                        ?>
                                            <div style="margin-bottom: 10px;">
                                                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                                    <span style="font-size: 14px;"><?= htmlspecialchars($op) ?></span>
                                                    <span style="font-size: 13px; font-weight: bold;"><?= $count ?> voto<?= $count != 1 ? 's' : '' ?> (<?= $pct ?>%)</span>
                                                </div>
                                                <div style="background: #e0d6c8; border-radius: 6px; height: 10px; overflow: hidden;">
                                                    <div style="background: #8b7355; height: 100%; width: <?= $pct ?>%; border-radius: 6px;"></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php
                                            $vStmt = $db->prepare("SELECT u.nombres, u.numero_vivienda, v.respuesta, v.fecha FROM encuestas_votos v JOIN usuarios u ON v.id_usuario = u.id_usuario WHERE v.id_encuesta = :eid ORDER BY v.fecha DESC");
                                            $vStmt->execute([':eid' => $enc['id']]);
                                            $votantes = $vStmt->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        <?php if (!empty($votantes)): ?>
                                            <div style="margin-top: 12px; border-top: 1px solid #e0d6c8; padding-top: 10px;">
                                                <strong style="font-size: 13px;"><i class="fa-solid fa-users"></i> Votantes (<?= count($votantes) ?>):</strong>
                                                <table style="width: 100%; margin-top: 6px; font-size: 13px;">
                                                    <thead>
                                                        <tr style="color: #888;">
                                                            <th style="text-align: left; padding: 3px 8px;">Residente</th>
                                                            <th style="text-align: left; padding: 3px 8px;">Vivienda</th>
                                                            <th style="text-align: left; padding: 3px 8px;">Voto por</th>
                                                            <th style="text-align: left; padding: 3px 8px;">Fecha</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($votantes as $v): ?>
                                                        <tr>
                                                            <td style="padding: 3px 8px;"><?= htmlspecialchars($v['nombres']) ?></td>
                                                            <td style="padding: 3px 8px;"><?= htmlspecialchars($v['numero_vivienda']) ?></td>
                                                            <td style="padding: 3px 8px;"><span class="badge badge-info"><?= htmlspecialchars($v['respuesta']) ?></span></td>
                                                            <td style="padding: 3px 8px;"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                        </div>
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
<script>
function agregarOpcion() {
    var container = document.getElementById('opcionesContainer');
    var count = container.querySelectorAll('input').length + 1;
    var div = document.createElement('div');
    div.style.cssText = 'display: flex; gap: 8px; margin-bottom: 8px;';
    div.innerHTML = '<input type="text" name="opciones[]" class="form-control" placeholder="Opcion ' + count + '" required><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>';
    container.appendChild(div);
}
function toggleResultado(id) {
    var el = document.getElementById('resultado-' + id);
    if (el) el.style.display = el.style.display === 'none' ? 'table-row' : 'none';
}
</script>
</body>
</html>
