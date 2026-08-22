<?php
session_start();
require_once __DIR__ . '/../config/auth_middleware.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/Validador.php';
require_once __DIR__ . '/../models/Notificacion.php';
require_once __DIR__ . '/../models/Logger.php';

verificarRol(['ADMINISTRADOR']);

$db = Database::obtenerConexion();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/**
 * Auto-reparacion de esquema: agrega columnas/tablas que falten para que
 * el modulo nunca falle con HTTP 500 en produccion.
 */
function asegurarEsquemaProveedores($db) {
    try {
        $col = $db->query("SHOW COLUMNS FROM activos LIKE 'costo_aproximado'")->fetch();
        if (!$col) {
            $db->exec("ALTER TABLE activos ADD COLUMN costo_aproximado DECIMAL(10,2) DEFAULT NULL");
        }
    } catch (PDOException $e) { error_log('[esquema activos] ' . $e->getMessage()); }

    // Proveedores: telefono y email separados (migra desde contacto si hace falta)
    try {
        $col = $db->query("SHOW COLUMNS FROM proveedores LIKE 'telefono'")->fetch();
        if (!$col) {
            $db->exec("ALTER TABLE proveedores ADD COLUMN telefono VARCHAR(50) DEFAULT NULL");
            $db->exec("UPDATE proveedores SET telefono = contacto WHERE (telefono IS NULL OR telefono = '') AND contacto IS NOT NULL AND contacto != ''");
        }
        $col = $db->query("SHOW COLUMNS FROM proveedores LIKE 'email'")->fetch();
        if (!$col) {
            $db->exec("ALTER TABLE proveedores ADD COLUMN email VARCHAR(100) DEFAULT NULL");
        }
    } catch (PDOException $e) { error_log('[esquema proveedores tel] ' . $e->getMessage()); }

    try {
        $db->exec("CREATE TABLE IF NOT EXISTS contratos (
            id_contrato INT AUTO_INCREMENT PRIMARY KEY,
            id_proveedor INT NOT NULL,
            servicio VARCHAR(200) NOT NULL,
            fecha_inicio DATE NOT NULL,
            fecha_fin DATE DEFAULT NULL,
            monto DECIMAL(10,2) NOT NULL,
            tipo_monto ENUM('MENSUAL','TOTAL') DEFAULT 'TOTAL',
            documento_pdf VARCHAR(255) DEFAULT NULL,
            estado_orden ENUM('PENDIENTE_ACTA','LISTO_PAGO','PAGADO') DEFAULT 'PENDIENTE_ACTA',
            estado ENUM('VIGENTE','FINALIZADO','CANCELADO') DEFAULT 'VIGENTE',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->exec("CREATE TABLE IF NOT EXISTS actas_recepcion (
            id_acta INT AUTO_INCREMENT PRIMARY KEY,
            id_contrato INT NOT NULL,
            conforme TINYINT(1) NOT NULL DEFAULT 1,
            detalle TEXT DEFAULT NULL,
            recibido_por VARCHAR(150) NOT NULL,
            fecha_acta TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->exec("CREATE TABLE IF NOT EXISTS pagos_proveedores (
            id_pago_prov INT AUTO_INCREMENT PRIMARY KEY,
            id_contrato INT NOT NULL,
            numero_factura VARCHAR(50) NOT NULL,
            metodo_pago ENUM('EFECTIVO','TRANSFERENCIA','CHEQUE') NOT NULL,
            cuenta_origen VARCHAR(100) DEFAULT NULL,
            monto_pagado DECIMAL(10,2) NOT NULL,
            fecha_pago TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (PDOException $e) { error_log('[esquema proveedores] ' . $e->getMessage()); }
}
asegurarEsquemaProveedores($db);

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
            try {
                $q = $db->prepare("SELECT nombre FROM activos WHERE id = :id");
                $q->execute([':id' => $id]);
                $nombreActivo = (string)$q->fetchColumn();

                $stmt = $db->prepare("DELETE FROM activos WHERE id = :id");
                $stmt->execute([':id' => $id]);
                Logger::eliminacion('Activos', "Activo \"{$nombreActivo}\" (#{$id})");
            } catch (PDOException $e) { /* noop */ }
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
            // Obtiene el dueno antes de actualizar
            $stmtOwner = $db->prepare("SELECT id_usuario, tipo FROM incidencias WHERE id = :id");
            $stmtOwner->execute([':id' => $id]);
            $incidencia = $stmtOwner->fetch(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("UPDATE incidencias SET estado = :estado WHERE id = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);

            // Notifica al residente que reporto la incidencia
            if ($incidencia) {
                $texto = $nuevo_estado === 'RESUELTO'
                    ? "Tu reporte \"{$incidencia['tipo']}\" fue resuelto."
                    : "Tu reporte \"{$incidencia['tipo']}\" está en revisión.";
                Notificacion::enviar(
                    (int)$incidencia['id_usuario'],
                    'INCIDENCIA',
                    $nuevo_estado === 'RESUELTO' ? 'Tu reporte fue resuelto' : 'Tu reporte está en revisión',
                    $texto,
                    $id,
                    'incidencia'
                );
            }
        }
        $_SESSION['flash_success'] = 'Estado de la incidencia actualizado.';
        header('Location: ../views/administrador/incidencias.php');
        exit();

    // --- RECAUDACIONES ---
    case 'crear_recaudacion':
        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $concepto = trim($_POST['concepto'] ?? '');
        $montoTexto = trim($_POST['monto'] ?? '');
        $fecha_pago = trim($_POST['fecha_pago'] ?? '');
        $observacion = trim($_POST['observacion'] ?? '');

        $_SESSION['form_old'] = [
            'id_usuario' => $id_usuario,
            'concepto' => $concepto,
            'monto' => $montoTexto,
            'fecha_pago' => $fecha_pago,
            'observacion' => $observacion,
        ];

        // Validaciones con mensajes especificos (el monto usa regex estricto de dinero)
        $errorValidacion = Validador::primerError([
            'residente' => $id_usuario > 0 ? null : 'Seleccione un residente de la lista.',
            'concepto'  => Validador::texto($concepto, 'El concepto', 1, 150),
            'monto'     => Validador::dinero($montoTexto, true),
            'fecha_pago'=> Validador::fecha($fecha_pago),
            'observacion' => $observacion === '' ? null : Validador::texto($observacion, 'La observacion', 1, 255),
        ]);

        if ($errorValidacion !== null) {
            $_SESSION['flash_error'] = $errorValidacion;
            header('Location: ../views/administrador/recaudacion.php');
            exit();
        }
        $monto = (float)$montoTexto;

        try {
            $stmt = $db->prepare("INSERT INTO recaudaciones (id_usuario, concepto, monto, fecha_pago, observacion, estado_pago, fecha_registro) VALUES (:id_usuario, :concepto, :monto, :fecha_pago, :observacion, 'PENDIENTE', NOW())");
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':concepto' => $concepto,
                ':monto' => $monto,
                ':fecha_pago' => $fecha_pago,
                ':observacion' => $observacion
            ]);
            $idRecaudacion = (int)$db->lastInsertId();

            unset($_SESSION['form_old']);
            $_SESSION['flash_success'] = 'Recaudación registrada correctamente.';

            // Notifica al residente sobre el nuevo cobro
            Notificacion::enviar(
                $id_usuario,
                'PAGO',
                'Nuevo cobro registrado',
                "La administración registró el cobro \"{$concepto}\" por $" . number_format($monto, 2) . " con fecha {$fecha_pago}.",
                $idRecaudacion,
                'pago'
            );
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Error al registrar la recaudación.';
        }

        header('Location: ../views/administrador/recaudacion.php');
        exit();

    case 'generar_cuotas_mensuales':
        $montoTexto = trim($_POST['monto'] ?? '');
        $mes = (int)($_POST['mes'] ?? 0);
        $anio = (int)($_POST['anio'] ?? 0);
        $fecha_pago = trim($_POST['fecha_pago'] ?? '');

        $errorCuota = Validador::primerError([
            'monto' => Validador::dinero($montoTexto, true),
            'mes'   => ($mes >= 1 && $mes <= 12) ? null : 'Seleccione un mes válido.',
            'anio'  => ($anio >= 2020 && $anio <= 2100) ? null : 'Ingrese un año válido (2020 - 2100).',
            'fecha' => Validador::fecha($fecha_pago),
        ]);
        if ($errorCuota !== null) {
            $_SESSION['flash_error'] = $errorCuota;
            header('Location: ../views/administrador/recaudacion.php');
            exit();
        }
        $monto = (float)$montoTexto;

        $nombresMeses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $concepto = 'Alícuota ordinaria ' . $nombresMeses[$mes] . ' ' . $anio;

        try {
            // Residentes activos (no bloqueados)
            $stmtResidentes = $db->query("SELECT id_usuario FROM usuarios WHERE rol = 'RESIDENTE' AND (estado IS NULL OR estado <> 'BLOQUEADO')");
            $residentes = $stmtResidentes->fetchAll(PDO::FETCH_ASSOC);

            $stmtExiste = $db->prepare("SELECT COUNT(*) FROM recaudaciones WHERE id_usuario = :u AND concepto = :c");
            $insertar = $db->prepare("INSERT INTO recaudaciones (id_usuario, concepto, monto, fecha_pago, observacion, estado_pago, fecha_registro) VALUES (:u, :c, :m, :f, :o, 'PENDIENTE', NOW())");

            $creados = 0;
            $omitidos = 0;
            foreach ($residentes as $res) {
                $stmtExiste->execute([':u' => $res['id_usuario'], ':c' => $concepto]);
                if ((int)$stmtExiste->fetchColumn() > 0) {
                    $omitidos++;
                    continue;
                }
                $insertar->execute([
                    ':u' => $res['id_usuario'],
                    ':c' => $concepto,
                    ':m' => $monto,
                    ':f' => $fecha_pago,
                    ':o' => 'Cobro generado automáticamente para ' . $nombresMeses[$mes] . ' ' . $anio,
                ]);
                $creados++;
                Notificacion::enviar(
                    (int)$res['id_usuario'],
                    'PAGO',
                    'Nueva alícuota generada',
                    "Se generó el cobro \"{$concepto}\" por $" . number_format($monto, 2) . " con fecha límite {$fecha_pago}.",
                    (int)$db->lastInsertId(),
                    'pago'
                );
            }

            require_once __DIR__ . '/../models/Logger.php';
            Logger::registrar('CREAR', 'Recaudaciones', "{$concepto}: {$creados} cobro(s) generado(s), {$omitidos} omitido(s) por duplicado");

            if ($creados > 0) {
                $_SESSION['flash_cuotas'] = "Se generaron {$creados} cobro(s): \"{$concepto}\"." . ($omitidos > 0 ? " Se omitieron {$omitidos} residente(s) que ya lo tenían." : '');
                $_SESSION['flash_success'] = "Se generaron {$creados} cobro(s) mensual(es) correctamente.";
            } else {
                $_SESSION['flash_cuotas'] = "No se generó ningún cobro: todos los residentes ya tienen \"{$concepto}\".";
            }
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Error al generar los cobros mensuales.';
        }

        header('Location: ../views/administrador/recaudacion.php');
        exit();

    case 'cambiar_estado_recaudacion':
        $id_pago = (int)($_POST['id_pago'] ?? 0);
        $nuevo_estado = $_POST['nuevo_estado'] ?? '';

        if ($id_pago > 0 && in_array($nuevo_estado, ['APROBADO', 'RECHAZADO'])) {
            // Obtiene el dueno antes de actualizar
            $stmtOwner = $db->prepare("SELECT id_usuario, concepto, monto FROM recaudaciones WHERE id_pago = :id");
            $stmtOwner->execute([':id' => $id_pago]);
            $recaudacion = $stmtOwner->fetch(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("UPDATE recaudaciones SET estado_pago = :estado WHERE id_pago = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_pago]);
            $_SESSION['flash_success'] = 'Estado de la recaudación actualizado.';

            // Notifica al residente dueno de la recaudacion
            if ($recaudacion) {
                Notificacion::enviar(
                    (int)$recaudacion['id_usuario'],
                    'PAGO',
                    $nuevo_estado === 'APROBADO' ? 'Tu pago fue aprobado' : 'Tu pago fue rechazado',
                    "El pago \"{$recaudacion['concepto']}\" por $" . number_format((float)$recaudacion['monto'], 2) . " fue " . ($nuevo_estado === 'APROBADO' ? 'aprobado' : 'rechazado') . " por la administración.",
                    $id_pago,
                    'pago'
                );
            }
        } else {
            $_SESSION['flash_error'] = 'Acción no válida.';
        }
        header('Location: ../views/administrador/recaudacion.php');
        exit();

    // --- PROVEEDORES ---
    case 'crear_proveedor':
        $nombre_empresa = trim($_POST['nombre_empresa'] ?? '');
        $servicio_rubro = trim($_POST['servicio_rubro'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $estado_pago = trim($_POST['estado_pago'] ?? 'PENDIENTE');

        if (empty($nombre_empresa) || empty($servicio_rubro)) {
            $_SESSION['flash_error'] = 'Por favor complete el nombre y el servicio del proveedor.';
            header('Location: ../views/administrador/proveedores.php');
            exit();
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'El correo electrónico no es válido.';
            header('Location: ../views/administrador/proveedores.php');
            exit();
        }

        // Evita registrar dos veces el mismo proveedor (mismo nombre de empresa)
        $stmtDupProv = $db->prepare("SELECT id_proveedor FROM proveedores WHERE LOWER(TRIM(nombre_empresa)) = LOWER(:nombre) LIMIT 1");
        $stmtDupProv->execute([':nombre' => $nombre_empresa]);
        if ($stmtDupProv->fetchColumn()) {
            $_SESSION['flash_error'] = "Ya existe un proveedor registrado con el nombre \"{$nombre_empresa}\".";
            header('Location: ../views/administrador/proveedores.php');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO proveedores (nombre_empresa, servicio_rubro, telefono, email, monto_contrato, estado_pago, created_at) VALUES (:nombre_empresa, :servicio_rubro, :telefono, :email, 0, :estado_pago, NOW())");
        $stmt->execute([
            ':nombre_empresa' => $nombre_empresa,
            ':servicio_rubro' => $servicio_rubro,
            ':telefono'       => $telefono,
            ':email'          => $email,
            ':estado_pago'    => $estado_pago
        ]);

        $_SESSION['flash_success'] = 'Proveedor registrado correctamente.';

        // Notifica a la directiva sobre la nueva contratacion
        Notificacion::enviarRol(
            'DIRECTIVA',
            'PROVEEDOR',
            'Nuevo proveedor contratado',
            "La administración contrató a \"{$nombre_empresa}\" ({$servicio_rubro}) por un monto de $" . number_format($monto_contrato, 2) . ".",
            (int)$db->lastInsertId(),
            'proveedor'
        );

        header('Location: ../views/administrador/proveedores.php');
        exit();

    case 'cambiar_estado_proveedor':
        $id_proveedor = (int)($_POST['id_proveedor'] ?? 0);
        $nuevo_estado = $_POST['nuevo_estado'] ?? '';

        if ($id_proveedor > 0 && in_array($nuevo_estado, ['AL_DIA', 'PENDIENTE', 'EN_PROCESO'])) {
            $stmt = $db->prepare("UPDATE proveedores SET estado_pago = :estado WHERE id_proveedor = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_proveedor]);
            $_SESSION['flash_success'] = 'Estado del proveedor actualizado.';

            // Notifica a la directiva el cambio de estado de pago
            $nombreEmpresa = $db->prepare("SELECT nombre_empresa FROM proveedores WHERE id_proveedor = :id");
            $nombreEmpresa->execute([':id' => $id_proveedor]);
            $empresa = (string)$nombreEmpresa->fetchColumn();
            $etiqueta = ['AL_DIA' => 'AL DIA', 'PENDIENTE' => 'PENDIENTE', 'EN_PROCESO' => 'EN PROCESO'][$nuevo_estado] ?? $nuevo_estado;
            Notificacion::enviarRol(
                'DIRECTIVA',
                'PROVEEDOR',
                'Estado de pago de proveedor actualizado',
                "El estado de pago de \"{$empresa}\" ahora es {$etiqueta}.",
                $id_proveedor,
                'proveedor'
            );
        } else {
            $_SESSION['flash_error'] = 'Acción no válida.';
        }
        header('Location: ../views/administrador/proveedores.php');
        exit();

    // --- CONTRATOS CON PROVEEDORES ---
    case 'crear_contrato':
        $id_proveedor = (int)($_POST['id_proveedor'] ?? 0);
        $servicio = trim($_POST['servicio'] ?? '');
        $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
        $fecha_fin = trim($_POST['fecha_fin'] ?? '') ?: null;
        $monto = (float)($_POST['monto'] ?? 0);
        $tipo_monto = ($_POST['tipo_monto'] ?? 'TOTAL') === 'MENSUAL' ? 'MENSUAL' : 'TOTAL';

        if ($id_proveedor <= 0 || empty($servicio) || empty($fecha_inicio) || $monto <= 0) {
            $_SESSION['flash_error'] = 'Complete proveedor, servicio, fecha de inicio y monto.';
            header('Location: ../views/administrador/proveedores.php');
            exit();
        }

        // Evita contratos duplicados: mismo proveedor + mismo servicio
        $stmtDupContrato = $db->prepare("SELECT id_contrato FROM contratos WHERE id_proveedor = :p AND LOWER(TRIM(servicio)) = LOWER(:s) LIMIT 1");
        $stmtDupContrato->execute([':p' => $id_proveedor, ':s' => $servicio]);
        if ($stmtDupContrato->fetchColumn()) {
            $_SESSION['flash_error'] = "Ya existe un contrato con el servicio \"{$servicio}\" para ese proveedor.";
            header('Location: ../views/administrador/proveedores.php');
            exit();
        }

        $documento_pdf = null;
        if (isset($_FILES['documento_pdf']) && $_FILES['documento_pdf']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['documento_pdf']['name'], PATHINFO_EXTENSION));
            if ($ext === 'pdf' && $_FILES['documento_pdf']['size'] <= 10 * 1024 * 1024) {
                $dirContratos = __DIR__ . '/../public/uploads/contratos/';
                if (!is_dir($dirContratos)) { mkdir($dirContratos, 0755, true); }
                $nombrePdf = 'contrato_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
                if (move_uploaded_file($_FILES['documento_pdf']['tmp_name'], $dirContratos . $nombrePdf)) {
                    $documento_pdf = 'public/uploads/contratos/' . $nombrePdf;
                }
            } else {
                $_SESSION['flash_error'] = 'El documento debe ser un PDF de máximo 10MB.';
                header('Location: ../views/administrador/proveedores.php');
                exit();
            }
        }

        $stmt = $db->prepare("INSERT INTO contratos (id_proveedor, servicio, fecha_inicio, fecha_fin, monto, tipo_monto, documento_pdf) VALUES (:p, :s, :fi, :ff, :m, :tm, :pdf)");
        $stmt->execute([':p' => $id_proveedor, ':s' => $servicio, ':fi' => $fecha_inicio, ':ff' => $fecha_fin, ':m' => $monto, ':tm' => $tipo_monto, ':pdf' => $documento_pdf]);
        $idContrato = (int)$db->lastInsertId();

        $nomProv = $db->prepare("SELECT nombre_empresa FROM proveedores WHERE id_proveedor = :id");
        $nomProv->execute([':id' => $id_proveedor]);
        $empresa = (string)$nomProv->fetchColumn();

        $_SESSION['flash_success'] = 'Contrato registrado correctamente. Pendiente de Acta de Recepción.';

        Notificacion::enviarRol('DIRECTIVA', 'PROVEEDOR', 'Nuevo contrato registrado',
            "Se registró el contrato con \"{$empresa}\" ({$servicio}) por $" . number_format($monto, 2) . ". Pendiente de acta de recepción.",
            $idContrato, 'proveedor');

        header('Location: ../views/administrador/proveedores.php');
        exit();

    case 'registrar_acta':
        $id_contrato = (int)($_POST['id_contrato'] ?? 0);
        $conforme = isset($_POST['conforme']) ? 1 : 0;
        $detalle = trim($_POST['detalle'] ?? '');
        $recibido_por = trim($_POST['recibido_por'] ?? '');

        if ($id_contrato <= 0 || empty($recibido_por)) {
            $_SESSION['flash_error'] = 'Seleccione el contrato e indique quién recibió.';
            header('Location: ../views/administrador/proveedores.php');
            exit();
        }

        $q = $db->prepare("SELECT estado_orden FROM contratos WHERE id_contrato = :id");
        $q->execute([':id' => $id_contrato]);
        if ($q->fetchColumn() !== 'PENDIENTE_ACTA') {
            $_SESSION['flash_error'] = 'Ese contrato ya tiene acta o pago registrados.';
            header('Location: ../views/administrador/proveedores.php');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO actas_recepcion (id_contrato, conforme, detalle, recibido_por) VALUES (:c, :cf, :d, :r)");
        $stmt->execute([':c' => $id_contrato, ':cf' => $conforme, ':d' => $detalle, ':r' => $recibido_por]);

        if ($conforme) {
            $db->prepare("UPDATE contratos SET estado_orden = 'LISTO_PAGO' WHERE id_contrato = :id")->execute([':id' => $id_contrato]);
            $_SESSION['flash_success'] = 'Acta de recepción conforme. El contrato está LISTO PARA PAGO.';
            Notificacion::enviarRol('DIRECTIVA', 'PROVEEDOR', 'Acta de recepción conforme',
                "El contrato #{$id_contrato} fue recibido conforme por {$recibido_por}. Listo para registrar el pago." . ($detalle !== '' ? " Detalle: {$detalle}" : ''),
                $id_contrato, 'proveedor');
        } else {
            $_SESSION['flash_warning'] = 'Acta NO conforme registrada. El contrato queda observado y no puede pagarse.';
            Notificacion::enviarRol('DIRECTIVA', 'PROVEEDOR', 'Acta de recepción NO conforme',
                "El contrato #{$id_contrato} fue observado por {$recibido_por}." . ($detalle !== '' ? " Motivo: {$detalle}" : ''),
                $id_contrato, 'proveedor');
        }

        header('Location: ../views/administrador/proveedores.php');
        exit();

    case 'registrar_pago_proveedor':
        $id_contrato = (int)($_POST['id_contrato'] ?? 0);
        $numero_factura = trim($_POST['numero_factura'] ?? '');
        $metodo_pago = in_array($_POST['metodo_pago'] ?? '', ['EFECTIVO', 'TRANSFERENCIA', 'CHEQUE']) ? $_POST['metodo_pago'] : '';
        $cuenta_origen = trim($_POST['cuenta_origen'] ?? '');
        $monto_pagado = (float)($_POST['monto_pagado'] ?? 0);

        if ($id_contrato <= 0 || empty($metodo_pago) || $monto_pagado <= 0) {
            $_SESSION['flash_error'] = 'Complete el método de pago y el monto.';
            header('Location: ../views/administrador/proveedores.php');
            exit();
        }

        $q = $db->prepare("SELECT estado_orden FROM contratos WHERE id_contrato = :id");
        $q->execute([':id' => $id_contrato]);
        if ($q->fetchColumn() !== 'LISTO_PAGO') {
            $_SESSION['flash_error'] = 'Solo se pueden pagar contratos con Acta de Recepción conforme (LISTO PARA PAGO).';
            header('Location: ../views/administrador/proveedores.php');
            exit();
        }

        $stmt = $db->prepare("INSERT INTO pagos_proveedores (id_contrato, numero_factura, metodo_pago, cuenta_origen, monto_pagado) VALUES (:c, :f, :mp, :co, :m)");
        $stmt->execute([':c' => $id_contrato, ':f' => $numero_factura, ':mp' => $metodo_pago, ':co' => $cuenta_origen, ':m' => $monto_pagado]);
        $db->prepare("UPDATE contratos SET estado_orden = 'PAGADO' WHERE id_contrato = :id")->execute([':id' => $id_contrato]);

        $_SESSION['flash_success'] = 'Pago registrado correctamente.';

        Notificacion::enviarRol('DIRECTIVA', 'PROVEEDOR', 'Pago a proveedor registrado',
            "Se registró el pago del contrato #{$id_contrato}: factura {$numero_factura} por $" . number_format($monto_pagado, 2) . " ({$metodo_pago}).",
            $id_contrato, 'proveedor');

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
            try {
                $q = $db->prepare("SELECT pregunta FROM encuestas WHERE id = :id");
                $q->execute([':id' => $id_encuesta]);
                $pregunta = (string)$q->fetchColumn();

                $db->prepare("DELETE FROM encuestas_votos WHERE id_encuesta = :id")->execute([':id' => $id_encuesta]);
                $db->prepare("DELETE FROM encuestas WHERE id = :id")->execute([':id' => $id_encuesta]);
                Logger::eliminacion('Encuestas', "Encuesta #{$id_encuesta} \"{$pregunta}\"");
                $_SESSION['flash_success'] = 'Encuesta eliminada.';
            } catch (PDOException $e) { /* noop */ }
        }
        header('Location: ../views/administrador/encuestas.php');
        exit();

    // --- ELIMINAR RECAUDACION ---
    case 'eliminar_recaudacion':
        $id = (int)($_POST['id_pago'] ?? 0);
        if ($id > 0) {
            try {
                $q = $db->prepare("SELECT concepto, monto, fecha_pago FROM recaudaciones WHERE id_pago = :id");
                $q->execute([':id' => $id]);
                $datos = $q->fetch(PDO::FETCH_ASSOC);
                $detalle = $datos
                    ? "Recaudación #{$id} \"{$datos['concepto']}\" (\$" . number_format((float)$datos['monto'], 2) . ", fecha {$datos['fecha_pago']})"
                    : "Recaudación #{$id}";

                $db->prepare("DELETE FROM recaudaciones WHERE id_pago = :id")->execute([':id' => $id]);
                Logger::eliminacion('Recaudaciones', $detalle);
                $_SESSION['flash_success'] = 'Recaudación eliminada.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Error al eliminar la recaudación.';
            }
        }
        header('Location: ../views/administrador/recaudacion.php');
        exit();

    // --- ELIMINAR PROVEEDOR ---
    case 'eliminar_proveedor':
        $id = (int)($_POST['id_proveedor'] ?? 0);
        if ($id > 0) {
            try {
                $q = $db->prepare("SELECT nombre_empresa FROM proveedores WHERE id_proveedor = :id");
                $q->execute([':id' => $id]);
                $nombreProv = (string)$q->fetchColumn();

                $db->prepare("DELETE FROM proveedores WHERE id_proveedor = :id")->execute([':id' => $id]);
                Logger::eliminacion('Proveedores', "Proveedor \"{$nombreProv}\" (#{$id})");
                $_SESSION['flash_success'] = 'Proveedor eliminado.';
            } catch (PDOException $e) {
                // Si hay contratos asociados la BD puede bloquear el borrado por FK
                $_SESSION['flash_error'] = 'No se pudo eliminar: puede tener contratos o pagos asociados.';
            }
        }
        header('Location: ../views/administrador/proveedores.php');
        exit();

    // --- EDITAR PRESUPUESTO ---
    case 'editar_presupuesto':
        $id = (int)($_POST['id'] ?? 0);
        $rubro = trim($_POST['rubro'] ?? '');
        $monto_asignado = (float)($_POST['monto_asignado'] ?? 0);
        $periodo = trim($_POST['periodo'] ?? '');

        if ($id <= 0 || empty($rubro) || $monto_asignado <= 0 || empty($periodo)) {
            $_SESSION['flash_error'] = 'Por favor complete todos los campos obligatorios.';
            header('Location: ../views/administrador/ejecucion_presupuestaria.php');
            exit();
        }

        $db->prepare("UPDATE presupuesto SET rubro = :rubro, monto_asignado = :monto, periodo = :periodo WHERE id_presupuesto = :id")
           ->execute([':rubro' => $rubro, ':monto' => $monto_asignado, ':periodo' => $periodo, ':id' => $id]);

        $_SESSION['flash_success'] = 'Rubro actualizado correctamente.';
        header('Location: ../views/administrador/ejecucion_presupuestaria.php');
        exit();

    // --- ELIMINAR PRESUPUESTO ---
    case 'eliminar_presupuesto':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $q = $db->prepare("SELECT rubro FROM presupuesto WHERE id_presupuesto = :id");
                $q->execute([':id' => $id]);
                $rubroEliminado = (string)$q->fetchColumn();

                $db->prepare("DELETE FROM presupuesto WHERE id_presupuesto = :id")->execute([':id' => $id]);
                Logger::eliminacion('Presupuestos', "Rubro \"{$rubroEliminado}\" (#{$id})");
                $_SESSION['flash_success'] = 'Rubro eliminado.';
            } catch (PDOException $e) { /* noop */ }
        }
        header('Location: ../views/administrador/ejecucion_presupuestaria.php');
        exit();

    // --- ELIMINAR DOCUMENTO LEGAL ---
    case 'eliminar_documento_legal':
        $id = (int)($_POST['id_documento'] ?? 0);
        if ($id > 0) {
            try {
                $doc = $db->prepare("SELECT archivo_url, titulo FROM documentos_directiva WHERE id = :id");
                $doc->execute([':id' => $id]);
                $docData = $doc->fetch(PDO::FETCH_ASSOC);
                if ($docData && !empty($docData['archivo_url'])) {
                    $file = __DIR__ . '/../' . $docData['archivo_url'];
                    if (file_exists($file)) unlink($file);
                }
                $db->prepare("DELETE FROM documentos_directiva WHERE id = :id")->execute([':id' => $id]);
                Logger::eliminacion('Documentos legales', "Documento #{$id} \"" . ($docData['titulo'] ?? '') . "\"");
                $_SESSION['flash_success'] = 'Documento eliminado.';
            } catch (PDOException $e) { /* noop */ }
        }
        header('Location: ../views/administrador/documentacion_legal.php');
        exit();

    // --- ELIMINAR CONVENIO ---
    case 'eliminar_convenio':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $db->prepare("DELETE FROM convenios WHERE id = :id")->execute([':id' => $id]);
                Logger::eliminacion('Convenios', "Convenio #{$id}");
                $_SESSION['flash_success'] = 'Convenio eliminado.';
            } catch (PDOException $e) { /* noop */ }
        }
        header('Location: ../views/administrador/convenios.php');
        exit();

    // --- ELIMINAR TRAMITE ---
    case 'eliminar_tramite':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $db->prepare("DELETE FROM tramites WHERE id = :id")->execute([':id' => $id]);
                Logger::eliminacion('Tramites', "Tramite #{$id}");
                $_SESSION['flash_success'] = 'Tramite eliminado.';
            } catch (PDOException $e) { /* noop */ }
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
            try {
                $q = $db->prepare("SELECT nombre FROM espacios WHERE id = :id");
                $q->execute([':id' => $id]);
                $nombreEspacio = (string)$q->fetchColumn();

                $db->prepare("DELETE FROM espacios WHERE id = :id")->execute([':id' => $id]);
                Logger::eliminacion('Espacios', "Espacio \"{$nombreEspacio}\" (#{$id})");
                $_SESSION['flash_success'] = 'Espacio eliminado.';
            } catch (PDOException $e) { /* noop */ }
        }
        header('Location: ../views/administrador/espacios.php');
        exit();

    default:
        header('Location: ../views/administrador/comunicados.php');
        exit();
}
