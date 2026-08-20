<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../config/db.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // --- ACTIVOS ---
    case 'crear_activo':
        $nombre = trim($_POST['nombre'] ?? '');
        $estado = trim($_POST['estado'] ?? 'BUENO');
        $costo = (float)($_POST['costo_aproximado'] ?? 0);

        if (empty($nombre)) {
            $_SESSION['flash_error'] = 'Por favor complete el nombre del activo.';
            header('Location: ../views/administrador/activos.php');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO activos (nombre, estado, costo_aproximado) VALUES (:nombre, :estado, :costo)");
        $stmt->execute([':nombre' => $nombre, ':estado' => $estado, ':costo' => $costo > 0 ? $costo : null]);

        $_SESSION['flash_success'] = 'Activo registrado correctamente.';
        header('Location: ../views/administrador/activos.php');
        exit();

    case 'eliminar_activo':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM activos WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
        $_SESSION['flash_success'] = 'Registro eliminado correctamente.';
        header('Location: ../views/administrador/activos.php');
        exit();

    // --- CONVENIOS ---
    case 'crear_convenio':
        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $monto_total = (float)($_POST['monto_total'] ?? 0);
        $num_cuotas = (int)($_POST['num_cuotas'] ?? 0);

        if ($id_usuario <= 0 || $monto_total <= 0 || $num_cuotas <= 0) {
            $_SESSION['flash_error'] = 'Por favor complete todos los campos obligatorios.';
            header('Location: ../views/administrador/convenios.php');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO convenios (id_usuario, monto_total, num_cuotas, estado) VALUES (:id_usuario, :monto_total, :num_cuotas, 'ACTIVO')");
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':monto_total' => $monto_total,
            ':num_cuotas' => $num_cuotas
        ]);

        $_SESSION['flash_success'] = 'Convenio registrado correctamente.';
        header('Location: ../views/administrador/convenios.php');
        exit();

    case 'cambiar_estado_convenio':
        $id = (int)($_POST['id'] ?? 0);
        $nuevo_estado = $_POST['nuevo_estado'] ?? '';

        if ($id > 0 && in_array($nuevo_estado, ['CUMPLIDO', 'INCUMPLIDO'])) {
            $stmt = $db->prepare("UPDATE convenios SET estado = :estado WHERE id = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);
        }
        $_SESSION['flash_success'] = 'Estado del convenio actualizado.';
        header('Location: ../views/administrador/convenios.php');
        exit();

    // --- TRAMITES ---
    case 'crear_tramite':
        $solicitante = trim($_POST['solicitante'] ?? '');
        $asunto = trim($_POST['asunto'] ?? '');

        if (empty($solicitante) || empty($asunto)) {
            $_SESSION['flash_error'] = 'Por favor complete todos los campos obligatorios.';
            header('Location: ../views/administrador/tramites.php');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO tramites (solicitante, asunto, estado) VALUES (:solicitante, :asunto, 'PENDIENTE')");
        $stmt->execute([':solicitante' => $solicitante, ':asunto' => $asunto]);

        $_SESSION['flash_success'] = 'Tramite registrado correctamente.';
        header('Location: ../views/administrador/tramites.php');
        exit();

    case 'cambiar_estado_tramite':
        $id = (int)($_POST['id'] ?? 0);
        $nuevo_estado = $_POST['nuevo_estado'] ?? '';

        if ($id > 0 && in_array($nuevo_estado, ['EN_PROCESO', 'COMPLETADO'])) {
            $stmt = $db->prepare("UPDATE tramites SET estado = :estado WHERE id = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);
        }
        $_SESSION['flash_success'] = 'Estado del tramite actualizado.';
        header('Location: ../views/administrador/tramites.php');
        exit();

    // --- INCIDENCIAS ---
    case 'cambiar_estado_incidencia':
        $id = (int)($_POST['id'] ?? 0);
        $nuevo_estado = $_POST['nuevo_estado'] ?? '';

        if ($id > 0 && in_array($nuevo_estado, ['EN_REVISION', 'RESUELTO'])) {
            $stmt = $db->prepare("UPDATE incidencias SET estado = :estado WHERE id = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);
        }
        $_SESSION['flash_success'] = 'Estado de la incidencia actualizado.';
        header('Location: ../views/administrador/incidencias.php');
        exit();

    // --- RECAUDACIONES ---
    case 'crear_recaudacion':
        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $concepto = trim($_POST['concepto'] ?? '');
        $monto = (float)($_POST['monto'] ?? 0);
        $fecha_pago = trim($_POST['fecha_pago'] ?? '');
        $observacion = trim($_POST['observacion'] ?? '');

        if ($id_usuario <= 0 || empty($concepto) || $monto <= 0 || empty($fecha_pago)) {
            $_SESSION['flash_error'] = 'Por favor complete todos los campos obligatorios.';
            header('Location: ../views/administrador/recaudacion.php');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO recaudaciones (id_usuario, concepto, monto, fecha_pago, observacion, estado_pago, fecha_registro) VALUES (:id_usuario, :concepto, :monto, :fecha_pago, :observacion, 'PENDIENTE', NOW())");
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':concepto' => $concepto,
            ':monto' => $monto,
            ':fecha_pago' => $fecha_pago,
            ':observacion' => $observacion
        ]);

        $_SESSION['flash_success'] = 'Recaudacion registrada correctamente.';
        header('Location: ../views/administrador/recaudacion.php');
        exit();

    case 'cambiar_estado_recaudacion':
        $id_pago = (int)($_POST['id_pago'] ?? 0);
        $nuevo_estado = $_POST['nuevo_estado'] ?? '';

        if ($id_pago > 0 && in_array($nuevo_estado, ['APROBADO', 'RECHAZADO'])) {
            $stmt = $db->prepare("UPDATE recaudaciones SET estado_pago = :estado WHERE id_pago = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_pago]);
            $_SESSION['flash_success'] = 'Estado de la recaudacion actualizado.';
        } else {
            $_SESSION['flash_error'] = 'Accion no valida.';
        }
        header('Location: ../views/administrador/recaudacion.php');
        exit();

    // --- PROVEEDORES ---
    case 'crear_proveedor':
        $nombre_empresa = trim($_POST['nombre_empresa'] ?? '');
        $servicio_rubro = trim($_POST['servicio_rubro'] ?? '');
        $contacto = trim($_POST['contacto'] ?? '');
        $monto_contrato = (float)($_POST['monto_contrato'] ?? 0);
        $estado_pago = trim($_POST['estado_pago'] ?? 'PENDIENTE');

        if (empty($nombre_empresa) || empty($servicio_rubro) || $monto_contrato <= 0) {
            $_SESSION['flash_error'] = 'Por favor complete todos los campos obligatorios.';
            header('Location: ../views/administrador/proveedores.php');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO proveedores (nombre_empresa, servicio_rubro, contacto, monto_contrato, estado_pago, created_at) VALUES (:nombre_empresa, :servicio_rubro, :contacto, :monto_contrato, :estado_pago, NOW())");
        $stmt->execute([
            ':nombre_empresa' => $nombre_empresa,
            ':servicio_rubro' => $servicio_rubro,
            ':contacto' => $contacto,
            ':monto_contrato' => $monto_contrato,
            ':estado_pago' => $estado_pago
        ]);

        $_SESSION['flash_success'] = 'Proveedor registrado correctamente.';
        header('Location: ../views/administrador/proveedores.php');
        exit();

    case 'cambiar_estado_proveedor':
        $id_proveedor = (int)($_POST['id_proveedor'] ?? 0);
        $nuevo_estado = $_POST['nuevo_estado'] ?? '';

        if ($id_proveedor > 0 && in_array($nuevo_estado, ['AL_DIA', 'PENDIENTE', 'EN_PROCESO'])) {
            $stmt = $db->prepare("UPDATE proveedores SET estado_pago = :estado WHERE id_proveedor = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_proveedor]);
            $_SESSION['flash_success'] = 'Estado del proveedor actualizado.';
        } else {
            $_SESSION['flash_error'] = 'Accion no valida.';
        }
        header('Location: ../views/administrador/proveedores.php');
        exit();

    // --- PRESUPUESTO ---
    case 'crear_presupuesto':
        $rubro = trim($_POST['rubro'] ?? '');
        $monto_asignado = (float)($_POST['monto_asignado'] ?? 0);
        $periodo = trim($_POST['periodo'] ?? '');

        if (empty($rubro) || $monto_asignado <= 0 || empty($periodo)) {
            $_SESSION['flash_error'] = 'Por favor complete todos los campos obligatorios.';
            header('Location: ../views/administrador/ejecucion_presupuestaria.php');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO presupuesto (rubro, monto_asignado, monto_ejecutado, periodo) VALUES (:rubro, :monto_asignado, 0, :periodo)");
        $stmt->execute([
            ':rubro' => $rubro,
            ':monto_asignado' => $monto_asignado,
            ':periodo' => $periodo
        ]);

        $_SESSION['flash_success'] = 'Rubro presupuestario creado correctamente.';
        header('Location: ../views/administrador/ejecucion_presupuestaria.php');
        exit();

    // --- DOCUMENTOS LEGALES ---
    case 'crear_documento_legal':
        $titulo = trim($_POST['titulo'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $fecha_publicacion = trim($_POST['fecha_publicacion'] ?? '');

        if (empty($titulo) || empty($categoria) || empty($fecha_publicacion)) {
            $_SESSION['flash_error'] = 'Por favor complete todos los campos obligatorios.';
            header('Location: ../views/administrador/documentacion_legal.php');
            exit();
        }

        $archivo_url = '';
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['archivo']['tmp_name'];
            $fileOriginal = $_FILES['archivo']['name'];
            $fileSize = $_FILES['archivo']['size'];
            $fileExt = strtolower(pathinfo($fileOriginal, PATHINFO_EXTENSION));

            $allowedExts = ['pdf','doc','docx','xls','xlsx','png','jpg','jpeg','gif'];
            $maxSize = 10 * 1024 * 1024; // 10MB

            if (!in_array($fileExt, $allowedExts)) {
                $_SESSION['flash_error'] = 'Formato de archivo no permitido. Use: PDF, Word, Excel o imagenes.';
                header('Location: ../views/administrador/documentacion_legal.php');
                exit();
            }
            if ($fileSize > $maxSize) {
                $_SESSION['flash_error'] = 'El archivo supera el limite de 10MB.';
                header('Location: ../views/administrador/documentacion_legal.php');
                exit();
            }

            $uploadDir = __DIR__ . '/../public/uploads/documentos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $nombreArchivo = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
            $destino = $uploadDir . $nombreArchivo;

            if (move_uploaded_file($fileTmp, $destino)) {
                $archivo_url = 'public/uploads/documentos/' . $nombreArchivo;
            } else {
                $_SESSION['flash_error'] = 'Error al subir el archivo. Intente de nuevo.';
                header('Location: ../views/administrador/documentacion_legal.php');
                exit();
            }
        } else {
            $_SESSION['flash_error'] = 'Debe seleccionar un archivo para subir.';
            header('Location: ../views/administrador/documentacion_legal.php');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO documentos_directiva (titulo, categoria, archivo_url, fecha_publicacion) VALUES (:titulo, :categoria, :archivo_url, :fecha_publicacion)");
        $stmt->execute([
            ':titulo' => $titulo,
            ':categoria' => $categoria,
            ':archivo_url' => $archivo_url,
            ':fecha_publicacion' => $fecha_publicacion
        ]);

        $_SESSION['flash_success'] = 'Documento legal subido y registrado correctamente.';
        header('Location: ../views/administrador/documentacion_legal.php');
        exit();

    // --- ENCUESTAS ---
    case 'crear_encuesta':
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $opciones = $_POST['opciones'] ?? [];
        $fecha_cierre = trim($_POST['fecha_cierre'] ?? null);

        $opcionesFiltradas = array_filter(array_map('trim', $opciones));

        if (empty($titulo) || count($opcionesFiltradas) < 2) {
            $_SESSION['flash_error'] = 'Ingrese un titulo y al menos 2 opciones.';
            header('Location: ../views/administrador/encuestas.php');
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

        $_SESSION['flash_success'] = 'Encuesta creada correctamente.';
        header('Location: ../views/administrador/encuestas.php');
        exit();

    case 'toggle_encuesta':
        $id_encuesta = (int)($_POST['id_encuesta'] ?? 0);
        $nuevo_estado = (int)($_POST['nuevo_estado'] ?? 0);

        if ($id_encuesta > 0) {
            $stmt = $db->prepare("UPDATE encuestas SET activa = :activa WHERE id = :id");
            $stmt->execute([':activa' => $nuevo_estado, ':id' => $id_encuesta]);
            $_SESSION['flash_success'] = $nuevo_estado ? 'Encuesta activada.' : 'Encuesta cerrada.';
        }
        header('Location: ../views/administrador/encuestas.php');
        exit();

    case 'eliminar_encuesta':
        $id_encuesta = (int)($_POST['id_encuesta'] ?? 0);
        if ($id_encuesta > 0) {
            $db->prepare("DELETE FROM encuestas_votos WHERE id_encuesta = :id")->execute([':id' => $id_encuesta]);
            $db->prepare("DELETE FROM encuestas WHERE id = :id")->execute([':id' => $id_encuesta]);
            $_SESSION['flash_success'] = 'Encuesta eliminada.';
        }
        header('Location: ../views/administrador/encuestas.php');
        exit();

    // --- ELIMINAR RECAUDACION ---
    case 'eliminar_recaudacion':
        $id = (int)($_POST['id_pago'] ?? 0);
        if ($id > 0) {
            $db->prepare("DELETE FROM recaudaciones WHERE id_pago = :id")->execute([':id' => $id]);
            $_SESSION['flash_success'] = 'Recaudacion eliminada.';
        }
        header('Location: ../views/administrador/recaudacion.php');
        exit();

    // --- ELIMINAR PROVEEDOR ---
    case 'eliminar_proveedor':
        $id = (int)($_POST['id_proveedor'] ?? 0);
        if ($id > 0) {
            $db->prepare("DELETE FROM proveedores WHERE id_proveedor = :id")->execute([':id' => $id]);
            $_SESSION['flash_success'] = 'Proveedor eliminado.';
        }
        header('Location: ../views/administrador/proveedores.php');
        exit();

    // --- ELIMINAR PRESUPUESTO ---
    case 'eliminar_presupuesto':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->prepare("DELETE FROM presupuesto WHERE id_presupuesto = :id")->execute([':id' => $id]);
            $_SESSION['flash_success'] = 'Rubro eliminado.';
        }
        header('Location: ../views/administrador/ejecucion_presupuestaria.php');
        exit();

    // --- ELIMINAR DOCUMENTO LEGAL ---
    case 'eliminar_documento_legal':
        $id = (int)($_POST['id_documento'] ?? 0);
        if ($id > 0) {
            $doc = $db->prepare("SELECT archivo_url FROM documentos_directiva WHERE id = :id");
            $doc->execute([':id' => $id]);
            $docData = $doc->fetch(PDO::FETCH_ASSOC);
            if ($docData && !empty($docData['archivo_url'])) {
                $file = __DIR__ . '/../' . $docData['archivo_url'];
                if (file_exists($file)) unlink($file);
            }
            $db->prepare("DELETE FROM documentos_directiva WHERE id = :id")->execute([':id' => $id]);
            $_SESSION['flash_success'] = 'Documento eliminado.';
        }
        header('Location: ../views/administrador/documentacion_legal.php');
        exit();

    // --- ELIMINAR CONVENIO ---
    case 'eliminar_convenio':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->prepare("DELETE FROM convenios WHERE id = :id")->execute([':id' => $id]);
            $_SESSION['flash_success'] = 'Convenio eliminado.';
        }
        header('Location: ../views/administrador/convenios.php');
        exit();

    // --- ELIMINAR TRAMITE ---
    case 'eliminar_tramite':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->prepare("DELETE FROM tramites WHERE id = :id")->execute([':id' => $id]);
            $_SESSION['flash_success'] = 'Tramite eliminado.';
        }
        header('Location: ../views/administrador/tramites.php');
        exit();

    // --- ESPACIOS ---
    case 'crear_espacio':
        $nombre = trim($_POST['nombre'] ?? '');
        if (empty($nombre)) {
            $_SESSION['flash_error'] = 'Ingrese el nombre del espacio.';
            header('Location: ../views/administrador/espacios.php');
            exit();
        }
        $db->prepare("INSERT INTO espacios (nombre, activo) VALUES (:nombre, 1)")->execute([':nombre' => $nombre]);
        $_SESSION['flash_success'] = 'Espacio creado.';
        header('Location: ../views/administrador/espacios.php');
        exit();

    case 'toggle_espacio':
        $id = (int)($_POST['id_espacio'] ?? 0);
        $nuevo = (int)($_POST['nuevo_estado'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE espacios SET activo = :activo WHERE id = :id")->execute([':activo' => $nuevo, ':id' => $id]);
            $_SESSION['flash_success'] = $nuevo ? 'Espacio activado.' : 'Espacio desactivado.';
        }
        header('Location: ../views/administrador/espacios.php');
        exit();

    case 'eliminar_espacio':
        $id = (int)($_POST['id_espacio'] ?? 0);
        if ($id > 0) {
            $db->prepare("DELETE FROM espacios WHERE id = :id")->execute([':id' => $id]);
            $_SESSION['flash_success'] = 'Espacio eliminado.';
        }
        header('Location: ../views/administrador/espacios.php');
        exit();

    default:
        header('Location: ../views/administrador/comunicados.php');
        exit();
}
