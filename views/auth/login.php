<?php
session_start();
// Evita que el navegador guarde en cache una version vieja del login
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

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
    <title>Iniciar Sesión - Conjunto Residencial Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="auth-body">

    <div class="auth-wrapper">
        <div class="auth-shell">

            <?php require_once __DIR__ . '/../partials/auth_escena.php'; ?>

            <!-- ============ PANEL FORMULARIO ============ -->
            <section class="auth-panel">
                <div class="auth-card">
                    <div class="auth-brand">
                        <div class="icon">
                            <i class="fa-solid fa-house-chimney"></i>
                        </div>
                        <h1>Vallermosso II</h1>
                        <p>Conjunto Residencial &middot; Sistema de Gestión</p>
                    </div>

                    <?php if (isset($_SESSION['error_login'])): ?>
                        <div class="alert alert-danger">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <?= $_SESSION['error_login'];
                            unset($_SESSION['error_login']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['clave_actualizada']) && $_SESSION['clave_actualizada']): ?>
                        <div class="alert alert-success">
                            <i class="fa-solid fa-circle-check"></i> Contraseña actualizada correctamente. Inicie sesión con su nueva contraseña.
                        </div>
                        <?php unset($_SESSION['clave_actualizada']); ?>
                    <?php endif; ?>

                    <?php if (isset($_GET['logout'])): ?>
                        <div class="alert alert-success">
                            <i class="fa-solid fa-circle-check"></i> Sesión cerrada correctamente.
                        </div>
                    <?php endif; ?>

                    <form action="../../controllers/AuthController.php" method="POST" id="loginForm">
                        <input type="hidden" name="action" value="login">

                        <div class="field-group">
                            <label for="correo">Correo Electrónico</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-envelope leading"></i>
                                <input type="email" id="correo" name="correo" class="form-control" placeholder="correo@ejemplo.com" required autocomplete="email">
                            </div>
                        </div>

                        <div class="field-group">
                            <label for="clave">Contraseña</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-lock leading"></i>
                                <input type="password" id="clave" name="clave" class="form-control" placeholder="Ingrese su contraseña" required autocomplete="current-password">
                                <button type="button" class="toggle-pass" data-objetivo="clave" aria-label="Mostrar u ocultar contraseña">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-auth">
                            Iniciar Sesión <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        </button>

                        <div class="recover-row">
                            <a href="recuperar.php" class="link-recover">
                                <i class="fa-solid fa-key"></i> Recuperar Contrase&ntilde;a
                            </a>
                        </div>
                    </form>

                    <div class="auth-footer">
                        <p><i class="fa-solid fa-house"></i>Conjunto Residencial Vallermosso II</p>
                        <p>&copy; 2026 Todos los derechos reservados.</p>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <div class="toast-error" id="toastError">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span id="toastMensaje"></span>
    </div>

    <script src="../../public/js/auth.js"></script>
    <script>
        (function() {
            var toast = document.getElementById('toastError');
            var toastMsg = document.getElementById('toastMensaje');
            var toastTimer = null;
            var fallbackUsado = false;

            function mostrarToast(mensaje) {
                toastMsg.textContent = mensaje;
                toast.classList.add('visible');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(function() {
                    toast.classList.remove('visible');
                }, 4000);
            }

            document.getElementById('loginForm').addEventListener('submit', function(e) {
                if (fallbackUsado) {
                    return;
                }
                e.preventDefault();

                var form = this;
                var btn = form.querySelector('button[type="submit"]');
                var textoOriginal = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verificando...';

                // Respaldo: envio clasico del formulario (inmune al desafio de seguridad del hosting).
                // form.submit() no dispara el evento submit, por lo que no hay bucle.
                function enviarNativo() {
                    fallbackUsado = true;
                    form.submit();
                }

                // Aborta la peticion si tarda mas de 15 segundos
                var controlador = ('AbortController' in window) ? new AbortController() : null;
                var temporizador = controlador ? setTimeout(function() {
                    controlador.abort();
                }, 15000) : null;

                fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        signal: controlador ? controlador.signal : undefined
                    })
                    .then(function(res) {
                        return res.text().then(function(text) {
                            try {
                                return JSON.parse(text);
                            } catch (err) {
                                console.warn('[login] Respuesta no JSON (primeros 200 chars):', text.substring(0, 200));
                                return {
                                    __invalido: true
                                };
                            }
                        });
                    })
                    .then(function(data) {
                        if (temporizador) {
                            clearTimeout(temporizador);
                        }
                        if (data && data.__invalido) {
                            console.warn('[login] Respuesta invalida -> reenviando de forma nativa');
                            enviarNativo();
                            return;
                        }
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            mostrarToast(data.message || 'Correo electrónico o contraseña incorrectos.');
                            btn.disabled = false;
                            btn.innerHTML = textoOriginal;
                        }
                    })
                    .catch(function(err) {
                        if (temporizador) {
                            clearTimeout(temporizador);
                        }
                        console.error('[login] Fallo AJAX:', err);
                        // No mostrar error: reintenta con el envio clasico del formulario
                        enviarNativo();
                    });
            });
        })();
    </script>

</body>

</html>
