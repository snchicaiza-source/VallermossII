<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

        if ($action === 'crear_reporte') {
            $id_usuario = $_SESSION['id_usuario'] ?? null;
            $tipo = trim($_POST['tipo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $return_to = trim($_POST['return_to'] ?? '');
            $redirect = ($return_to === 'denuncias') ? '../views/residente/denuncias.php' : '../views/residente/reportar_danos.php';

            if (!$id_usuario || empty($tipo) || empty($descripcion)) {
                $_SESSION['flash_error'] = "Complete todos los campos.";
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

                $_SESSION['flash_success'] = "Reporte enviado correctamente.";
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
                $id_usuario = $_SESSION['id_usuario'] ?? 0;
                $pdo = Database::obtenerConexion();
                $stmt = $pdo->prepare("DELETE FROM incidencias WHERE id_incidencia = :id AND id_usuario = :uid");
                $stmt->execute([':id' => $id_incidencia, ':uid' => $id_usuario]);
                $_SESSION['flash_success'] = 'Registro eliminado.';
            }
            header('Location: ' . $redirect);
            exit();
        }
}
