<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Pago.php';

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
                $_SESSION['flash_error'] = "El archivo excede el limite de 5MB.";
                header('Location: ../views/residente/reporte_pagos.php');
                exit();
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['comprobante']['tmp_name']);
            finfo_close($finfo);

            $mimePermitidos = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!in_array($mime, $mimePermitidos)) {
                $_SESSION['flash_error'] = "Tipo de archivo no valido.";
                header('Location: ../views/residente/reporte_pagos.php');
                exit();
            }

            $filename = 'pago_' . $id_usuario . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $destination = __DIR__ . '/../public/uploads/' . $filename;

            if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $destination)) {
                $comprobante_url = 'public/uploads/' . $filename;
            }
        }

        $pagoModel = new Pago();
        $resultado = $pagoModel->registrar($id_usuario, $monto, $concepto, $comprobante_url);

        if ($resultado) {
            $_SESSION['flash_success'] = "Tu pago ha sido enviado correctamente a revision.";
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
            $pdo->prepare("DELETE FROM pagos WHERE id_pago = :id AND id_usuario = :uid")->execute([':id' => $id_pago, ':uid' => $_SESSION['id_usuario']]);
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

            $_SESSION['flash_success'] = "Reserva creada correctamente. Pendiente de aprobacion.";
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
            $pdo->prepare("DELETE FROM reservas WHERE id = :id AND id_usuario = :uid")->execute([':id' => $id_reserva, ':uid' => $_SESSION['id_usuario']]);
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
            $_SESSION['flash_error'] = "Seleccione una opcion para votar.";
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
