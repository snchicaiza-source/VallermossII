<?php
// controllers/AuthController.php
session_start();

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../config/auth_middleware.php';

$root = calcularRaizProyecto();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $esAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

    if ($action === 'login') {
        $correo = trim($_POST['correo'] ?? '');
        $clave  = trim($_POST['clave'] ?? '');

        if (empty($correo) || empty($clave)) {
            $mensaje = "Por favor, complete todos los campos.";
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $mensaje]);
                exit();
            }
            $_SESSION['error_login'] = $mensaje;
            header("Location: {$root}/views/auth/login.php");
            exit();
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerPorCorreo($correo);

        $hashGuardado = $usuario['clave_hash'] ?? $usuario['password'] ?? $usuario['clave'] ?? '';

        $passwordValida = false;
        if ($usuario && !empty($hashGuardado)) {
            if (password_verify($clave, $hashGuardado)) {
                $passwordValida = true;
            }
        }

        if ($passwordValida) {
            // Usuarios bloqueados o inactivos no pueden iniciar sesion
            $estadoCuenta = strtoupper($usuario['estado'] ?? 'ACTIVO');
            if ($estadoCuenta !== 'ACTIVO') {
                $mensaje = "Tu cuenta esta bloqueada. Contacta al administrador.";
                if ($esAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $mensaje]);
                    exit();
                }
                $_SESSION['error_login'] = $mensaje;
                header("Location: {$root}/views/auth/login.php");
                exit();
            }

            $_SESSION['id_usuario']       = $usuario['id_usuario'];
            $_SESSION['usuario_id']       = $usuario['id_usuario'];
            $_SESSION['nombres']          = $usuario['nombres'];
            $_SESSION['usuario_nombres']  = $usuario['nombres'];
            $_SESSION['correo']           = $usuario['correo'];
            $_SESSION['usuario_correo']   = $usuario['correo'];
            $_SESSION['rol']              = $usuario['rol'];
            $_SESSION['usuario_rol']      = $usuario['rol'];
            $_SESSION['numero_vivienda']  = $usuario['numero_vivienda'] ?? '';
            $_SESSION['usuario_vivienda'] = $usuario['numero_vivienda'] ?? '';

            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'redirect' => $root . obtenerRutaSegunRol($usuario['rol'])
                ]);
                exit();
            }

            redirigirSegunRol($usuario['rol']);
        } else {
            $mensaje = "Correo electrónico o contraseña incorrectos.";
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $mensaje]);
                exit();
            }
            $_SESSION['error_login'] = $mensaje;
            header("Location: {$root}/views/auth/login.php");
            exit();
        }
    }

    if ($action === 'logout') {
        session_unset();
        session_destroy();
        header("Location: {$root}/views/auth/login.php?logout=1");
        exit();
    }

    // ================= RECUPERACION DE CONTRASENA =================
    // Flujo en 3 pasos sin correo electronico:
    // Paso 1 -> correo registrado | Paso 2 -> verifica numero de vivienda | Paso 3 -> nueva contrasena
    if ($action === 'recuperar_paso1') {
        $correo = trim($_POST['correo'] ?? '');

        if (empty($correo)) {
            $mensaje = "Por favor, ingrese su correo electrónico.";
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $mensaje]);
                exit();
            }
            $_SESSION['error_login'] = $mensaje;
            header("Location: {$root}/views/auth/recuperar.php");
            exit();
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerPorCorreo($correo);

        if (!$usuario) {
            $mensaje = "No existe una cuenta registrada con ese correo.";
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $mensaje]);
                exit();
            }
            $_SESSION['error_login'] = $mensaje;
            header("Location: {$root}/views/auth/recuperar.php");
            exit();
        }

        $_SESSION['recuperar'] = [
            'id_usuario' => (int)$usuario['id_usuario'],
            'paso'       => 2,
            'intentos'   => 0,
            'expira'     => time() + 600
        ];

        if ($esAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
        }
        header("Location: {$root}/views/auth/recuperar.php");
        exit();
    }

    if ($action === 'recuperar_paso2') {
        $sesion = $_SESSION['recuperar'] ?? null;

        if (!$sesion || (int)$sesion['paso'] !== 2 || $sesion['expira'] < time()) {
            unset($_SESSION['recuperar']);
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'reiniciar' => true, 'message' => "La recuperacion expiro. Comience de nuevo."]);
                exit();
            }
            header("Location: {$root}/views/auth/recuperar.php");
            exit();
        }

        $vivienda = trim($_POST['numero_vivienda'] ?? '');

        if (empty($vivienda)) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "Por favor, ingrese el número de su vivienda."]);
                exit();
            }
            $_SESSION['error_login'] = "Por favor, ingrese el número de su vivienda.";
            header("Location: {$root}/views/auth/recuperar.php");
            exit();
        }

        // Limite de intentos para evitar pruebas de fuerza bruta
        if ((int)$sesion['intentos'] >= 5) {
            unset($_SESSION['recuperar']);
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'reiniciar' => true, 'message' => "Demasiados intentos incorrectos. La verificacion fue cancelada."]);
                exit();
            }
            $_SESSION['error_login'] = "Demasiados intentos incorrectos. Comience de nuevo.";
            header("Location: {$root}/views/auth/recuperar.php");
            exit();
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerPorId($sesion['id_usuario']);
        $viviendaGuardada = strtolower(trim($usuario['numero_vivienda'] ?? ''));
        $viviendaIngresada = strtolower($vivienda);

        if (!$usuario || $viviendaIngresada === '' || $viviendaGuardada !== $viviendaIngresada) {
            $_SESSION['recuperar']['intentos'] = (int)$sesion['intentos'] + 1;
            $restantes = max(0, 5 - ($_SESSION['recuperar']['intentos']));
            $mensaje = "El número de vivienda no coincide. Intentos restantes: {$restantes}.";
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $mensaje]);
                exit();
            }
            $_SESSION['error_login'] = $mensaje;
            header("Location: {$root}/views/auth/recuperar.php");
            exit();
        }

        $_SESSION['recuperar']['paso']   = 3;
        $_SESSION['recuperar']['expira'] = time() + 600;

        if ($esAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
        }
        header("Location: {$root}/views/auth/recuperar.php");
        exit();
    }

    if ($action === 'recuperar_paso3') {
        $sesion = $_SESSION['recuperar'] ?? null;

        if (!$sesion || (int)$sesion['paso'] !== 3 || $sesion['expira'] < time()) {
            unset($_SESSION['recuperar']);
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'reiniciar' => true, 'message' => "La recuperacion expiro. Comience de nuevo."]);
                exit();
            }
            header("Location: {$root}/views/auth/recuperar.php");
            exit();
        }

        $clave        = $_POST['clave'] ?? '';
        $confirmacion = $_POST['confirmacion'] ?? '';

        if (strlen($clave) < 6) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "La contraseña debe tener al menos 6 caracteres."]);
                exit();
            }
            $_SESSION['error_login'] = "La contraseña debe tener al menos 6 caracteres.";
            header("Location: {$root}/views/auth/recuperar.php");
            exit();
        }

        if ($clave !== $confirmacion) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "Las contraseñas no coinciden."]);
                exit();
            }
            $_SESSION['error_login'] = "Las contraseñas no coinciden.";
            header("Location: {$root}/views/auth/recuperar.php");
            exit();
        }

        $usuarioModel = new Usuario();
        $actualizado = $usuarioModel->actualizarClave($sesion['id_usuario'], $clave);

        if (!$actualizado) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "No se pudo actualizar la contraseña. Intente nuevamente."]);
                exit();
            }
            $_SESSION['error_login'] = "No se pudo actualizar la contraseña.";
            header("Location: {$root}/views/auth/recuperar.php");
            exit();
        }

        unset($_SESSION['recuperar']);

        if ($esAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
        }
        $_SESSION['clave_actualizada'] = true;
        header("Location: {$root}/views/auth/login.php");
        exit();
    }
} else {
    header("Location: {$root}/views/auth/login.php");
    exit();
}
