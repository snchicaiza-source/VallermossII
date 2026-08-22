<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/Validador.php';
require_once __DIR__ . '/../models/Notificacion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear_usuario') {
        $datos = [
            'cedula'          => trim($_POST['cedula'] ?? ''),
            'nombres'         => trim($_POST['nombres'] ?? ''),
            'correo'          => trim($_POST['correo'] ?? ''),
            'telefono_whatsapp' => trim($_POST['telefono_whatsapp'] ?? ''),
            'numero_vivienda' => trim($_POST['numero_vivienda'] ?? ''),
            'password'        => trim($_POST['password'] ?? ''),
            'rol'             => trim($_POST['rol'] ?? 'RESIDENTE'),
        ];

        // Guarda los valores para restaurarlos si hay error
        $_SESSION['form_old'] = $datos;

        // Validaciones con mensajes especificos por campo
        $errores = [
            'cedula'  => Validador::cedula($datos['cedula']),
            'nombres' => Validador::texto($datos['nombres'], 'Los nombres', 2, 120),
            'correo'  => Validador::correo($datos['correo']),
            'telefono_whatsapp' => $datos['telefono_whatsapp'] === '' ? null : Validador::telefono($datos['telefono_whatsapp']),
            'numero_vivienda'   => $datos['numero_vivienda'] === '' ? null : Validador::texto($datos['numero_vivienda'], 'La vivienda', 1, 30),
            'password' => Validador::contrasena($datos['password']),
        ];
        $primerError = Validador::primerError($errores);
        if ($primerError !== null) {
            $_SESSION['form_errors'] = $errores;
            $_SESSION['flash_error'] = $primerError;
            header('Location: ../views/administrador/usuarios.php');
            exit;
        }

        try {
            require_once __DIR__ . '/../models/Usuario.php';
            $usuarioModel = new Usuario();

            // Duplicados verificados con mensaje claro por campo
            if ($usuarioModel->existeCedula($datos['cedula'])) {
                $_SESSION['form_errors'] = ['cedula' => "La cédula {$datos['cedula']} ya está registrada."];
                $_SESSION['flash_error'] = "La cédula {$datos['cedula']} ya está registrada en el sistema.";
                header('Location: ../views/administrador/usuarios.php');
                exit;
            }
            $existenteCorreo = $usuarioModel->obtenerPorCorreo($datos['correo']);
            if ($existenteCorreo) {
                $_SESSION['form_errors'] = ['correo' => "El correo {$datos['correo']} ya está registrado."];
                $_SESSION['flash_error'] = "El correo {$datos['correo']} ya está registrado por otro usuario.";
                header('Location: ../views/administrador/usuarios.php');
                exit;
            }

            $hashedPassword = password_hash($datos['password'], PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (cedula, nombres, correo, telefono_whatsapp, numero_vivienda, clave_hash, rol) VALUES (:cedula, :nombres, :correo, :telefono, :numero_vivienda, :clave_hash, :rol)");
            $stmt->execute([
                ':cedula' => $datos['cedula'],
                ':nombres' => $datos['nombres'],
                ':correo' => $datos['correo'],
                ':telefono' => $datos['telefono_whatsapp'],
                ':numero_vivienda' => $datos['numero_vivienda'],
                ':clave_hash' => $hashedPassword,
                ':rol' => in_array($datos['rol'], ['RESIDENTE', 'DIRECTIVA', 'ADMINISTRADOR'], true) ? $datos['rol'] : 'RESIDENTE'
            ]);
            $nuevoId = (int)$pdo->lastInsertId();

            unset($_SESSION['form_old'], $_SESSION['form_errors']);
            $_SESSION['flash_success'] = "Usuario registrado exitosamente.";

            // Bienvenida al nuevo usuario
            Notificacion::enviar(
                $nuevoId,
                'SISTEMA',
                'Bienvenido a Vallermosso II',
                "Hola {$datos['nombres']}, tu cuenta fue creada correctamente. Ya puedes iniciar sesión con tu correo y contraseña."
            );

            header('Location: ../views/administrador/usuarios.php?msg=creado');
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Error al registrar el usuario. Verifique que la cédula o correo no estén duplicados.";
            header('Location: ../views/administrador/usuarios.php');
            exit;
        }
    }

    if ($action === 'editar_usuario') {
        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $datos = [
            'cedula'          => trim($_POST['cedula'] ?? ''),
            'nombres'         => trim($_POST['nombres'] ?? ''),
            'correo'          => trim($_POST['correo'] ?? ''),
            'telefono_whatsapp' => trim($_POST['telefono_whatsapp'] ?? ''),
            'numero_vivienda' => trim($_POST['numero_vivienda'] ?? ''),
            'password'        => trim($_POST['password'] ?? ''),
            'rol'             => trim($_POST['rol'] ?? 'RESIDENTE'),
        ];

        $_SESSION['form_old'] = $datos;

        if ($id_usuario <= 0) {
            $_SESSION['flash_error'] = "Usuario no valido.";
            header('Location: ../views/administrador/usuarios.php');
            exit;
        }

        $errores = [
            'cedula'  => Validador::cedula($datos['cedula']),
            'nombres' => Validador::texto($datos['nombres'], 'Los nombres', 2, 120),
            'correo'  => Validador::correo($datos['correo']),
            'telefono_whatsapp' => $datos['telefono_whatsapp'] === '' ? null : Validador::telefono($datos['telefono_whatsapp']),
            'numero_vivienda'   => $datos['numero_vivienda'] === '' ? null : Validador::texto($datos['numero_vivienda'], 'La vivienda', 1, 30),
            'password' => $datos['password'] === '' ? null : Validador::contrasena($datos['password']),
        ];
        $primerError = Validador::primerError($errores);
        if ($primerError !== null) {
            $_SESSION['form_errors'] = $errores;
            $_SESSION['flash_error'] = $primerError;
            header('Location: ../views/administrador/usuarios.php?editar=' . $id_usuario);
            exit;
        }

        require_once __DIR__ . '/../models/Usuario.php';
        $usuarioModel = new Usuario();

        // Evita duplicados con otros usuarios
        if ($usuarioModel->existeCedula($datos['cedula'], $id_usuario)) {
            $_SESSION['form_errors'] = ['cedula' => "La cédula {$datos['cedula']} ya pertenece a otro usuario."];
            $_SESSION['flash_error'] = "La cédula {$datos['cedula']} ya está registrada por otro usuario.";
            header('Location: ../views/administrador/usuarios.php?editar=' . $id_usuario);
            exit;
        }
        $existente = $usuarioModel->obtenerPorCorreo($datos['correo']);
        if ($existente && (int)$existente['id_usuario'] !== $id_usuario) {
            $_SESSION['form_errors'] = ['correo' => "El correo {$datos['correo']} ya pertenece a otro usuario."];
            $_SESSION['flash_error'] = "El correo {$datos['correo']} ya está registrado por otro usuario.";
            header('Location: ../views/administrador/usuarios.php?editar=' . $id_usuario);
            exit;
        }

        $ok = $usuarioModel->actualizar($id_usuario, [
            'cedula'          => $datos['cedula'],
            'nombres'         => $datos['nombres'],
            'correo'          => $datos['correo'],
            'telefono'        => $datos['telefono_whatsapp'],
            'numero_vivienda' => $datos['numero_vivienda'],
            'rol'             => $datos['rol'],
            'password'        => $datos['password']
        ]);

        unset($_SESSION['form_old'], $_SESSION['form_errors']);

        if ($ok) {
            $_SESSION['flash_success'] = "Usuario actualizado correctamente.";
            header('Location: ../views/administrador/usuarios.php');
        } else {
            $_SESSION['flash_error'] = "Error al actualizar el usuario.";
            header('Location: ../views/administrador/usuarios.php?editar=' . $id_usuario);
        }
        exit;
    }

    if ($action === 'cambiar_estado_usuario') {
        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $nuevo_estado = ($_POST['nuevo_estado'] ?? '') === 'BLOQUEADO' ? 'BLOQUEADO' : 'ACTIVO';

        if ($id_usuario <= 0) {
            header('Location: ../views/administrador/usuarios.php');
            exit;
        }

        // Un administrador no puede bloquearse a si mismo
        if ($id_usuario === (int)($_SESSION['id_usuario'] ?? 0)) {
            $_SESSION['flash_error'] = "No puedes cambiar el estado de tu propia cuenta.";
            header('Location: ../views/administrador/usuarios.php');
            exit;
        }

        require_once __DIR__ . '/../models/Usuario.php';
        $usuarioModel = new Usuario();

        if ($usuarioModel->cambiarEstado($id_usuario, $nuevo_estado)) {
            $_SESSION['flash_success'] = $nuevo_estado === 'BLOQUEADO'
                ? "Usuario bloqueado. Ya no podrá iniciar sesión."
                : "Usuario activado correctamente.";

            // Notifica al usuario afectado
            Notificacion::enviar(
                $id_usuario,
                'SISTEMA',
                $nuevo_estado === 'BLOQUEADO' ? 'Tu cuenta fue bloqueada' : 'Tu cuenta fue activada',
                $nuevo_estado === 'BLOQUEADO'
                    ? 'Tu acceso al sistema ha sido bloqueado por la administración. Contacta al administrador si crees que es un error.'
                    : 'Tu acceso al sistema ha sido restablecido. Ya puedes iniciar sesión nuevamente.'
            );
        } else {
            $_SESSION['flash_error'] = "Error al cambiar el estado del usuario.";
        }
        header('Location: ../views/administrador/usuarios.php');
        exit;
    }

    if ($action === 'cambiar_clave_usuario') {
        // Solo el administrador puede restablecer contrasenas
        $rolSesion = strtoupper($_SESSION['rol'] ?? $_SESSION['usuario_rol'] ?? '');
        if ($rolSesion !== 'ADMINISTRADOR') {
            $_SESSION['flash_error'] = "Solo el administrador puede cambiar contraseñas.";
            header('Location: ../views/administrador/usuarios.php');
            exit;
        }

        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $nueva_clave = trim($_POST['nueva_clave'] ?? '');
        $confirmar = trim($_POST['confirmar_clave'] ?? '');

        if ($id_usuario <= 0 || empty($nueva_clave)) {
            $_SESSION['flash_error'] = "Selecciona un usuario e ingresa la nueva contraseña.";
            header('Location: ../views/administrador/usuarios.php');
            exit;
        }
        if (strlen($nueva_clave) < 6) {
            $_SESSION['flash_error'] = "La contraseña debe tener al menos 6 caracteres.";
            header('Location: ../views/administrador/usuarios.php');
            exit;
        }
        if ($nueva_clave !== $confirmar) {
            $_SESSION['flash_error'] = "Las contraseñas no coinciden.";
            header('Location: ../views/administrador/usuarios.php');
            exit;
        }

        try {
            $pdo = Database::obtenerConexion();
            $stmt = $pdo->prepare("UPDATE usuarios SET clave_hash = :clave WHERE id_usuario = :id");
            $stmt->execute([
                ':clave' => password_hash($nueva_clave, PASSWORD_BCRYPT),
                ':id'    => $id_usuario
            ]);

            $q = $pdo->prepare("SELECT nombres FROM usuarios WHERE id_usuario = :id");
            $q->execute([':id' => $id_usuario]);
            $nombreUsuario = (string)$q->fetchColumn();

            $_SESSION['flash_success'] = "Contraseña actualizada correctamente para {$nombreUsuario}.";

            Notificacion::enviar(
                $id_usuario,
                'SISTEMA',
                'Tu contraseña fue actualizada',
                "Hola {$nombreUsuario}, la administración restableció tu contraseña. Úsala para iniciar sesión; puedes cambiarla después con el administrador."
            );
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Error al actualizar la contraseña.";
        }
        header('Location: ../views/administrador/usuarios.php');
        exit;
    }

    if ($action === 'cambiar_clave_propia') {
        // Cualquier usuario puede cambiar su PROPIA contrasena
        $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
        $clave_actual = trim($_POST['clave_actual'] ?? '');
        $nueva_clave = trim($_POST['nueva_clave'] ?? '');
        $confirmar = trim($_POST['confirmar_clave'] ?? '');

        $volver = '../views/residente/perfil.php';
        $rolSesion = strtoupper($_SESSION['rol'] ?? $_SESSION['usuario_rol'] ?? '');
        if ($rolSesion === 'ADMINISTRADOR' || $rolSesion === 'DIRECTIVA') {
            $volver = '../views/' . strtolower($rolSesion) . '/perfil.php';
        }

        if ($id_usuario <= 0 || empty($clave_actual) || empty($nueva_clave)) {
            $_SESSION['flash_error'] = "Completa todos los campos.";
            header("Location: $volver");
            exit;
        }
        if (strlen($nueva_clave) < 6) {
            $_SESSION['flash_error'] = "La nueva contraseña debe tener al menos 6 caracteres.";
            header("Location: $volver");
            exit;
        }
        if ($nueva_clave !== $confirmar) {
            $_SESSION['flash_error'] = "Las contraseñas nuevas no coinciden.";
            header("Location: $volver");
            exit;
        }

        try {
            $pdo = Database::obtenerConexion();
            $stmt = $pdo->prepare("SELECT clave_hash FROM usuarios WHERE id_usuario = :id");
            $stmt->execute([':id' => $id_usuario]);
            $hashActual = (string)$stmt->fetchColumn();

            // Verifica la contrasena actual antes de permitir el cambio
            if ($hashActual === '' || !password_verify($clave_actual, $hashActual)) {
                $_SESSION['flash_error'] = "La contraseña actual es incorrecta.";
                header("Location: $volver");
                exit;
            }

            $upd = $pdo->prepare("UPDATE usuarios SET clave_hash = :clave WHERE id_usuario = :id");
            $upd->execute([
                ':clave' => password_hash($nueva_clave, PASSWORD_BCRYPT),
                ':id'    => $id_usuario
            ]);

            $_SESSION['flash_success'] = "Tu contraseña fue actualizada correctamente.";
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Error al actualizar la contraseña.";
        }
        header("Location: $volver");
        exit;
    }

    if ($action === 'actualizar_perfil') {
        $id_usuario = $_SESSION['id_usuario'] ?? 0;
        $nombres = trim($_POST['nombres'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono_whatsapp'] ?? '');

        $errorPerfil = Validador::primerError([
            'nombres' => Validador::texto($nombres, 'Los nombres', 2, 120),
            'correo'  => Validador::correo($correo),
            'telefono_whatsapp' => $telefono === '' ? null : Validador::telefono($telefono),
        ]);
        if ($errorPerfil !== null) {
            $_SESSION['flash_error'] = $errorPerfil;
        } elseif ($id_usuario > 0) {
            try {
                // Evita que otro usuario ya tenga ese correo
                require_once __DIR__ . '/../models/Usuario.php';
                $existente = (new Usuario())->obtenerPorCorreo($correo);
                if ($existente && (int)$existente['id_usuario'] !== (int)$id_usuario) {
                    $_SESSION['flash_error'] = "El correo {$correo} ya está registrado por otro usuario.";
                    header('Location: ../views/residente/perfil.php');
                    exit;
                }

                $pdo = Database::obtenerConexion();
                $stmt = $pdo->prepare("UPDATE usuarios SET nombres = :nombres, correo = :correo, telefono_whatsapp = :telefono WHERE id_usuario = :id");
                $stmt->execute([
                    ':nombres' => $nombres,
                    ':correo' => $correo,
                    ':telefono' => $telefono,
                    ':id' => $id_usuario
                ]);

                $_SESSION['nombres'] = $nombres;
                $_SESSION['usuario_nombres'] = $nombres;
                $_SESSION['correo'] = $correo;
                $_SESSION['usuario_correo'] = $correo;

                $_SESSION['flash_success'] = "Perfil actualizado correctamente.";
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Error al actualizar el perfil.";
            }
        }
        header('Location: ../views/residente/perfil.php');
        exit;
    }
}
