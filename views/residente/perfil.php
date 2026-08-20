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
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-id-card"></i> Perfil y Datos del Residente</h1>
            <p class="subtitle">Actualizacion de datos personales y aceptacion del reglamento de uso.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-user-pen"></i> Datos de la Cuenta</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/UsuarioController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="actualizar_perfil">

                    <div class="form-group">
                        <label for="nombres">Nombres y Apellidos</label>
                        <input type="text" id="nombres" name="nombres" class="form-control" value="<?= htmlspecialchars($_SESSION['nombres'] ?? $_SESSION['usuario_nombres'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo Electronico</label>
                        <input type="email" id="correo" name="correo" class="form-control" value="<?= htmlspecialchars($_SESSION['correo'] ?? $_SESSION['usuario_correo'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono_whatsapp">Telefono / WhatsApp</label>
                        <input type="text" id="telefono_whatsapp" name="telefono_whatsapp" class="form-control" placeholder="09XXXXXXXX" value="<?= htmlspecialchars($_SESSION['telefono'] ?? $_SESSION['usuario_telefono'] ?? '') ?>">
                    </div>

                    <div class="form-group span-full">
                        <label class="checkbox-container">
                            <input type="checkbox" name="aceptacion_terminos" value="1" required checked>
                            <span>Declaro haber leido y aceptado las normas de convivencia y reglamentos de uso de areas comunes del conjunto Vallermosso II.</span>
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

<script src="../../public/js/sidebar.js"></script>
</body>
</html>
