<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Pago.php';
require_once __DIR__ . '/../models/Notificacion.php';

verificarRol(['RESIDENTE']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'subir_pago') {
        $monto = (float)($_POST['monto'] ?? 0);
        $concepto = trim($_POST['concepto'] ?? '');
        $id_usuario = $_SESSION['id_usuario'];

        if ($monto <= 0 || empty($concepto)) {
            $_SESSION['flash_error'] = "Complete todos los campos obligatorios.";
            header('Location: ../views/residente/reporte_pagos.php');
            exit();
        }

        $comprobante_url = '';

        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            $ext = strtolower(pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $permitidos)) {
                $_SESSION['flash_error'] = "Tipo de archivo no permitido. Use: JPG, PNG, GIF o PDF.";
                header('Location: ../views/residente/reporte_pagos.php');
                exit();
            }

            $maxSize = 5 * 1024 * 1024;
            if ($_FILES['comprobante']['size'] > $maxSize) {
                $_SESSION['flash_error'] = "El archivo excede el límite de 5MB.";
                header('Location: ../views/residente/reporte_pagos.php');
                exit();
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['comprobante']['tmp_name']);
            finfo_close($finfo);

            $mimePermitidos = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!in_array($mime, $mimePermitidos)) {
                $_SESSION['flash_error'] = "Tipo de archivo no válido.";
                header('Location: ../views/residente/reporte_pagos.php');
                exit();
            }

            $filename = 'pago_' . $id_usuario . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $uploadDir = __DIR__ . '/../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $destination)) {
                $comprobante_url = 'public/uploads/' . $filename;
            }
        }

        $pagoModel = new Pago();
        $resultado = $pagoModel->registrar($id_usuario, $monto, $concepto, $comprobante_url);

        if ($resultado) {
            $_SESSION['flash_success'] = "Tu pago ha sido enviado correctamente a revisión.";

            // Notifica solo a la administracion (la revision de pagos es exclusiva del admin)
            Notificacion::enviarRol(
                'ADMINISTRADOR',
                'PAGO',
                'Nuevo pago enviado a revisión',
                Notificacion::nombreUsuario($id_usuario) . " envió el pago \"{$concepto}\" por $" . number_format($monto, 2) . ".",
                (int)$resultado,
                'pago',
                $id_usuario
            );
        } else {
            $_SESSION['flash_error'] = "Error al guardar el pago en la base de datos.";
        }

        header('Location: ../views/residente/reporte_pagos.php');
        exit();
    }

    if ($action === 'eliminar_pago') {
        $id_pago = (int)($_POST['id_pago'] ?? 0);
        if ($id_pago > 0) {
            $pdo = Database::obtenerConexion();

            // Captura datos para el log antes de eliminar
            $q = $pdo->prepare("SELECT concepto, monto FROM pagos WHERE id_pago = :id AND id_usuario = :uid");
            $q->execute([':id' => $id_pago, ':uid' => $_SESSION['id_usuario']]);
            $datosPago = $q->fetch(PDO::FETCH_ASSOC);

            $pdo->prepare("DELETE FROM pagos WHERE id_pago = :id AND id_usuario = :uid")->execute([':id' => $id_pago, ':uid' => $_SESSION['id_usuario']]);

            require_once __DIR__ . '/../models/Logger.php';
            Logger::eliminacion('Pagos', $datosPago
                ? "El residente eliminó su pago #{$id_pago} \"{$datosPago['concepto']}\" (\$" . number_format((float)$datosPago['monto'], 2) . ")"
                : "El residente eliminó su pago #{$id_pago}");
            $_SESSION['flash_success'] = 'Pago eliminado.';
        }
        header('Location: ../views/residente/reporte_pagos.php');
        exit();
    }

    if ($action === 'crear_reserva') {
        $id_usuario = $_SESSION['id_usuario'];
        $espacio = trim($_POST['espacio'] ?? '');
        if ($espacio === 'OTROS') {
            $espacio = trim($_POST['otro_espacio'] ?? '');
            if (empty($espacio)) {
                $espacio = 'OTROS';
            }
        }
        $fecha_reserva = trim($_POST['fecha_reserva'] ?? '');
        $hora_inicio = trim($_POST['hora_inicio'] ?? '');
        $hora_fin = trim($_POST['hora_fin'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');

        if (empty($espacio) || empty($fecha_reserva) || empty($hora_inicio) || empty($hora_fin)) {
            $_SESSION['flash_error'] = "Complete todos los campos obligatorios.";
            header('Location: ../views/residente/reservas.php');
            exit();
        }

        // La fecha no puede estar en el pasado
        $tsFecha = strtotime($fecha_reserva);
        if ($tsFecha === false || $tsFecha < strtotime(date('Y-m-d'))) {
            $_SESSION['flash_error'] = "No puedes hacer reservas en fechas pasadas.";
            header('Location: ../views/residente/reservas.php');
            exit();
        }

        // Horas con formato HH:MM validas y hora fin posterior a hora inicio
        if (!preg_match('/^\d{2}:\d{2}$/', $hora_inicio) || !preg_match('/^\d{2}:\d{2}$/', $hora_fin)) {
            $_SESSION['flash_error'] = "Formato de hora inválido.";
            header('Location: ../views/residente/reservas.php');
            exit();
        }
        if (strcmp($hora_fin, $hora_inicio) <= 0) {
            $_SESSION['flash_error'] = "La hora de fin debe ser posterior a la hora de inicio.";
            header('Location: ../views/residente/reservas.php');
            exit();
        }

        try {
            $pdo = Database::obtenerConexion();
            $stmt = $pdo->prepare("INSERT INTO reservas (id_usuario, espacio, fecha_reserva, hora_inicio, hora_fin, observaciones, estado, fecha_registro) VALUES (:id_usuario, :espacio, :fecha_reserva, :hora_inicio, :hora_fin, :observaciones, 'PENDIENTE', NOW())");
            $stmt->execute([
                ':id_usuario'    => $id_usuario,
                ':espacio'       => $espacio,
                ':fecha_reserva' => $fecha_reserva,
                ':hora_inicio'   => $hora_inicio,
                ':hora_fin'      => $hora_fin,
                ':observaciones' => $observaciones
            ]);
            $idReserva = (int)$pdo->lastInsertId();

            $_SESSION['flash_success'] = "Reserva creada correctamente. Pendiente de aprobación.";

            // Notifica a administracion y directiva sobre la nueva solicitud
            Notificacion::enviarGestion(
                'RESERVA',
                'Nueva solicitud de reserva',
                Notificacion::nombreUsuario($id_usuario) . " solicito \"{$espacio}\" para el {$fecha_reserva} ({$hora_inicio} - {$hora_fin}).",
                $idReserva,
                'reserva',
                $id_usuario
            );
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Error al crear la reserva.";
        }

        header('Location: ../views/residente/reservas.php');
        exit();
    }

    if ($action === 'eliminar_reserva') {
        $id_reserva = (int)($_POST['id_reserva'] ?? 0);
        if ($id_reserva > 0) {
            $pdo = Database::obtenerConexion();

            $q = $pdo->prepare("SELECT espacio, fecha_reserva FROM reservas WHERE id = :id AND id_usuario = :uid");
            $q->execute([':id' => $id_reserva, ':uid' => $_SESSION['id_usuario']]);
            $datosReserva = $q->fetch(PDO::FETCH_ASSOC);

            $pdo->prepare("DELETE FROM reservas WHERE id = :id AND id_usuario = :uid")->execute([':id' => $id_reserva, ':uid' => $_SESSION['id_usuario']]);

            require_once __DIR__ . '/../models/Logger.php';
            Logger::eliminacion('Reservas', $datosReserva
                ? "El residente eliminó su reserva #{$id_reserva} de \"{$datosReserva['espacio']}\" ({$datosReserva['fecha_reserva']})"
                : "El residente eliminó su reserva #{$id_reserva}");
            $_SESSION['flash_success'] = 'Reserva eliminada.';
        }
        header('Location: ../views/residente/reservas.php');
        exit();
    }

    if ($action === 'votar_encuesta') {
        $id_usuario = $_SESSION['id_usuario'];
        $id_encuesta = (int)($_POST['id_encuesta'] ?? 0);
        $respuesta = trim($_POST['respuesta'] ?? '');

        if ($id_encuesta <= 0 || empty($respuesta)) {
            $_SESSION['flash_error'] = "Seleccione una opción para votar.";
            header('Location: ../views/residente/encuestas.php');
            exit();
        }

        try {
            $pdo = Database::obtenerConexion();

            $check = $pdo->prepare("SELECT id FROM encuestas_votos WHERE id_encuesta = :eid AND id_usuario = :uid");
            $check->execute([':eid' => $id_encuesta, ':uid' => $id_usuario]);
            if ($check->fetch()) {
                $_SESSION['flash_error'] = "Ya has votado en esta encuesta.";
                header('Location: ../views/residente/encuestas.php');
                exit();
            }

            $stmt = $pdo->prepare("INSERT INTO encuestas_votos (id_encuesta, id_usuario, respuesta, fecha) VALUES (:eid, :uid, :respuesta, NOW())");
            $stmt->execute([
                ':eid'       => $id_encuesta,
                ':uid'       => $id_usuario,
                ':respuesta' => $respuesta
            ]);

            $_SESSION['flash_success'] = "Tu voto ha sido registrado correctamente.";
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Error al registrar tu voto.";
        }

        header('Location: ../views/residente/encuestas.php');
        exit();
    }
}
