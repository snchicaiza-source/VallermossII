<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Notificacion.php';

verificarRol(['DIRECTIVA']);

$db = Database::obtenerConexion();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'crear_encuesta':
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $opciones = $_POST['opciones'] ?? [];
        $fecha_cierre = trim($_POST['fecha_cierre'] ?? null);

        $opcionesFiltradas = array_filter(array_map('trim', $opciones));

        if (empty($titulo) || count($opcionesFiltradas) < 2) {
            $_SESSION['flash_error'] = 'Ingrese un título y al menos 2 opciones.';
            header('Location: ../views/directiva/encuestas.php');
            exit();
        }

        $id_usuario = $_SESSION['id_usuario'] ?? 1;
        $stmt = $db->prepare("INSERT INTO encuestas (titulo, descripcion, opciones, activa, creada_por, fecha_creacion, fecha_cierre) VALUES (:titulo, :descripcion, :opciones, 1, :creada_por, NOW(), :fecha_cierre)");
        $stmt->execute([
            ':titulo' => $titulo,
            ':descripcion' => $descripcion,
            ':opciones' => json_encode(array_values($opcionesFiltradas)),
            ':creada_por' => $id_usuario,
            ':fecha_cierre' => $fecha_cierre ?: null
        ]);
        $idEncuesta = (int)$db->lastInsertId();

        $_SESSION['flash_success'] = 'Encuesta creada correctamente.';

        // Notifica a todos los residentes sobre la nueva encuesta
        Notificacion::enviarResidentes(
            'ENCUESTA',
            'Nueva encuesta disponible',
            "Participa en la encuesta \"{$titulo}\". Tu voto es importante.",
            $idEncuesta,
            'encuesta'
        );

        header('Location: ../views/directiva/encuestas.php');
        exit();

    case 'toggle_encuesta':
        $id_encuesta = (int)($_POST['id_encuesta'] ?? 0);
        $nuevo_estado = (int)($_POST['nuevo_estado'] ?? 0);

        if ($id_encuesta > 0) {
            $stmt = $db->prepare("UPDATE encuestas SET activa = :activa WHERE id = :id");
            $stmt->execute([':activa' => $nuevo_estado, ':id' => $id_encuesta]);
            $_SESSION['flash_success'] = $nuevo_estado ? 'Encuesta activada.' : 'Encuesta cerrada.';
        }
        header('Location: ../views/directiva/encuestas.php');
        exit();

    case 'eliminar_encuesta':
        $id_encuesta = (int)($_POST['id_encuesta'] ?? 0);
        if ($id_encuesta > 0) {
            try {
                $q = $db->prepare("SELECT titulo FROM encuestas WHERE id = :id");
                $q->execute([':id' => $id_encuesta]);
                $tituloEncuesta = (string)$q->fetchColumn();

                $db->prepare("DELETE FROM encuestas_votos WHERE id_encuesta = :id")->execute([':id' => $id_encuesta]);
                $db->prepare("DELETE FROM encuestas WHERE id = :id")->execute([':id' => $id_encuesta]);

                require_once __DIR__ . '/../models/Logger.php';
                Logger::eliminacion('Encuestas', "Encuesta #{$id_encuesta} \"{$tituloEncuesta}\" (directiva)");
                $_SESSION['flash_success'] = 'Encuesta eliminada.';
            } catch (PDOException $e) { /* noop */ }
        }
        header('Location: ../views/directiva/encuestas.php');
        exit();

    case 'cambiar_estado_reserva':
        $id_reserva = (int)($_POST['id_reserva'] ?? 0);
        $nuevo_estado = $_POST['nuevo_estado'] ?? '';
        if ($id_reserva > 0 && in_array($nuevo_estado, ['APROBADA', 'RECHAZADA'])) {
            // Obtiene el dueno antes de actualizar
            $stmtOwner = $db->prepare("SELECT id_usuario, espacio, fecha_reserva FROM reservas WHERE id = :id");
            $stmtOwner->execute([':id' => $id_reserva]);
            $reserva = $stmtOwner->fetch(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("UPDATE reservas SET estado = :estado WHERE id = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_reserva]);
            $_SESSION['flash_success'] = 'Reserva actualizada.';

            // Notifica al residente dueno de la reserva
            if ($reserva) {
                Notificacion::enviar(
                    (int)$reserva['id_usuario'],
                    'RESERVA',
                    $nuevo_estado === 'APROBADA' ? 'Tu reserva fue aprobada' : 'Tu reserva fue rechazada',
                    "La reserva de \"{$reserva['espacio']}\" para el {$reserva['fecha_reserva']} fue " . ($nuevo_estado === 'APROBADA' ? 'aprobada' : 'rechazada') . ".",
                    $id_reserva,
                    'reserva'
                );
            }
        }
        header('Location: ../views/directiva/dashboard.php');
        exit();

    default:
        header('Location: ../views/directiva/dashboard.php');
        exit();
}
