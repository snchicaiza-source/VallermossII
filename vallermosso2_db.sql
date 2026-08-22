-- Vallermosso II - Database Export
-- Fecha: 2026-08-22 05:47:02

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activos`;
CREATE TABLE `activos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `estado` enum('EXCELENTE','BUENO','REGULAR','MALO') DEFAULT 'BUENO',
  `costo_aproximado` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activos` (`id`, `nombre`, `estado`, `costo_aproximado`, `created_at`) VALUES ('1', 'bombas de agua', 'BUENO', NULL, '2026-08-18 21:23:50');

DROP TABLE IF EXISTS `comunicados`;
CREATE TABLE `comunicados` (
  `id_comunicado` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `mensaje` text NOT NULL,
  `canal` enum('EMAIL','WHATSAPP','AMBOS') NOT NULL,
  `enviado_por` int(11) NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_comunicado`),
  KEY `enviado_por` (`enviado_por`),
  CONSTRAINT `comunicados_ibfk_1` FOREIGN KEY (`enviado_por`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comunicados` (`id_comunicado`, `id_usuario`, `titulo`, `mensaje`, `canal`, `enviado_por`, `fecha_envio`) VALUES ('1', NULL, 'mantenimiento', 'se cerraran las calles S y M', 'AMBOS', '1', '2026-08-10 02:15:30');
INSERT INTO `comunicados` (`id_comunicado`, `id_usuario`, `titulo`, `mensaje`, `canal`, `enviado_por`, `fecha_envio`) VALUES ('2', NULL, 'reunion General', 'PRUEBA REUNION', 'EMAIL', '1', '2026-08-18 20:17:25');
INSERT INTO `comunicados` (`id_comunicado`, `id_usuario`, `titulo`, `mensaje`, `canal`, `enviado_por`, `fecha_envio`) VALUES ('3', NULL, 'PRUEBA', '1234', 'AMBOS', '1', '2026-08-18 20:19:51');
INSERT INTO `comunicados` (`id_comunicado`, `id_usuario`, `titulo`, `mensaje`, `canal`, `enviado_por`, `fecha_envio`) VALUES ('4', NULL, 'reunion General', 'nose', 'AMBOS', '2', '2026-08-18 21:28:43');
INSERT INTO `comunicados` (`id_comunicado`, `id_usuario`, `titulo`, `mensaje`, `canal`, `enviado_por`, `fecha_envio`) VALUES ('5', NULL, 'programa listo', 'ya podemos revisar el documento', 'AMBOS', '1', '2026-08-18 22:30:55');
INSERT INTO `comunicados` (`id_comunicado`, `id_usuario`, `titulo`, `mensaje`, `canal`, `enviado_por`, `fecha_envio`) VALUES ('6', NULL, 'iii', 'jj', 'AMBOS', '1', '2026-08-18 23:20:57');
INSERT INTO `comunicados` (`id_comunicado`, `id_usuario`, `titulo`, `mensaje`, `canal`, `enviado_por`, `fecha_envio`) VALUES ('7', NULL, 'uuu', 'uu', 'AMBOS', '1', '2026-08-18 23:26:15');
INSERT INTO `comunicados` (`id_comunicado`, `id_usuario`, `titulo`, `mensaje`, `canal`, `enviado_por`, `fecha_envio`) VALUES ('8', NULL, 'REUNION GENERAL', 'Reunión general a las  8am el Dia 20 de Agosto.', 'AMBOS', '1', '2026-08-19 21:53:23');

DROP TABLE IF EXISTS `convenios`;
CREATE TABLE `convenios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `num_cuotas` int(11) DEFAULT 1,
  `estado` enum('ACTIVO','CUMPLIDO','INCUMPLIDO') DEFAULT 'ACTIVO',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `convenios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `convenios` (`id`, `id_usuario`, `monto_total`, `num_cuotas`, `estado`, `created_at`) VALUES ('2', '3', '44.00', '4', 'ACTIVO', '2026-08-19 22:59:23');

DROP TABLE IF EXISTS `documentos`;
CREATE TABLE `documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `tipo` enum('LEYES','REGLAMENTOS','ACTAS') NOT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `fecha` date DEFAULT curdate(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `documentos_directiva`;
CREATE TABLE `documentos_directiva` (
  `id_documento` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `categoria` enum('LEYES','ACTAS_ASAMBLEA','ACTAS_DIRECTIVA','DECLARATORIA_PH') NOT NULL,
  `archivo_url` varchar(255) NOT NULL,
  `fecha_publicacion` date NOT NULL,
  PRIMARY KEY (`id_documento`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `documentos_directiva` (`id_documento`, `titulo`, `categoria`, `archivo_url`, `fecha_publicacion`) VALUES ('1', 'Ley de Propiedad Horizontal', 'LEYES', '#', '2026-01-15');
INSERT INTO `documentos_directiva` (`id_documento`, `titulo`, `categoria`, `archivo_url`, `fecha_publicacion`) VALUES ('2', 'Reglamento Interno de Convivencia', 'LEYES', '#', '2026-02-01');
INSERT INTO `documentos_directiva` (`id_documento`, `titulo`, `categoria`, `archivo_url`, `fecha_publicacion`) VALUES ('3', 'Acta Asamblea General Ordinaria 2026', 'ACTAS_ASAMBLEA', '#', '2026-06-15');
INSERT INTO `documentos_directiva` (`id_documento`, `titulo`, `categoria`, `archivo_url`, `fecha_publicacion`) VALUES ('4', 'Acta Sesion de Directiva Julio 2026', 'ACTAS_DIRECTIVA', '#', '2026-07-10');
INSERT INTO `documentos_directiva` (`id_documento`, `titulo`, `categoria`, `archivo_url`, `fecha_publicacion`) VALUES ('5', 'Declaratoria de Propiedad Horizontal', 'DECLARATORIA_PH', '#', '2026-01-01');
INSERT INTO `documentos_directiva` (`id_documento`, `titulo`, `categoria`, `archivo_url`, `fecha_publicacion`) VALUES ('6', 'prueba', 'LEYES', 'public/uploads/documentos/doc_1787113518_253fbe1f.docx', '2026-08-19');
INSERT INTO `documentos_directiva` (`id_documento`, `titulo`, `categoria`, `archivo_url`, `fecha_publicacion`) VALUES ('7', 'Convenios', 'ACTAS_ASAMBLEA', 'public/uploads/documentos/doc_1787195467_b70acd08.docx', '2026-08-07');

DROP TABLE IF EXISTS `encuestas`;
CREATE TABLE `encuestas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `opciones` text NOT NULL COMMENT 'JSON array of options',
  `activa` tinyint(1) DEFAULT 1,
  `creada_por` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `encuestas` (`id`, `titulo`, `descripcion`, `opciones`, `activa`, `creada_por`, `fecha_creacion`, `fecha_cierre`) VALUES ('2', 'prioridades', 'rrr', '[\"recoleccion basura\",\"parqueaderos\"]', '1', '1', '2026-08-19 22:16:54', '2026-08-23');

DROP TABLE IF EXISTS `encuestas_votos`;
CREATE TABLE `encuestas_votos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_encuesta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `respuesta` varchar(200) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unica_voto` (`id_encuesta`,`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `encuestas_votos` (`id`, `id_encuesta`, `id_usuario`, `respuesta`, `fecha`) VALUES ('1', '2', '3', 'parqueaderos', '2026-08-19 22:32:07');

DROP TABLE IF EXISTS `espacios`;
CREATE TABLE `espacios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `espacios` (`id`, `nombre`, `activo`, `created_at`) VALUES ('1', 'Salon de Eventos', '1', '2026-08-19 22:41:44');
INSERT INTO `espacios` (`id`, `nombre`, `activo`, `created_at`) VALUES ('2', 'Piscina', '1', '2026-08-19 22:41:44');
INSERT INTO `espacios` (`id`, `nombre`, `activo`, `created_at`) VALUES ('3', 'Salon de Juegos', '1', '2026-08-19 22:41:45');
INSERT INTO `espacios` (`id`, `nombre`, `activo`, `created_at`) VALUES ('4', 'Terraza', '1', '2026-08-19 22:41:45');
INSERT INTO `espacios` (`id`, `nombre`, `activo`, `created_at`) VALUES ('5', 'Parqueadero', '0', '2026-08-19 22:41:45');

DROP TABLE IF EXISTS `incidencias`;
CREATE TABLE `incidencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('DANO','QUEJA','RESERVACION') DEFAULT 'DANO',
  `descripcion` text NOT NULL,
  `estado` enum('PENDIENTE','EN_REVISION','RESUELTO') DEFAULT 'PENDIENTE',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `incidencias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `incidencias` (`id`, `id_usuario`, `tipo`, `descripcion`, `estado`, `fecha`) VALUES ('1', '3', 'QUEJA', 'los vecinos hacen bulla', 'RESUELTO', '2026-08-19 22:32:41');
INSERT INTO `incidencias` (`id`, `id_usuario`, `tipo`, `descripcion`, `estado`, `fecha`) VALUES ('2', '3', '', 'ruido altas horas', 'RESUELTO', '2026-08-19 22:33:46');

DROP TABLE IF EXISTS `notificaciones_log`;
CREATE TABLE `notificaciones_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `canal` enum('EMAIL','WHATSAPP','AMBOS') NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `destinatario_nombre` varchar(150) DEFAULT NULL,
  `destinatario_correo` varchar(100) DEFAULT NULL,
  `destinatario_telefono` varchar(20) DEFAULT NULL,
  `estado` enum('ENVIADO','FALLIDO','PENDIENTE') DEFAULT 'PENDIENTE',
  `error_detalle` text DEFAULT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('1', 'EMAIL', 'reunion General', 'Administrador General', 'admin@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 21:28:43');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('2', 'EMAIL', 'reunion General', 'Presidente Directiva', 'directiva@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 21:28:43');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('3', 'EMAIL', 'reunion General', 'Juan Pérez (Residente)', 'residente@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 21:28:43');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('4', 'EMAIL', 'reunion General', 'Chicaiza Rocha Sonia Maribel', 'snchicaiza@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 21:28:43');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('5', 'EMAIL', 'reunion General', 'Cedeño Josselyn', 'loostefany98@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 21:28:43');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('6', 'WHATSAPP', 'reunion General', 'Administrador General', NULL, '593999999991', 'PENDIENTE', NULL, '2026-08-18 21:28:43');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('7', 'WHATSAPP', 'reunion General', 'Presidente Directiva', NULL, '593999999992', 'PENDIENTE', NULL, '2026-08-18 21:28:43');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('8', 'WHATSAPP', 'reunion General', 'Juan Pérez (Residente)', NULL, '593999999993', 'PENDIENTE', NULL, '2026-08-18 21:28:43');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('9', 'WHATSAPP', 'reunion General', 'Chicaiza Rocha Sonia Maribel', NULL, '0963610976', 'PENDIENTE', NULL, '2026-08-18 21:28:43');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('10', 'WHATSAPP', 'reunion General', 'Cedeño Josselyn', NULL, '0984292142', 'PENDIENTE', NULL, '2026-08-18 21:28:43');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('11', 'EMAIL', 'programa listo', 'Administrador General', 'admin@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 22:30:55');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('12', 'EMAIL', 'programa listo', 'Presidente Directiva', 'directiva@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 22:30:55');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('13', 'EMAIL', 'programa listo', 'Juan Pérez (Residente)', 'residente@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 22:30:55');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('14', 'EMAIL', 'programa listo', 'Chicaiza Rocha Sonia Maribel', 'snchicaiza@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 22:30:55');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('15', 'EMAIL', 'programa listo', 'Cedeño Josselyn', 'loostefany98@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 22:30:55');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('16', 'WHATSAPP', 'programa listo', 'Administrador General', NULL, '593999999991', 'PENDIENTE', NULL, '2026-08-18 22:30:55');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('17', 'WHATSAPP', 'programa listo', 'Presidente Directiva', NULL, '593999999992', 'PENDIENTE', NULL, '2026-08-18 22:30:55');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('18', 'WHATSAPP', 'programa listo', 'Juan Pérez (Residente)', NULL, '593999999993', 'PENDIENTE', NULL, '2026-08-18 22:30:55');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('19', 'WHATSAPP', 'programa listo', 'Chicaiza Rocha Sonia Maribel', NULL, '0963610976', 'PENDIENTE', NULL, '2026-08-18 22:30:55');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('20', 'WHATSAPP', 'programa listo', 'Cedeño Josselyn', NULL, '0984292142', 'PENDIENTE', NULL, '2026-08-18 22:30:55');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('21', 'EMAIL', 'iii', 'Administrador General', 'admin@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 23:20:57');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('22', 'EMAIL', 'iii', 'Presidente Directiva', 'directiva@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 23:20:57');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('23', 'EMAIL', 'iii', 'Juan Pérez (Residente)', 'residente@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 23:20:57');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('24', 'EMAIL', 'iii', 'Chicaiza Rocha Sonia Maribel', 'snchicaiza@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 23:20:57');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('25', 'EMAIL', 'iii', 'Cedeño Josselyn', 'loostefany98@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 23:20:57');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('26', 'WHATSAPP', 'iii', 'Administrador General', NULL, '593999999991', 'PENDIENTE', NULL, '2026-08-18 23:20:57');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('27', 'WHATSAPP', 'iii', 'Presidente Directiva', NULL, '593999999992', 'PENDIENTE', NULL, '2026-08-18 23:20:57');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('28', 'WHATSAPP', 'iii', 'Juan Pérez (Residente)', NULL, '593999999993', 'PENDIENTE', NULL, '2026-08-18 23:20:57');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('29', 'WHATSAPP', 'iii', 'Chicaiza Rocha Sonia Maribel', NULL, '0963610976', 'PENDIENTE', NULL, '2026-08-18 23:20:57');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('30', 'WHATSAPP', 'iii', 'Cedeño Josselyn', NULL, '0984292142', 'PENDIENTE', NULL, '2026-08-18 23:20:57');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('31', 'EMAIL', 'uuu', 'Administrador General', 'admin@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 23:26:15');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('32', 'EMAIL', 'uuu', 'Presidente Directiva', 'directiva@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 23:26:15');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('33', 'EMAIL', 'uuu', 'Juan Pérez (Residente)', 'residente@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 23:26:15');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('34', 'EMAIL', 'uuu', 'Chicaiza Rocha Sonia Maribel', 'snchicaiza@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 23:26:15');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('35', 'EMAIL', 'uuu', 'Cedeño Josselyn', 'loostefany98@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-18 23:26:15');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('36', 'WHATSAPP', 'uuu', 'Administrador General', NULL, '593999999991', 'PENDIENTE', NULL, '2026-08-18 23:26:15');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('37', 'WHATSAPP', 'uuu', 'Presidente Directiva', NULL, '593999999992', 'PENDIENTE', NULL, '2026-08-18 23:26:15');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('38', 'WHATSAPP', 'uuu', 'Juan Pérez (Residente)', NULL, '593999999993', 'PENDIENTE', NULL, '2026-08-18 23:26:15');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('39', 'WHATSAPP', 'uuu', 'Chicaiza Rocha Sonia Maribel', NULL, '0963610976', 'PENDIENTE', NULL, '2026-08-18 23:26:15');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('40', 'WHATSAPP', 'uuu', 'Cedeño Josselyn', NULL, '0984292142', 'PENDIENTE', NULL, '2026-08-18 23:26:15');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('41', 'EMAIL', 'REUNION GENERAL', 'Administrador General', 'admin@vallermosso.com', NULL, 'FALLIDO', 'Sistema de correo deshabilitado. Active mail_config[habilitado].', '2026-08-19 21:53:23');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('42', 'EMAIL', 'REUNION GENERAL', 'Presidente Directiva', 'directiva@vallermosso.com', NULL, 'FALLIDO', 'Sistema de correo deshabilitado. Active mail_config[habilitado].', '2026-08-19 21:53:23');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('43', 'EMAIL', 'REUNION GENERAL', 'Juan Pérez (Residente)', 'residente@vallermosso.com', NULL, 'FALLIDO', 'Sistema de correo deshabilitado. Active mail_config[habilitado].', '2026-08-19 21:53:23');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('44', 'EMAIL', 'REUNION GENERAL', 'Chicaiza Rocha Sonia Maribel', 'snchicaiza@gmail.com', NULL, 'FALLIDO', 'Sistema de correo deshabilitado. Active mail_config[habilitado].', '2026-08-19 21:53:23');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('45', 'EMAIL', 'REUNION GENERAL', 'Cedeño Josselyn', 'loostefany98@gmail.com', NULL, 'FALLIDO', 'Sistema de correo deshabilitado. Active mail_config[habilitado].', '2026-08-19 21:53:23');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('46', 'WHATSAPP', 'REUNION GENERAL', 'Administrador General', NULL, '593999999991', 'PENDIENTE', NULL, '2026-08-19 21:53:23');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('47', 'WHATSAPP', 'REUNION GENERAL', 'Presidente Directiva', NULL, '593999999992', 'PENDIENTE', NULL, '2026-08-19 21:53:23');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('48', 'WHATSAPP', 'REUNION GENERAL', 'Juan Pérez (Residente)', NULL, '593999999993', 'PENDIENTE', NULL, '2026-08-19 21:53:23');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('49', 'WHATSAPP', 'REUNION GENERAL', 'Chicaiza Rocha Sonia Maribel', NULL, '0963610976', 'PENDIENTE', NULL, '2026-08-19 21:53:23');
INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES ('50', 'WHATSAPP', 'REUNION GENERAL', 'Cedeño Josselyn', NULL, '0984292142', 'PENDIENTE', NULL, '2026-08-19 21:53:23');

DROP TABLE IF EXISTS `notificaciones_usuario`;
CREATE TABLE `notificaciones_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'COMUNICADO',
  `titulo` varchar(200) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `referencia_tipo` varchar(50) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario_leida` (`id_usuario`,`leida`),
  CONSTRAINT `notificaciones_usuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('1', '1', 'COMUNICADO', 'uuu', 'uu', '7', 'comunicado', '1', '2026-08-18 23:26:15');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('2', '2', 'COMUNICADO', 'uuu', 'uu', '7', 'comunicado', '0', '2026-08-18 23:26:15');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('3', '3', 'COMUNICADO', 'uuu', 'uu', '7', 'comunicado', '0', '2026-08-18 23:26:15');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('4', '4', 'COMUNICADO', 'uuu', 'uu', '7', 'comunicado', '0', '2026-08-18 23:26:15');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('5', '5', 'COMUNICADO', 'uuu', 'uu', '7', 'comunicado', '0', '2026-08-18 23:26:15');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('6', '1', 'COMUNICADO', 'iii', 'jj', '6', 'comunicado', '1', '2026-08-18 23:20:57');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('7', '2', 'COMUNICADO', 'iii', 'jj', '6', 'comunicado', '0', '2026-08-18 23:20:57');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('8', '3', 'COMUNICADO', 'iii', 'jj', '6', 'comunicado', '0', '2026-08-18 23:20:57');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('9', '4', 'COMUNICADO', 'iii', 'jj', '6', 'comunicado', '0', '2026-08-18 23:20:57');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('10', '5', 'COMUNICADO', 'iii', 'jj', '6', 'comunicado', '0', '2026-08-18 23:20:57');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('11', '1', 'COMUNICADO', 'programa listo', 'ya podemos revisar el documento', '5', 'comunicado', '1', '2026-08-18 22:30:55');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('12', '2', 'COMUNICADO', 'programa listo', 'ya podemos revisar el documento', '5', 'comunicado', '0', '2026-08-18 22:30:55');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('13', '3', 'COMUNICADO', 'programa listo', 'ya podemos revisar el documento', '5', 'comunicado', '0', '2026-08-18 22:30:55');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('14', '4', 'COMUNICADO', 'programa listo', 'ya podemos revisar el documento', '5', 'comunicado', '0', '2026-08-18 22:30:55');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('15', '5', 'COMUNICADO', 'programa listo', 'ya podemos revisar el documento', '5', 'comunicado', '0', '2026-08-18 22:30:55');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('16', '1', 'COMUNICADO', 'reunion General', 'nose', '4', 'comunicado', '1', '2026-08-18 21:28:43');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('17', '2', 'COMUNICADO', 'reunion General', 'nose', '4', 'comunicado', '0', '2026-08-18 21:28:43');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('18', '3', 'COMUNICADO', 'reunion General', 'nose', '4', 'comunicado', '0', '2026-08-18 21:28:43');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('19', '4', 'COMUNICADO', 'reunion General', 'nose', '4', 'comunicado', '0', '2026-08-18 21:28:43');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('20', '5', 'COMUNICADO', 'reunion General', 'nose', '4', 'comunicado', '0', '2026-08-18 21:28:43');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('21', '1', 'COMUNICADO', 'PRUEBA', '1234', '3', 'comunicado', '1', '2026-08-18 20:19:51');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('22', '2', 'COMUNICADO', 'PRUEBA', '1234', '3', 'comunicado', '0', '2026-08-18 20:19:51');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('23', '3', 'COMUNICADO', 'PRUEBA', '1234', '3', 'comunicado', '0', '2026-08-18 20:19:51');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('24', '4', 'COMUNICADO', 'PRUEBA', '1234', '3', 'comunicado', '0', '2026-08-18 20:19:51');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('25', '5', 'COMUNICADO', 'PRUEBA', '1234', '3', 'comunicado', '0', '2026-08-18 20:19:51');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('26', '1', 'COMUNICADO', 'reunion General', 'PRUEBA REUNION', '2', 'comunicado', '1', '2026-08-18 20:17:25');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('27', '2', 'COMUNICADO', 'reunion General', 'PRUEBA REUNION', '2', 'comunicado', '0', '2026-08-18 20:17:25');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('28', '3', 'COMUNICADO', 'reunion General', 'PRUEBA REUNION', '2', 'comunicado', '0', '2026-08-18 20:17:25');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('29', '4', 'COMUNICADO', 'reunion General', 'PRUEBA REUNION', '2', 'comunicado', '0', '2026-08-18 20:17:25');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('30', '5', 'COMUNICADO', 'reunion General', 'PRUEBA REUNION', '2', 'comunicado', '0', '2026-08-18 20:17:25');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('31', '1', 'COMUNICADO', 'mantenimiento', 'se cerraran las calles S y M', '1', 'comunicado', '1', '2026-08-10 02:15:30');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('32', '2', 'COMUNICADO', 'mantenimiento', 'se cerraran las calles S y M', '1', 'comunicado', '0', '2026-08-10 02:15:30');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('33', '3', 'COMUNICADO', 'mantenimiento', 'se cerraran las calles S y M', '1', 'comunicado', '0', '2026-08-10 02:15:30');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('34', '4', 'COMUNICADO', 'mantenimiento', 'se cerraran las calles S y M', '1', 'comunicado', '0', '2026-08-10 02:15:30');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('35', '5', 'COMUNICADO', 'mantenimiento', 'se cerraran las calles S y M', '1', 'comunicado', '0', '2026-08-10 02:15:30');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('36', '1', 'COMUNICADO', 'REUNION GENERAL', 'Reunión general a las  8am el Dia 20 de Agosto.', '8', 'comunicado', '0', '2026-08-19 21:53:23');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('37', '2', 'COMUNICADO', 'REUNION GENERAL', 'Reunión general a las  8am el Dia 20 de Agosto.', '8', 'comunicado', '0', '2026-08-19 21:53:23');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('38', '3', 'COMUNICADO', 'REUNION GENERAL', 'Reunión general a las  8am el Dia 20 de Agosto.', '8', 'comunicado', '0', '2026-08-19 21:53:23');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('39', '4', 'COMUNICADO', 'REUNION GENERAL', 'Reunión general a las  8am el Dia 20 de Agosto.', '8', 'comunicado', '0', '2026-08-19 21:53:23');
INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES ('40', '5', 'COMUNICADO', 'REUNION GENERAL', 'Reunión general a las  8am el Dia 20 de Agosto.', '8', 'comunicado', '0', '2026-08-19 21:53:23');

DROP TABLE IF EXISTS `pagos`;
CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `concepto` varchar(150) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('PENDIENTE','EN_REVISION','PAGADO','RECHAZADO') DEFAULT 'PENDIENTE',
  `comprobante_url` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pago`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pagos` (`id_pago`, `id_usuario`, `concepto`, `monto`, `fecha_vencimiento`, `estado`, `comprobante_url`, `fecha_subida`, `created_at`) VALUES ('1', '2', 'Alícuota Ordinaria - Agosto 2026', '45.00', '2026-08-05', 'PAGADO', NULL, NULL, '2026-08-10 02:21:44');
INSERT INTO `pagos` (`id_pago`, `id_usuario`, `concepto`, `monto`, `fecha_vencimiento`, `estado`, `comprobante_url`, `fecha_subida`, `created_at`) VALUES ('2', '2', 'Alícuota Ordinaria - Julio 2026', '45.00', '2026-07-05', 'PAGADO', NULL, NULL, '2026-08-10 02:21:44');
INSERT INTO `pagos` (`id_pago`, `id_usuario`, `concepto`, `monto`, `fecha_vencimiento`, `estado`, `comprobante_url`, `fecha_subida`, `created_at`) VALUES ('3', '2', 'Cuota Extraordinaria Mantenimiento Portón', '20.00', '2026-06-15', 'PAGADO', NULL, NULL, '2026-08-10 02:21:44');
INSERT INTO `pagos` (`id_pago`, `id_usuario`, `concepto`, `monto`, `fecha_vencimiento`, `estado`, `comprobante_url`, `fecha_subida`, `created_at`) VALUES ('4', '3', 'Alícuota Ordinaria - Agosto 2026', '45.00', '2026-08-05', 'PAGADO', 'public/uploads/comprobantes/voucher_3_4_1786346952.jpeg', '2026-08-10 02:29:12', '2026-08-10 02:28:00');
INSERT INTO `pagos` (`id_pago`, `id_usuario`, `concepto`, `monto`, `fecha_vencimiento`, `estado`, `comprobante_url`, `fecha_subida`, `created_at`) VALUES ('5', '3', 'Alícuota Ordinaria - Julio 2026', '45.00', '2026-07-05', 'PAGADO', NULL, NULL, '2026-08-10 02:28:00');
INSERT INTO `pagos` (`id_pago`, `id_usuario`, `concepto`, `monto`, `fecha_vencimiento`, `estado`, `comprobante_url`, `fecha_subida`, `created_at`) VALUES ('6', '3', 'Cuota Extraordinaria Mantenimiento Portón', '20.00', '2026-06-15', 'PAGADO', NULL, NULL, '2026-08-10 02:28:00');
INSERT INTO `pagos` (`id_pago`, `id_usuario`, `concepto`, `monto`, `fecha_vencimiento`, `estado`, `comprobante_url`, `fecha_subida`, `created_at`) VALUES ('7', '3', 'uu', '88.00', '2026-08-16', 'PAGADO', 'pago_1786940033_465.jpeg', NULL, '2026-08-16 23:13:53');

DROP TABLE IF EXISTS `presupuesto`;
CREATE TABLE `presupuesto` (
  `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT,
  `rubro` varchar(150) NOT NULL,
  `monto_asignado` decimal(10,2) NOT NULL,
  `monto_ejecutado` decimal(10,2) DEFAULT 0.00,
  `periodo` varchar(20) NOT NULL DEFAULT '2026',
  PRIMARY KEY (`id_presupuesto`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `presupuesto` (`id_presupuesto`, `rubro`, `monto_asignado`, `monto_ejecutado`, `periodo`) VALUES ('1', 'Mantenimiento Portón Eléctrico', '1200.00', '450.00', '2026');
INSERT INTO `presupuesto` (`id_presupuesto`, `rubro`, `monto_asignado`, `monto_ejecutado`, `periodo`) VALUES ('2', 'Jardinería y Áreas Verdes', '800.00', '800.00', '2026');
INSERT INTO `presupuesto` (`id_presupuesto`, `rubro`, `monto_asignado`, `monto_ejecutado`, `periodo`) VALUES ('3', 'Seguridad y Monitoreo', '2500.00', '1800.00', '2026');
INSERT INTO `presupuesto` (`id_presupuesto`, `rubro`, `monto_asignado`, `monto_ejecutado`, `periodo`) VALUES ('4', 'recoleccion basura', '120.50', '0.00', '2026');

DROP TABLE IF EXISTS `presupuesto_ejecucion`;
CREATE TABLE `presupuesto_ejecucion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `concepto` varchar(150) NOT NULL,
  `monto_presupuestado` decimal(10,2) NOT NULL,
  `monto_ejecutado` decimal(10,2) DEFAULT 0.00,
  `porcentaje` decimal(5,2) DEFAULT 0.00,
  `periodo` varchar(20) DEFAULT '2026',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_empresa` varchar(150) NOT NULL,
  `servicio_rubro` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `monto_contrato` decimal(10,2) NOT NULL,
  `estado_pago` enum('AL_DIA','PENDIENTE','EN_PROCESO') DEFAULT 'AL_DIA',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `proveedores` (`id_proveedor`, `nombre_empresa`, `servicio_rubro`, `contacto`, `monto_contrato`, `estado_pago`, `created_at`) VALUES ('1', 'Serviseg Cía Ltda', 'Seguridad Física', '0991234567', '600.00', 'AL_DIA', '2026-08-15 21:31:58');
INSERT INTO `proveedores` (`id_proveedor`, `nombre_empresa`, `servicio_rubro`, `contacto`, `monto_contrato`, `estado_pago`, `created_at`) VALUES ('2', 'Jardines del Valle', 'Mantenimiento de Áreas Verdes', '0987654321', '150.00', 'AL_DIA', '2026-08-15 21:31:58');

DROP TABLE IF EXISTS `recaudaciones`;
CREATE TABLE `recaudaciones` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `concepto` varchar(150) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `comprobante_url` varchar(255) DEFAULT NULL,
  `estado_pago` enum('PENDIENTE','APROBADO','RECHAZADO') DEFAULT 'PENDIENTE',
  `observacion` text DEFAULT NULL,
  `fecha_pago` date NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pago`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `recaudaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `recaudaciones` (`id_pago`, `id_usuario`, `concepto`, `monto`, `comprobante_url`, `estado_pago`, `observacion`, `fecha_pago`, `fecha_registro`) VALUES ('1', '5', 'pago mensual alicuota Agosto', '300.00', NULL, 'PENDIENTE', 'pago puntual', '2026-08-19', '2026-08-19 22:03:22');

DROP TABLE IF EXISTS `recibos_pago`;
CREATE TABLE `recibos_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pago` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `numero_recibo` varchar(20) NOT NULL,
  `monto_pagado` decimal(10,2) NOT NULL,
  `concepto` varchar(150) NOT NULL,
  `fecha_emision` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `reservas`;
CREATE TABLE `reservas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `espacio` varchar(100) NOT NULL,
  `fecha_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('PENDIENTE','APROBADA','RECHAZADA','CANCELADA') DEFAULT 'PENDIENTE',
  `observaciones` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `reservas` (`id`, `id_usuario`, `espacio`, `fecha_reserva`, `hora_inicio`, `hora_fin`, `estado`, `observaciones`, `fecha_registro`) VALUES ('1', '4', 'Salon de Eventos', '2026-08-19', '08:00:00', '12:00:00', 'PENDIENTE', 'lugar limpio', '2026-08-18 23:33:44');
INSERT INTO `reservas` (`id`, `id_usuario`, `espacio`, `fecha_reserva`, `hora_inicio`, `hora_fin`, `estado`, `observaciones`, `fecha_registro`) VALUES ('2', '3', 'Piscina', '2026-08-20', '10:00:00', '12:00:00', 'PENDIENTE', 'van a ingresar 10 personas', '2026-08-19 22:30:27');

DROP TABLE IF EXISTS `tramites`;
CREATE TABLE `tramites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `solicitante` varchar(150) NOT NULL,
  `asunto` varchar(200) NOT NULL,
  `estado` enum('PENDIENTE','EN_PROCESO','COMPLETADO') DEFAULT 'PENDIENTE',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(15) NOT NULL,
  `nombres` varchar(120) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono_whatsapp` varchar(20) NOT NULL,
  `numero_vivienda` varchar(20) NOT NULL,
  `rol` enum('RESIDENTE','ADMINISTRADOR','DIRECTIVA') NOT NULL DEFAULT 'RESIDENTE',
  `puesto_casa` varchar(50) DEFAULT NULL,
  `clave_hash` varchar(255) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` (`id_usuario`, `cedula`, `nombres`, `correo`, `telefono_whatsapp`, `numero_vivienda`, `rol`, `puesto_casa`, `clave_hash`, `estado`, `creado_en`, `created_at`) VALUES ('1', '1700000001', 'Administrador General', 'admin@vallermosso.com', '593999999991', 'Oficina Admin', 'ADMINISTRADOR', NULL, '$2y$10$R4xNZ7CBwRB82Eckwbykxe61BDBQ0mv6PL6q1.NAr3ToqSlhkbVqG', 'ACTIVO', '2026-08-10 01:37:15', '2026-08-15 22:12:23');
INSERT INTO `usuarios` (`id_usuario`, `cedula`, `nombres`, `correo`, `telefono_whatsapp`, `numero_vivienda`, `rol`, `puesto_casa`, `clave_hash`, `estado`, `creado_en`, `created_at`) VALUES ('2', '1700000002', 'Presidente Directiva', 'directiva@vallermosso.com', '593999999992', 'Casa 01', 'DIRECTIVA', NULL, '$2y$10$R4xNZ7CBwRB82Eckwbykxe61BDBQ0mv6PL6q1.NAr3ToqSlhkbVqG', 'ACTIVO', '2026-08-10 01:37:15', '2026-08-15 22:12:23');
INSERT INTO `usuarios` (`id_usuario`, `cedula`, `nombres`, `correo`, `telefono_whatsapp`, `numero_vivienda`, `rol`, `puesto_casa`, `clave_hash`, `estado`, `creado_en`, `created_at`) VALUES ('3', '1700000003', 'Juan Pérez (Residente)', 'residente@vallermosso.com', '593999999993', 'Casa 15', 'RESIDENTE', NULL, '$2y$10$R4xNZ7CBwRB82Eckwbykxe61BDBQ0mv6PL6q1.NAr3ToqSlhkbVqG', 'ACTIVO', '2026-08-10 01:37:15', '2026-08-15 22:12:23');
INSERT INTO `usuarios` (`id_usuario`, `cedula`, `nombres`, `correo`, `telefono_whatsapp`, `numero_vivienda`, `rol`, `puesto_casa`, `clave_hash`, `estado`, `creado_en`, `created_at`) VALUES ('4', '1727642553', 'Chicaiza Rocha Sonia Maribel', 'snchicaiza@gmail.com', '0963610976', '355', 'RESIDENTE', NULL, '$2y$10$.UFFi7r2weHRgNIrAIE5dusuKgOoIgZdVNZASwaISzuAIvOG1ybJK', 'ACTIVO', '2026-08-18 20:19:08', '2026-08-18 20:19:08');
INSERT INTO `usuarios` (`id_usuario`, `cedula`, `nombres`, `correo`, `telefono_whatsapp`, `numero_vivienda`, `rol`, `puesto_casa`, `clave_hash`, `estado`, `creado_en`, `created_at`) VALUES ('5', '1315217594', 'Cedeño Josselyn', 'loostefany98@gmail.com', '0984292142', '123', 'RESIDENTE', NULL, '$2y$10$rfneyutjTVdUKKk00Ar5O.Qsha8Z3wxpyJXXPriZHMTjEl7YvNEDu', 'ACTIVO', '2026-08-18 21:21:32', '2026-08-18 21:21:32');

SET FOREIGN_KEY_CHECKS = 1;
