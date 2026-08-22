<?php
session_start();
// Evita que el navegador guarde en cache una version vieja de la pagina
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Si ya tiene sesion iniciada, lo manda a su panel
if (isset($_SESSION['usuario_id'])) {
    require_once __DIR__ . '/../../config/auth_middleware.php';
    redirigirSegunRol($_SESSION['usuario_rol']);
}

require_once __DIR__ . '/../../models/Usuario.php';

// Si el flujo de recuperacion expiro o no existe, se limpia
if (isset($_SESSION['recuperar']) && (!isset($_SESSION['recuperar']['expira']) || $_SESSION['recuperar']['expira'] < time())) {
    unset($_SESSION['recuperar']);
}

$pasoActual = 1;
if (isset($_SESSION['recuperar']['paso'])) {
    $pasoActual = (int)$_SESSION['recuperar']['paso'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Conjunto Residencial Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="auth-body">

    <div class="auth-wrapper">
        <div class="auth-shell">

            <?php require_once __DIR__ . '/../partials/auth_escena.php'; ?>

            <!-- ============ PANEL FORMULARIO: RECUPERAR CONTRASENA ============ -->
            <section class="auth-panel">
                <div class="auth-card">
                    <div class="auth-brand" style="margin-bottom:20px;">
                        <div class="icon">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <h1>Recuperar Contraseña</h1>
                        <p>Conjunto Residencial Vallermosso II</p>
                    </div>

                    <!-- Indicador de pasos -->
                    <div class="steps" id="stepsBar">
                        <div class="step <?= $pasoActual >= 1 ? 'active' : '' ?>" data-step="1">
                            <span class="dot"><i class="fa-solid fa-envelope"></i></span>
                            <span class="lbl">Correo</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step <?= $pasoActual >= 2 ? 'active' : '' ?>" data-step="2">
                            <span class="dot"><i class="fa-solid fa-house-circle-check"></i></span>
                            <span class="lbl">Vivienda</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step <?= $pasoActual >= 3 ? 'active' : '' ?>" data-step="3">
                            <span class="dot"><i class="fa-solid fa-lock"></i></span>
                            <span class="lbl">Clave</span>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['error_login'])): ?>
                        <div class="alert alert-danger">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <?= $_SESSION['error_login'];
                            unset($_SESSION['error_login']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- PASO 1: Correo registrado -->
                    <div class="step-pane <?= $pasoActual === 1 ? 'visible' : '' ?>" id="pane1">
                        <div class="hint">
                            <i class="fa-solid fa-circle-info"></i>
                            Ingrese el correo electrónico con el que esta registrado en el sistema.
                        </div>
                        <form id="formPaso1">
                            <input type="hidden" name="action" value="recuperar_paso1">
                            <div class="field-group">
                                <label for="correoP1">Correo Electrónico</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-envelope leading"></i>
                                    <input type="email" id="correoP1" name="correo" class="form-control" placeholder="correo@ejemplo.com" required autocomplete="email">
                                </div>
                            </div>
                            <button type="submit" class="btn-auth">
                                Continuar <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </form>
                    </div>

                    <!-- PASO 2: Verificacion de identidad -->
                    <div class="step-pane <?= $pasoActual === 2 ? 'visible' : '' ?>" id="pane2">
                        <div class="hint">
                            <i class="fa-solid fa-shield-halved"></i>
                            Para verificar su identidad, ingrese el número de su vivienda o casa registrada
                            <strong>(ejemplo: Casa 15, 355, Oficina Admin)</strong>.
                        </div>
                        <form id="formPaso2">
                            <input type="hidden" name="action" value="recuperar_paso2">
                            <div class="field-group">
                                <label for="viviendaP2">Número de Vivienda</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-house-flag leading"></i>
                                    <input type="text" id="viviendaP2" name="numero_vivienda" class="form-control" placeholder="Ej. Casa 15" required autocomplete="off">
                                </div>
                            </div>
                            <button type="submit" class="btn-auth">
                                Verificar <i class="fa-solid fa-check"></i>
                            </button>
                        </form>
                    </div>

                    <!-- PASO 3: Nueva contraseña -->
                    <div class="step-pane <?= $pasoActual === 3 ? 'visible' : '' ?>" id="pane3">
                        <div class="hint">
                            <i class="fa-solid fa-lock"></i>
                            Cree una nueva contraseña de al menos 6 caracteres.
                        </div>
                        <form id="formPaso3">
                            <input type="hidden" name="action" value="recuperar_paso3">
                            <div class="field-group">
                                <label for="nuevaClave">Nueva Contraseña</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-lock leading"></i>
                                    <input type="password" id="nuevaClave" name="clave" class="form-control" placeholder="Mínimo 6 caracteres" minlength="6" required autocomplete="new-password">
                                    <button type="button" class="toggle-pass" data-objetivo="nuevaClave" aria-label="Mostrar u ocultar contraseña">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="field-group">
                                <label for="confirmarClave">Confirmar Contraseña</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-lock leading"></i>
                                    <input type="password" id="confirmarClave" name="confirmacion" class="form-control" placeholder="Repita la contraseña" minlength="6" required autocomplete="new-password">
                                    <button type="button" class="toggle-pass" data-objetivo="confirmarClave" aria-label="Mostrar u ocultar contraseña">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn-auth">
                                Guardar Contraseña <i class="fa-solid fa-floppy-disk"></i>
                            </button>
                        </form>
                    </div>

                    <!-- FINALIZADO -->
                    <div class="step-pane" id="paneOk">
                        <div class="success-box">
                            <span class="ok-icon"><i class="fa-solid fa-check"></i></span>
                            <h2>Contraseña actualizada</h2>
                            <p>Su contraseña se restableció correctamente.<br>Ya puede iniciar sesión con su nueva contraseña.</p>
                            <a href="login.php" class="btn-auth" style="display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;">
                                <i class="fa-solid fa-right-to-bracket"></i> Ir a Iniciar Sesión
                            </a>
                        </div>
                    </div>

                    <div style="text-align:center;">
                        <a href="login.php" class="back-login">
                            <i class="fa-solid fa-arrow-left"></i> Volver al inicio de sesión
                        </a>
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
        console.log('recuperar.js v1 - asistente en 3 pasos');
        (function() {
            var toast = document.getElementById('toastError');
            var toastMsg = document.getElementById('toastMensaje');
            var toastTimer = null;

            function mostrarToast(mensaje) {
                toastMsg.textContent = mensaje;
                toast.classList.add('visible');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(function() {
                    toast.classList.remove('visible');
                }, 4200);
            }

            function marcarPasos(hasta) {
                document.querySelectorAll('#stepsBar .step').forEach(function(s) {
                    var n = parseInt(s.dataset.step, 10);
                    s.classList.remove('active', 'done');
                    if (n < hasta) s.classList.add('done');
                    if (n === hasta) s.classList.add('active');
                });
                document.querySelectorAll('#stepsBar .step-line').forEach(function(l, i) {
                    l.classList.toggle('done', i + 1 < hasta);
                });
            }

            function mostrarPane(id) {
                document.querySelectorAll('.step-pane').forEach(function(p) {
                    p.classList.remove('visible');
                });
                document.getElementById(id).classList.add('visible');
            }

            function enviar(formulario, alListo) {
                var btn = formulario.querySelector('button[type="submit"]');
                var textoOriginal = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';

                fetch('../../controllers/AuthController.php', {
                        method: 'POST',
                        body: new FormData(formulario),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        btn.disabled = false;
                        btn.innerHTML = textoOriginal;
                        if (data.success) {
                            alListo(data);
                        } else {
                            mostrarToast(data.message || 'Ocurrio un error. Intente nuevamente.');
                            // Si la sesion de recuperacion expiro, vuelve al paso 1
                            if (data.reiniciar) {
                                mostrarPane('pane1');
                                marcarPasos(1);
                            }
                        }
                    })
                    .catch(function(err) {
                        console.error('[recuperar] Error:', err);
                        btn.disabled = false;
                        btn.innerHTML = textoOriginal;
                        mostrarToast('No se pudo conectar con el servidor. Intente nuevamente.');
                    });
            }

            // Paso 1: correo registrado
            document.getElementById('formPaso1').addEventListener('submit', function(e) {
                e.preventDefault();
                var form = this;
                enviar(form, function(data) {
                    mostrarPane('pane2');
                    marcarPasos(2);
                    document.getElementById('viviendaP2').focus();
                    form.reset();
                });
            });

            // Paso 2: verificacion por numero de vivienda
            document.getElementById('formPaso2').addEventListener('submit', function(e) {
                e.preventDefault();
                var form = this;
                enviar(form, function(data) {
                    mostrarPane('pane3');
                    marcarPasos(3);
                    document.getElementById('nuevaClave').focus();
                    form.reset();
                });
            });

            // Paso 3: guardar nueva contraseña
            document.getElementById('formPaso3').addEventListener('submit', function(e) {
                e.preventDefault();
                var clave = document.getElementById('nuevaClave').value;
                var confirmacion = document.getElementById('confirmarClave').value;

                if (clave !== confirmacion) {
                    mostrarToast('Las contraseñas no coinciden. Verifique e intente de nuevo.');
                    return;
                }

                var form = this;
                enviar(form, function(data) {
                    marcarPasos(4);
                    document.querySelectorAll('#stepsBar .step').forEach(function(s) {
                        s.classList.add('done');
                    });
                    document.querySelectorAll('#stepsBar .step-line').forEach(function(l) {
                        l.classList.add('done');
                    });
                    mostrarPane('paneOk');
                    form.reset();
                });
            });
        })();
    </script>

</body>

</html>
