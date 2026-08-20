<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['RESIDENTE']);

$id_usuario = $_SESSION['id_usuario'];
$pdo = Database::obtenerConexion();

$stmt = $pdo->prepare("SELECT * FROM reservas WHERE id_usuario = :id ORDER BY fecha_registro DESC");
$stmt->execute([':id' => $id_usuario]);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$espaciosStmt = $pdo->query("SELECT nombre FROM espacios WHERE activo = 1 ORDER BY nombre");
$espaciosDisponibles = $espaciosStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-calendar-days"></i> Reservas de Espacios</h1>
            <p class="subtitle">Reserva los espacios comunes del condominio.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-plus-circle"></i> Nueva Reserva</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/ResidenteController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="crear_reserva">

                    <div class="form-group">
                        <label for="espacio">Espacio</label>
                        <select id="espacio" name="espacio" class="form-control" required onchange="document.getElementById('otro_espacio_group').style.display = this.value === 'OTROS' ? 'block' : 'none'">
                            <option value="">Seleccione un espacio</option>
                            <?php foreach ($espaciosDisponibles as $e): ?>
                                <option value="<?= htmlspecialchars($e) ?>"><?= htmlspecialchars($e) ?></option>
                            <?php endforeach; ?>
                            <option value="OTROS">Otros</option>
                        </select>
                    </div>
                    <div class="form-group" id="otro_espacio_group" style="display: none;">
                        <label for="otro_espacio">Especifique el espacio</label>
                        <input type="text" id="otro_espacio" name="otro_espacio" class="form-control" placeholder="Escriba el nombre del espacio...">
                    </div>

                    <div class="form-group">
                        <label for="fecha_reserva">Fecha</label>
                        <input type="date" id="fecha_reserva" name="fecha_reserva" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="hora_inicio">Hora Inicio</label>
                        <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="hora_fin">Hora Fin</label>
                        <input type="time" id="hora_fin" name="hora_fin" class="form-control" required>
                    </div>

                    <div class="form-group span-full">
                        <label for="observaciones">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" class="form-control" rows="3" placeholder="Detalles adicionales de la reserva..."></textarea>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-calendar-check"></i> Reservar</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list-check"></i> Mis Reservas</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Espacio</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Observaciones</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservas)): ?>
                                <tr><td colspan="6" class="text-center">No tienes reservas registradas.</td></tr>
                            <?php else: ?>
                                <?php foreach ($reservas as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['espacio']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($r['fecha_reserva'])) ?></td>
                                        <td><?= substr($r['hora_inicio'], 0, 5) ?> - <?= substr($r['hora_fin'], 0, 5) ?></td>
                                        <td><?= htmlspecialchars($r['observaciones'] ?? '-') ?></td>
                                        <td>
                                            <?php $est = $r['estado']; ?>
                                            <?php if ($est === 'APROBADA'): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> APROBADA</span>
                                            <?php elseif ($est === 'RECHAZADA'): ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> RECHAZADA</span>
                                            <?php elseif ($est === 'CANCELADA'): ?>
                                                <span class="badge badge-secondary"><i class="fa-solid fa-ban"></i> CANCELADA</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> PENDIENTE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="../../controllers/ResidenteController.php" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar esta reserva?');">
                                                <input type="hidden" name="action" value="eliminar_reserva">
                                                <input type="hidden" name="id_reserva" value="<?= $r['id'] ?>">
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
