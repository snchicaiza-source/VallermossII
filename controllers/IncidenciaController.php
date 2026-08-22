<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Notificacion.php';
require_once __DIR__ . '/../models/Incidencia.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    Incidencia::asegurarTabla();

        if ($action === 'crear_reporte') {
            $id_usuario = $_SESSION['id_usuario'] ?? null;
            $tipo = trim($_POST['tipo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $return_to = trim($_POST['return_to'] ?? '');
            $redirect = ($return_to === 'denuncias') ? '../views/residente/denuncias.php' : '../views/residente/reportar_danos.php';

            // El residente ya no elige tipo en reportes de danos: se asigna automatico.
            // Denuncias de convivencia se guardan como QUEJA; lo demas como DANO.
            $permitidos = ['DANO', 'QUEJA', 'RESERVACION'];
            if (!in_array($tipo, $permitidos, true)) {
                $tipo = ($return_to === 'denuncias') ? 'QUEJA' : 'DANO';
            }

            if (!$id_usuario || empty($descripcion)) {
                $_SESSION['flash_error'] = "Escribe la descripción del reporte.";
                header('Location: ' . $redirect);
                exit;
            }

            try {
                $pdo = Database::obtenerConexion();
                $stmt = $pdo->prepare("INSERT INTO incidencias (id_usuario, tipo, descripcion, estado) VALUES (:id_usuario, :tipo, :descripcion, 'PENDIENTE')");
                $stmt->execute([
                    ':id_usuario' => $id_usuario,
                    ':tipo' => $tipo,
                    ':descripcion' => $descripcion
                ]);
                $idIncidencia = (int)$pdo->lastInsertId();

                $_SESSION['flash_success'] = "Reporte enviado correctamente.";

                // Notifica a administracion y directiva sobre el nuevo reporte
                Notificacion::enviarGestion(
                    'INCIDENCIA',
                    'Nueva incidencia reportada',
                    Notificacion::nombreUsuario($id_usuario) . " reporto: {$tipo} - " . mb_substr($descripcion, 0, 120),
                    $idIncidencia,
                    'incidencia',
                    $id_usuario
                );

                header('Location: ' . $redirect);
                exit;
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Error al enviar el reporte.";
                header('Location: ' . $redirect);
                exit;
            }
        }

        if ($action === 'eliminar_incidencia') {
            $id_incidencia = (int)($_POST['id_incidencia'] ?? 0);
            $return_to = trim($_POST['return_to'] ?? 'reportar_danos');
            $redirect = ($return_to === 'denuncias') ? '../views/residente/denuncias.php' : '../views/residente/reportar_danos.php';

            if ($id_incidencia > 0) {
                try {
                    $id_usuario = $_SESSION['id_usuario'] ?? 0;
                    $pdo = Database::obtenerConexion();

                    // Captura datos para el log antes de eliminar
                    $q = $pdo->prepare("SELECT tipo FROM incidencias WHERE id = :id AND id_usuario = :uid");
                    $q->execute([':id' => $id_incidencia, ':uid' => $id_usuario]);
                    $tipoIncidencia = (string)$q->fetchColumn();

                    // La columna real es 'id' (esquema de produccion)
                    $stmt = $pdo->prepare("DELETE FROM incidencias WHERE id = :id AND id_usuario = :uid");
                    $stmt->execute([':id' => $id_incidencia, ':uid' => $id_usuario]);

                    require_once __DIR__ . '/../models/Logger.php';
                    Logger::eliminacion('Incidencias', "El residente eliminó su incidencia #{$id_incidencia}" . ($tipoIncidencia !== '' ? " ({$tipoIncidencia})" : ''));
                    $_SESSION['flash_success'] = 'Registro eliminado.';
                } catch (PDOException $e) {
                    $_SESSION['flash_error'] = 'No se pudo eliminar el registro.';
                }
            }
            header('Location: ' . $redirect);
            exit();
        }
}
