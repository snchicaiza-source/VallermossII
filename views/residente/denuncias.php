<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['RESIDENTE']);

$id_usuario = $_SESSION['id_usuario'];
$pdo = Database::obtenerConexion();

$stmt = $pdo->prepare("SELECT * FROM incidencias WHERE id_usuario = :id AND tipo = 'QUEJA' ORDER BY fecha DESC");
$stmt->execute([':id' => $id_usuario]);
$denuncias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denuncias de Convivencia - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-hand-fist"></i> Denuncias de Convivencia</h1>
            <p class="subtitle">Reporta problemas de convivencia en el condominio.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-pen"></i> Nueva Denuncia</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/IncidenciaController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_reporte">
                    <input type="hidden" name="return_to" value="denuncias">

                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select id="tipo" name="tipo" class="form-control" required>
                            <option value="">Seleccione una opcion</option>
                            <option value="RUIDO">Ruido</option>
                            <option value="MASCOTAS">Mascotas</option>
                            <option value="VECINOS">Problemas con Vecinos</option>
                            <option value="OTROS">Otros</option>
                        </select>
                    </div>

                    <div class="form-group span-full">
                        <label for="descripcion">Descripcion</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="5" placeholder="Describe el problema de convivencia con el mayor detalle posible..." required></textarea>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Enviar Denuncia</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list-check"></i> Mis Denuncias</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Descripcion</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($denuncias)): ?>
                                <tr><td colspan="5" class="text-center">No has registrado denuncias.</td></tr>
                            <?php else: ?>
                                <?php foreach ($denuncias as $d): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($d['tipo']) ?></span></td>
                                        <td><?= htmlspecialchars($d['descripcion']) ?></td>
                                        <td>
                                            <?php $est = $d['estado']; ?>
                                            <?php if ($est === 'RESUELTO'): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> RESUELTO</span>
                                            <?php elseif ($est === 'EN_REVISION'): ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> EN REVISION</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-hourglass"></i> PENDIENTE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="../../controllers/IncidenciaController.php" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar esta denuncia?');">
                                                <input type="hidden" name="action" value="eliminar_incidencia">
                                                <input type="hidden" name="id_incidencia" value="<?= $d['id_incidencia'] ?>">
                                                <input type="hidden" name="return_to" value="denuncias">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
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
