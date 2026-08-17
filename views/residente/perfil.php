<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';

verificarRol(['RESIDENTE']);

$mensaje = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Residente - Vallermosso II</title>
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
                <a href="perfil.php" class="nav-link active"><i class="fa-solid fa-user-gear"></i> <span>Mi Perfil</span></a>
            </li>
            <li class="nav-item">
                <a href="normativa.php" class="nav-link"><i class="fa-solid fa-book"></i> <span>Guía y Normativa</span></a>
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
            <h1><i class="fa-solid fa-id-card"></i> Perfil y Datos del Residentes</h1>
            <p class="subtitle">Actualización de datos personales y aceptación del reglamento de uso.</p>
        </header>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <section class="card form-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-user-pen"></i> Datos de la Cuenta</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/UsuarioController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="actualizar_perfil">

                    <div class="form-group">
                        <label for="nombres">Nombres y Apellidos</label>
                        <input type="text" id="nombres" name="nombres" class="form-control" value="<?= htmlspecialchars($_SESSION['usuario_nombres'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($_SESSION['usuario_email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono / WhatsApp</label>
                        <input type="text" id="telefono" name="telefono" class="form-control" placeholder="09XXXXXXXX">
                    </div>

                    <div class="form-group span-full">
                        <label class="checkbox-container">
                            <input type="checkbox" name="aceptacion_terminos" value="1" required checked>
                            <span>Declaro haber leído y aceptado las normas de convivencia y reglamentos de uso de áreas comunes del conjunto Vallermosso II.</span>
                        </label>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

</body>
</html>