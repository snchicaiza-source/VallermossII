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
    <link rel="stylesheet" href="../../public/css/tablas.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-id-card"></i> Perfil y Datos del Residente</h1>
            <p class="subtitle">Actualización de datos personales y aceptación del reglamento de uso.</p>
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
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" class="form-control" value="<?= htmlspecialchars($_SESSION['correo'] ?? $_SESSION['usuario_correo'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono_whatsapp">Teléfono / WhatsApp</label>
                        <input type="tel" id="telefono_whatsapp" name="telefono_whatsapp" class="form-control" placeholder="Ej. 0987654321" maxlength="13" inputmode="tel" data-validar="telefono" data-solo-digitos="13" data-permite-mas value="<?= htmlspecialchars($_SESSION['telefono'] ?? $_SESSION['usuario_telefono'] ?? '') ?>">
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

        <section class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-key"></i> Cambiar Mi Contraseña</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/UsuarioController.php" method="POST" class="grid-form">
                    <input type="hidden" name="action" value="cambiar_clave_propia">

                    <div class="form-group span-full">
                        <label for="clave_actual">Contraseña actual *</label>
                        <div style="display:flex; gap:0.5rem;">
                            <input type="password" id="clave_actual" name="clave_actual" class="form-control" required>
                            <button type="button" class="btn btn-sm btn-outline" onclick="alternarVer('clave_actual', this)"><i class="fa-solid fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nueva_clave">Nueva contraseña (min. 6 caracteres) *</label>
                        <div style="display:flex; gap:0.5rem;">
                            <input type="password" id="nueva_clave" name="nueva_clave" class="form-control" required minlength="6">
                            <button type="button" class="btn btn-sm btn-outline" onclick="alternarVer('nueva_clave', this)"><i class="fa-solid fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_clave">Confirmar nueva contraseña *</label>
                        <div style="display:flex; gap:0.5rem;">
                            <input type="password" id="confirmar_clave" name="confirmar_clave" class="form-control" required minlength="6">
                            <button type="button" class="btn btn-sm btn-outline" onclick="alternarVer('confirmar_clave', this)"><i class="fa-solid fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-key"></i> Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<script>
function alternarVer(idInput, btn) {
    var input = document.getElementById(idInput);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    var icono = btn.querySelector('i');
    if (icono) icono.className = input.type === 'text' ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
}
</script>

<script src="../../public/js/sidebar.js"></script>
<script src="../../public/js/tablas.js?v=3"></script>
</body>
</html>
