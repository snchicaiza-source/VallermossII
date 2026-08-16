<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    require_once __DIR__ . '/../../config/auth_middleware.php';
    redirigirSegunRol($_SESSION['usuario_rol']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">

    <div class="card" style="width: 100%; max-width: 420px; padding: 30px;">
        <div style="text-align: center; margin-bottom: 25px;">
            <h1 style="color: var(--primary-dark); font-size: 1.6rem; margin-bottom: 5px;">VALLERMOSSO II</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Sistema de Gestión Residencial</p>
        </div>

        <!-- Mensajes de Alerta -->
        <?php if (isset($_SESSION['error_login'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_login']; unset($_SESSION['error_login']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['logout'])): ?>
            <div class="alert alert-success">
                Sesión cerrada correctamente.
            </div>
        <?php endif; ?>

        <!-- Formulario por método POST -->
        <form action="../../controllers/AuthController.php" method="POST">
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control" placeholder="ejemplo@vallermosso.com" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="clave">Contraseña</label>
                <input type="password" id="clave" name="clave" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                Iniciar Sesión
            </button>
        </form>

        <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid var(--secondary); font-size: 0.8rem; color: var(--text-muted); text-align: center;">
            <p><strong>Cuentas de Prueba:</strong></p>
            <p>Admin: admin@vallermosso.com | Clave: 123456</p>
            <p>Residente: residente@vallermosso.com | Clave: 123456</p>
            <p>Directiva: directiva@vallermosso.com | Clave: 123456</p>
        </div>
    </div>

</body>
</html>