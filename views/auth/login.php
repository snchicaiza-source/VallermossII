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
    <title>Iniciar Sesion - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, #F7F5F0 0%, #E8E2D8 50%, #D8CFC4 100%);
        }
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        .login-card {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: 0 8px 32px rgba(54, 50, 46, 0.12);
            border: 1px solid var(--secondary);
        }
        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-brand .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 16px;
            margin-bottom: 16px;
        }
        .login-brand .icon i {
            font-size: 1.8rem;
            color: #FFFFFF;
        }
        .login-brand h1 {
            color: var(--primary-dark);
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 4px;
        }
        .login-brand p {
            color: var(--text-muted);
            font-size: 0.88rem;
        }
        .login-form .form-group {
            margin-bottom: 20px;
        }
        .login-form label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-main);
            margin-bottom: 6px;
        }
        .login-form .form-control {
            padding: 12px 14px;
            border: 1.5px solid var(--secondary);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
        }
        .login-form .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(163, 143, 120, 0.15);
        }
        .login-form .btn-primary {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            margin-top: 8px;
        }
        .login-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid var(--secondary);
            text-align: center;
        }
        .login-footer p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-brand">
            <div class="icon">
                <i class="fa-solid fa-building"></i>
            </div>
            <h1>VALLERMOSSO II</h1>
            <p>Sistema de Gestion Residencial</p>
        </div>

        <?php if (isset($_SESSION['error_login'])): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= $_SESSION['error_login']; unset($_SESSION['error_login']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['logout'])): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <i class="fa-solid fa-circle-check"></i> Sesion cerrada correctamente.
            </div>
        <?php endif; ?>

        <form action="../../controllers/AuthController.php" method="POST" class="login-form">
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label for="correo"><i class="fa-solid fa-envelope"></i> Correo Electronico</label>
                <input type="email" id="correo" name="correo" class="form-control" placeholder="correo@ejemplo.com" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="clave"><i class="fa-solid fa-lock"></i> Contrasena</label>
                <input type="password" id="clave" name="clave" class="form-control" placeholder="Ingrese su contrasena" required>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesion
            </button>
        </form>

        <div class="login-footer">
            <p><strong>Cuentas de prueba:</strong></p>
            <p>admin@vallermosso.com / directiva@vallermosso.com / residente@vallermosso.com</p>
            <p>Clave: 123456</p>
        </div>
    </div>
</div>

</body>
</html>
