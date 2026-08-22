<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();

// Crea la tabla 'espacios' si no existe en la base de datos (evita el error 500)
try {
    $db->query("SELECT 1 FROM espacios LIMIT 1");
} catch (PDOException $e) {
    $db->exec("CREATE TABLE IF NOT EXISTS espacios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL UNIQUE,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

$espacios = $db->query("SELECT * FROM espacios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espacios Comunes - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-building"></i> Espacios Comunes</h1>
            <p class="subtitle">Administrar los espacios disponibles para reserva por los residentes.</p>
        </header>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-plus-circle"></i> Agregar Espacio</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdministradorController.php" method="POST" style="display: flex; gap: 12px; align-items: flex-end;">
                    <input type="hidden" name="action" value="crear_espacio">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label for="nombre">Nombre del Espacio</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ej. Gimnasio" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Agregar</button>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list"></i> Espacios Registrados</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($espacios)): ?>
                                <tr><td colspan="4" class="text-center">No hay espacios registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($espacios as $e): ?>
                                <tr>
                                    <td><?= $e['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($e['nombre']) ?></strong></td>
                                    <td>
                                        <?php if ($e['activo']): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_espacio">
                                            <input type="hidden" name="id_espacio" value="<?= $e['id'] ?>">
                                            <input type="hidden" name="nuevo_estado" value="<?= $e['activo'] ? 0 : 1 ?>">
                                            <button type="submit" class="btn btn-sm <?= $e['activo'] ? 'btn-outline-danger' : 'btn-success' ?>" title="<?= $e['activo'] ? 'Desactivar' : 'Activar' ?>">
                                                <i class="fa-solid fa-<?= $e['activo'] ? 'eye-slash' : 'eye' ?>"></i>
                                            </button>
                                        </form>
                                        <form action="../../controllers/AdministradorController.php" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar este espacio?');">
                                            <input type="hidden" name="action" value="eliminar_espacio">
                                            <input type="hidden" name="id_espacio" value="<?= $e['id'] ?>">
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
