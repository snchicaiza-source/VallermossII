-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-08-2026 a las 04:48:18
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `vallermosso2_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activos`
--

CREATE TABLE `activos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `estado` enum('EXCELENTE','BUENO','REGULAR','MALO') DEFAULT 'BUENO',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `activos`
--

INSERT INTO `activos` (`id`, `nombre`, `estado`, `created_at`) VALUES
(1, 'bombas de agua', 'BUENO', '2026-08-19 02:23:50'),
(2, 'manguera', 'MALO', '2026-08-19 02:24:20'),
(3, 'jjj', 'BUENO', '2026-08-19 02:26:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicados`
--

CREATE TABLE `comunicados` (
  `id_comunicado` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `mensaje` text NOT NULL,
  `canal` enum('EMAIL','WHATSAPP','AMBOS') NOT NULL,
  `enviado_por` int(11) NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comunicados`
--

INSERT INTO `comunicados` (`id_comunicado`, `id_usuario`, `titulo`, `mensaje`, `canal`, `enviado_por`, `fecha_envio`) VALUES
(1, NULL, 'mantenimiento', 'se cerraran las calles S y M', 'AMBOS', 1, '2026-08-10 07:15:30'),
(2, NULL, 'reunion General', 'PRUEBA REUNION', 'EMAIL', 1, '2026-08-19 01:17:25'),
(3, NULL, 'PRUEBA', '1234', 'AMBOS', 1, '2026-08-19 01:19:51'),
(4, NULL, 'reunion General', 'nose', 'AMBOS', 2, '2026-08-19 02:28:43'),
(5, NULL, 'programa listo', 'ya podemos revisar el documento', 'AMBOS', 1, '2026-08-19 03:30:55'),
(6, NULL, 'iii', 'jj', 'AMBOS', 1, '2026-08-19 04:20:57'),
(7, NULL, 'uuu', 'uu', 'AMBOS', 1, '2026-08-19 04:26:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `convenios`
--

CREATE TABLE `convenios` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `num_cuotas` int(11) DEFAULT 1,
  `estado` enum('ACTIVO','CUMPLIDO','INCUMPLIDO') DEFAULT 'ACTIVO',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `tipo` enum('LEYES','REGLAMENTOS','ACTAS') NOT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `fecha` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_directiva`
--

CREATE TABLE `documentos_directiva` (
  `id_documento` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `categoria` enum('LEYES','ACTAS_ASAMBLEA','ACTAS_DIRECTIVA','DECLARATORIA_PH') NOT NULL,
  `archivo_url` varchar(255) NOT NULL,
  `fecha_publicacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documentos_directiva`
--

INSERT INTO `documentos_directiva` (`id_documento`, `titulo`, `categoria`, `archivo_url`, `fecha_publicacion`) VALUES
(1, 'Ley de Propiedad Horizontal', 'LEYES', '#', '2026-01-15'),
(2, 'Reglamento Interno de Convivencia', 'LEYES', '#', '2026-02-01'),
(3, 'Acta Asamblea General Ordinaria 2026', 'ACTAS_ASAMBLEA', '#', '2026-06-15'),
(4, 'Acta Sesion de Directiva Julio 2026', 'ACTAS_DIRECTIVA', '#', '2026-07-10'),
(5, 'Declaratoria de Propiedad Horizontal', 'DECLARATORIA_PH', '#', '2026-01-01'),
(6, 'prueba', 'LEYES', 'public/uploads/documentos/doc_1787113518_253fbe1f.docx', '2026-08-19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas`
--

CREATE TABLE `encuestas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `opciones` text NOT NULL COMMENT 'JSON array of options',
  `activa` tinyint(1) DEFAULT 1,
  `creada_por` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas_votos`
--

CREATE TABLE `encuestas_votos` (
  `id` int(11) NOT NULL,
  `id_encuesta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `respuesta` varchar(200) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias`
--

CREATE TABLE `incidencias` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('DANO','QUEJA','RESERVACION') DEFAULT 'DANO',
  `descripcion` text NOT NULL,
  `estado` enum('PENDIENTE','EN_REVISION','RESUELTO') DEFAULT 'PENDIENTE',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_log`
--

CREATE TABLE `notificaciones_log` (
  `id` int(11) NOT NULL,
  `canal` enum('EMAIL','WHATSAPP','AMBOS') NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `destinatario_nombre` varchar(150) DEFAULT NULL,
  `destinatario_correo` varchar(100) DEFAULT NULL,
  `destinatario_telefono` varchar(20) DEFAULT NULL,
  `estado` enum('ENVIADO','FALLIDO','PENDIENTE') DEFAULT 'PENDIENTE',
  `error_detalle` text DEFAULT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones_log`
--

INSERT INTO `notificaciones_log` (`id`, `canal`, `titulo`, `destinatario_nombre`, `destinatario_correo`, `destinatario_telefono`, `estado`, `error_detalle`, `fecha_envio`) VALUES
(1, 'EMAIL', 'reunion General', 'Administrador General', 'admin@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 02:28:43'),
(2, 'EMAIL', 'reunion General', 'Presidente Directiva', 'directiva@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 02:28:43'),
(3, 'EMAIL', 'reunion General', 'Juan Pérez (Residente)', 'residente@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 02:28:43'),
(4, 'EMAIL', 'reunion General', 'Chicaiza Rocha Sonia Maribel', 'snchicaiza@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 02:28:43'),
(5, 'EMAIL', 'reunion General', 'Cedeño Josselyn', 'loostefany98@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 02:28:43'),
(6, 'WHATSAPP', 'reunion General', 'Administrador General', NULL, '593999999991', 'PENDIENTE', NULL, '2026-08-19 02:28:43'),
(7, 'WHATSAPP', 'reunion General', 'Presidente Directiva', NULL, '593999999992', 'PENDIENTE', NULL, '2026-08-19 02:28:43'),
(8, 'WHATSAPP', 'reunion General', 'Juan Pérez (Residente)', NULL, '593999999993', 'PENDIENTE', NULL, '2026-08-19 02:28:43'),
(9, 'WHATSAPP', 'reunion General', 'Chicaiza Rocha Sonia Maribel', NULL, '0963610976', 'PENDIENTE', NULL, '2026-08-19 02:28:43'),
(10, 'WHATSAPP', 'reunion General', 'Cedeño Josselyn', NULL, '0984292142', 'PENDIENTE', NULL, '2026-08-19 02:28:43'),
(11, 'EMAIL', 'programa listo', 'Administrador General', 'admin@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 03:30:55'),
(12, 'EMAIL', 'programa listo', 'Presidente Directiva', 'directiva@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 03:30:55'),
(13, 'EMAIL', 'programa listo', 'Juan Pérez (Residente)', 'residente@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 03:30:55'),
(14, 'EMAIL', 'programa listo', 'Chicaiza Rocha Sonia Maribel', 'snchicaiza@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 03:30:55'),
(15, 'EMAIL', 'programa listo', 'Cedeño Josselyn', 'loostefany98@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 03:30:55'),
(16, 'WHATSAPP', 'programa listo', 'Administrador General', NULL, '593999999991', 'PENDIENTE', NULL, '2026-08-19 03:30:55'),
(17, 'WHATSAPP', 'programa listo', 'Presidente Directiva', NULL, '593999999992', 'PENDIENTE', NULL, '2026-08-19 03:30:55'),
(18, 'WHATSAPP', 'programa listo', 'Juan Pérez (Residente)', NULL, '593999999993', 'PENDIENTE', NULL, '2026-08-19 03:30:55'),
(19, 'WHATSAPP', 'programa listo', 'Chicaiza Rocha Sonia Maribel', NULL, '0963610976', 'PENDIENTE', NULL, '2026-08-19 03:30:55'),
(20, 'WHATSAPP', 'programa listo', 'Cedeño Josselyn', NULL, '0984292142', 'PENDIENTE', NULL, '2026-08-19 03:30:55'),
(21, 'EMAIL', 'iii', 'Administrador General', 'admin@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 04:20:57'),
(22, 'EMAIL', 'iii', 'Presidente Directiva', 'directiva@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 04:20:57'),
(23, 'EMAIL', 'iii', 'Juan Pérez (Residente)', 'residente@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 04:20:57'),
(24, 'EMAIL', 'iii', 'Chicaiza Rocha Sonia Maribel', 'snchicaiza@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 04:20:57'),
(25, 'EMAIL', 'iii', 'Cedeño Josselyn', 'loostefany98@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 04:20:57'),
(26, 'WHATSAPP', 'iii', 'Administrador General', NULL, '593999999991', 'PENDIENTE', NULL, '2026-08-19 04:20:57'),
(27, 'WHATSAPP', 'iii', 'Presidente Directiva', NULL, '593999999992', 'PENDIENTE', NULL, '2026-08-19 04:20:57'),
(28, 'WHATSAPP', 'iii', 'Juan Pérez (Residente)', NULL, '593999999993', 'PENDIENTE', NULL, '2026-08-19 04:20:57'),
(29, 'WHATSAPP', 'iii', 'Chicaiza Rocha Sonia Maribel', NULL, '0963610976', 'PENDIENTE', NULL, '2026-08-19 04:20:57'),
(30, 'WHATSAPP', 'iii', 'Cedeño Josselyn', NULL, '0984292142', 'PENDIENTE', NULL, '2026-08-19 04:20:57'),
(31, 'EMAIL', 'uuu', 'Administrador General', 'admin@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 04:26:15'),
(32, 'EMAIL', 'uuu', 'Presidente Directiva', 'directiva@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 04:26:15'),
(33, 'EMAIL', 'uuu', 'Juan Pérez (Residente)', 'residente@vallermosso.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 04:26:15'),
(34, 'EMAIL', 'uuu', 'Chicaiza Rocha Sonia Maribel', 'snchicaiza@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 04:26:15'),
(35, 'EMAIL', 'uuu', 'Cedeño Josselyn', 'loostefany98@gmail.com', NULL, 'FALLIDO', 'SMTP no configurado. Edite config/mail_config.php con sus credenciales de Gmail.', '2026-08-19 04:26:15'),
(36, 'WHATSAPP', 'uuu', 'Administrador General', NULL, '593999999991', 'PENDIENTE', NULL, '2026-08-19 04:26:15'),
(37, 'WHATSAPP', 'uuu', 'Presidente Directiva', NULL, '593999999992', 'PENDIENTE', NULL, '2026-08-19 04:26:15'),
(38, 'WHATSAPP', 'uuu', 'Juan Pérez (Residente)', NULL, '593999999993', 'PENDIENTE', NULL, '2026-08-19 04:26:15'),
(39, 'WHATSAPP', 'uuu', 'Chicaiza Rocha Sonia Maribel', NULL, '0963610976', 'PENDIENTE', NULL, '2026-08-19 04:26:15'),
(40, 'WHATSAPP', 'uuu', 'Cedeño Josselyn', NULL, '0984292142', 'PENDIENTE', NULL, '2026-08-19 04:26:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_usuario`
--

CREATE TABLE `notificaciones_usuario` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'COMUNICADO',
  `titulo` varchar(200) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `referencia_tipo` varchar(50) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones_usuario`
--

INSERT INTO `notificaciones_usuario` (`id`, `id_usuario`, `tipo`, `titulo`, `mensaje`, `referencia_id`, `referencia_tipo`, `leida`, `fecha_creacion`) VALUES
(1, 1, 'COMUNICADO', 'uuu', 'uu', 7, 'comunicado', 1, '2026-08-19 04:26:15'),
(2, 2, 'COMUNICADO', 'uuu', 'uu', 7, 'comunicado', 0, '2026-08-19 04:26:15'),
(3, 3, 'COMUNICADO', 'uuu', 'uu', 7, 'comunicado', 0, '2026-08-19 04:26:15'),
(4, 4, 'COMUNICADO', 'uuu', 'uu', 7, 'comunicado', 0, '2026-08-19 04:26:15'),
(5, 5, 'COMUNICADO', 'uuu', 'uu', 7, 'comunicado', 0, '2026-08-19 04:26:15'),
(6, 1, 'COMUNICADO', 'iii', 'jj', 6, 'comunicado', 1, '2026-08-19 04:20:57'),
(7, 2, 'COMUNICADO', 'iii', 'jj', 6, 'comunicado', 0, '2026-08-19 04:20:57'),
(8, 3, 'COMUNICADO', 'iii', 'jj', 6, 'comunicado', 0, '2026-08-19 04:20:57'),
(9, 4, 'COMUNICADO', 'iii', 'jj', 6, 'comunicado', 0, '2026-08-19 04:20:57'),
(10, 5, 'COMUNICADO', 'iii', 'jj', 6, 'comunicado', 0, '2026-08-19 04:20:57'),
(11, 1, 'COMUNICADO', 'programa listo', 'ya podemos revisar el documento', 5, 'comunicado', 1, '2026-08-19 03:30:55'),
(12, 2, 'COMUNICADO', 'programa listo', 'ya podemos revisar el documento', 5, 'comunicado', 0, '2026-08-19 03:30:55'),
(13, 3, 'COMUNICADO', 'programa listo', 'ya podemos revisar el documento', 5, 'comunicado', 0, '2026-08-19 03:30:55'),
(14, 4, 'COMUNICADO', 'programa listo', 'ya podemos revisar el documento', 5, 'comunicado', 0, '2026-08-19 03:30:55'),
(15, 5, 'COMUNICADO', 'programa listo', 'ya podemos revisar el documento', 5, 'comunicado', 0, '2026-08-19 03:30:55'),
(16, 1, 'COMUNICADO', 'reunion General', 'nose', 4, 'comunicado', 1, '2026-08-19 02:28:43'),
(17, 2, 'COMUNICADO', 'reunion General', 'nose', 4, 'comunicado', 0, '2026-08-19 02:28:43'),
(18, 3, 'COMUNICADO', 'reunion General', 'nose', 4, 'comunicado', 0, '2026-08-19 02:28:43'),
(19, 4, 'COMUNICADO', 'reunion General', 'nose', 4, 'comunicado', 0, '2026-08-19 02:28:43'),
(20, 5, 'COMUNICADO', 'reunion General', 'nose', 4, 'comunicado', 0, '2026-08-19 02:28:43'),
(21, 1, 'COMUNICADO', 'PRUEBA', '1234', 3, 'comunicado', 1, '2026-08-19 01:19:51'),
(22, 2, 'COMUNICADO', 'PRUEBA', '1234', 3, 'comunicado', 0, '2026-08-19 01:19:51'),
(23, 3, 'COMUNICADO', 'PRUEBA', '1234', 3, 'comunicado', 0, '2026-08-19 01:19:51'),
(24, 4, 'COMUNICADO', 'PRUEBA', '1234', 3, 'comunicado', 0, '2026-08-19 01:19:51'),
(25, 5, 'COMUNICADO', 'PRUEBA', '1234', 3, 'comunicado', 0, '2026-08-19 01:19:51'),
(26, 1, 'COMUNICADO', 'reunion General', 'PRUEBA REUNION', 2, 'comunicado', 1, '2026-08-19 01:17:25'),
(27, 2, 'COMUNICADO', 'reunion General', 'PRUEBA REUNION', 2, 'comunicado', 0, '2026-08-19 01:17:25'),
(28, 3, 'COMUNICADO', 'reunion General', 'PRUEBA REUNION', 2, 'comunicado', 0, '2026-08-19 01:17:25'),
(29, 4, 'COMUNICADO', 'reunion General', 'PRUEBA REUNION', 2, 'comunicado', 0, '2026-08-19 01:17:25'),
(30, 5, 'COMUNICADO', 'reunion General', 'PRUEBA REUNION', 2, 'comunicado', 0, '2026-08-19 01:17:25'),
(31, 1, 'COMUNICADO', 'mantenimiento', 'se cerraran las calles S y M', 1, 'comunicado', 1, '2026-08-10 07:15:30'),
(32, 2, 'COMUNICADO', 'mantenimiento', 'se cerraran las calles S y M', 1, 'comunicado', 0, '2026-08-10 07:15:30'),
(33, 3, 'COMUNICADO', 'mantenimiento', 'se cerraran las calles S y M', 1, 'comunicado', 0, '2026-08-10 07:15:30'),
(34, 4, 'COMUNICADO', 'mantenimiento', 'se cerraran las calles S y M', 1, 'comunicado', 0, '2026-08-10 07:15:30'),
(35, 5, 'COMUNICADO', 'mantenimiento', 'se cerraran las calles S y M', 1, 'comunicado', 0, '2026-08-10 07:15:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `concepto` varchar(150) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('PENDIENTE','EN_REVISION','PAGADO','RECHAZADO') DEFAULT 'PENDIENTE',
  `comprobante_url` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_usuario`, `concepto`, `monto`, `fecha_vencimiento`, `estado`, `comprobante_url`, `fecha_subida`, `created_at`) VALUES
(1, 2, 'Alícuota Ordinaria - Agosto 2026', 45.00, '2026-08-05', 'PAGADO', NULL, NULL, '2026-08-10 07:21:44'),
(2, 2, 'Alícuota Ordinaria - Julio 2026', 45.00, '2026-07-05', 'PAGADO', NULL, NULL, '2026-08-10 07:21:44'),
(3, 2, 'Cuota Extraordinaria Mantenimiento Portón', 20.00, '2026-06-15', 'PAGADO', NULL, NULL, '2026-08-10 07:21:44'),
(4, 3, 'Alícuota Ordinaria - Agosto 2026', 45.00, '2026-08-05', 'PAGADO', 'public/uploads/comprobantes/voucher_3_4_1786346952.jpeg', '2026-08-10 02:29:12', '2026-08-10 07:28:00'),
(5, 3, 'Alícuota Ordinaria - Julio 2026', 45.00, '2026-07-05', 'PAGADO', NULL, NULL, '2026-08-10 07:28:00'),
(6, 3, 'Cuota Extraordinaria Mantenimiento Portón', 20.00, '2026-06-15', 'PAGADO', NULL, NULL, '2026-08-10 07:28:00'),
(7, 3, 'uu', 88.00, '2026-08-16', 'PAGADO', 'pago_1786940033_465.jpeg', NULL, '2026-08-17 04:13:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto`
--

CREATE TABLE `presupuesto` (
  `id_presupuesto` int(11) NOT NULL,
  `rubro` varchar(150) NOT NULL,
  `monto_asignado` decimal(10,2) NOT NULL,
  `monto_ejecutado` decimal(10,2) DEFAULT 0.00,
  `periodo` varchar(20) NOT NULL DEFAULT '2026'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto`
--

INSERT INTO `presupuesto` (`id_presupuesto`, `rubro`, `monto_asignado`, `monto_ejecutado`, `periodo`) VALUES
(1, 'Mantenimiento Portón Eléctrico', 1200.00, 450.00, '2026'),
(2, 'Jardinería y Áreas Verdes', 800.00, 800.00, '2026'),
(3, 'Seguridad y Monitoreo', 2500.00, 1800.00, '2026');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_ejecucion`
--

CREATE TABLE `presupuesto_ejecucion` (
  `id` int(11) NOT NULL,
  `concepto` varchar(150) NOT NULL,
  `monto_presupuestado` decimal(10,2) NOT NULL,
  `monto_ejecutado` decimal(10,2) DEFAULT 0.00,
  `porcentaje` decimal(5,2) DEFAULT 0.00,
  `periodo` varchar(20) DEFAULT '2026'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `nombre_empresa` varchar(150) NOT NULL,
  `servicio_rubro` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `monto_contrato` decimal(10,2) NOT NULL,
  `estado_pago` enum('AL_DIA','PENDIENTE','EN_PROCESO') DEFAULT 'AL_DIA',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `nombre_empresa`, `servicio_rubro`, `contacto`, `monto_contrato`, `estado_pago`, `created_at`) VALUES
(1, 'Serviseg Cía Ltda', 'Seguridad Física', '0991234567', 600.00, 'AL_DIA', '2026-08-16 02:31:58'),
(2, 'Jardines del Valle', 'Mantenimiento de Áreas Verdes', '0987654321', 150.00, 'PENDIENTE', '2026-08-16 02:31:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recaudaciones`
--

CREATE TABLE `recaudaciones` (
  `id_pago` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `concepto` varchar(150) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `comprobante_url` varchar(255) DEFAULT NULL,
  `estado_pago` enum('PENDIENTE','APROBADO','RECHAZADO') DEFAULT 'PENDIENTE',
  `observacion` text DEFAULT NULL,
  `fecha_pago` date NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibos_pago`
--

CREATE TABLE `recibos_pago` (
  `id` int(11) NOT NULL,
  `id_pago` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `numero_recibo` varchar(20) NOT NULL,
  `monto_pagado` decimal(10,2) NOT NULL,
  `concepto` varchar(150) NOT NULL,
  `fecha_emision` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `espacio` varchar(100) NOT NULL,
  `fecha_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('PENDIENTE','APROBADA','RECHAZADA','CANCELADA') DEFAULT 'PENDIENTE',
  `observaciones` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `id_usuario`, `espacio`, `fecha_reserva`, `hora_inicio`, `hora_fin`, `estado`, `observaciones`, `fecha_registro`) VALUES
(1, 4, 'Salon de Eventos', '2026-08-19', '08:00:00', '12:00:00', 'PENDIENTE', 'lugar limpio', '2026-08-19 04:33:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tramites`
--

CREATE TABLE `tramites` (
  `id` int(11) NOT NULL,
  `solicitante` varchar(150) NOT NULL,
  `asunto` varchar(200) NOT NULL,
  `estado` enum('PENDIENTE','EN_PROCESO','COMPLETADO') DEFAULT 'PENDIENTE',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `cedula`, `nombres`, `correo`, `telefono_whatsapp`, `numero_vivienda`, `rol`, `puesto_casa`, `clave_hash`, `estado`, `creado_en`, `created_at`) VALUES
(1, '1700000001', 'Administrador General', 'admin@vallermosso.com', '593999999991', 'Oficina Admin', 'ADMINISTRADOR', NULL, '$2y$10$R4xNZ7CBwRB82Eckwbykxe61BDBQ0mv6PL6q1.NAr3ToqSlhkbVqG', 'ACTIVO', '2026-08-10 06:37:15', '2026-08-16 03:12:23'),
(2, '1700000002', 'Presidente Directiva', 'directiva@vallermosso.com', '593999999992', 'Casa 01', 'DIRECTIVA', NULL, '$2y$10$R4xNZ7CBwRB82Eckwbykxe61BDBQ0mv6PL6q1.NAr3ToqSlhkbVqG', 'ACTIVO', '2026-08-10 06:37:15', '2026-08-16 03:12:23'),
(3, '1700000003', 'Juan Pérez (Residente)', 'residente@vallermosso.com', '593999999993', 'Casa 15', 'RESIDENTE', NULL, '$2y$10$R4xNZ7CBwRB82Eckwbykxe61BDBQ0mv6PL6q1.NAr3ToqSlhkbVqG', 'ACTIVO', '2026-08-10 06:37:15', '2026-08-16 03:12:23'),
(4, '1727642553', 'Chicaiza Rocha Sonia Maribel', 'snchicaiza@gmail.com', '0963610976', '355', 'RESIDENTE', NULL, '$2y$10$.UFFi7r2weHRgNIrAIE5dusuKgOoIgZdVNZASwaISzuAIvOG1ybJK', 'ACTIVO', '2026-08-19 01:19:08', '2026-08-19 01:19:08'),
(5, '1315217594', 'Cedeño Josselyn', 'loostefany98@gmail.com', '0984292142', '123', 'RESIDENTE', NULL, '$2y$10$rfneyutjTVdUKKk00Ar5O.Qsha8Z3wxpyJXXPriZHMTjEl7YvNEDu', 'ACTIVO', '2026-08-19 02:21:32', '2026-08-19 02:21:32');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activos`
--
ALTER TABLE `activos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`id_comunicado`),
  ADD KEY `enviado_por` (`enviado_por`);

--
-- Indices de la tabla `convenios`
--
ALTER TABLE `convenios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `documentos_directiva`
--
ALTER TABLE `documentos_directiva`
  ADD PRIMARY KEY (`id_documento`);

--
-- Indices de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `encuestas_votos`
--
ALTER TABLE `encuestas_votos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unica_voto` (`id_encuesta`,`id_usuario`);

--
-- Indices de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `notificaciones_log`
--
ALTER TABLE `notificaciones_log`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notificaciones_usuario`
--
ALTER TABLE `notificaciones_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_leida` (`id_usuario`,`leida`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `presupuesto`
--
ALTER TABLE `presupuesto`
  ADD PRIMARY KEY (`id_presupuesto`);

--
-- Indices de la tabla `presupuesto_ejecucion`
--
ALTER TABLE `presupuesto_ejecucion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indices de la tabla `recaudaciones`
--
ALTER TABLE `recaudaciones`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `recibos_pago`
--
ALTER TABLE `recibos_pago`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tramites`
--
ALTER TABLE `tramites`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activos`
--
ALTER TABLE `activos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id_comunicado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `convenios`
--
ALTER TABLE `convenios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos_directiva`
--
ALTER TABLE `documentos_directiva`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuestas_votos`
--
ALTER TABLE `encuestas_votos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones_log`
--
ALTER TABLE `notificaciones_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `notificaciones_usuario`
--
ALTER TABLE `notificaciones_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `presupuesto`
--
ALTER TABLE `presupuesto`
  MODIFY `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `presupuesto_ejecucion`
--
ALTER TABLE `presupuesto_ejecucion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `recaudaciones`
--
ALTER TABLE `recaudaciones`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recibos_pago`
--
ALTER TABLE `recibos_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tramites`
--
ALTER TABLE `tramites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD CONSTRAINT `comunicados_ibfk_1` FOREIGN KEY (`enviado_por`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `convenios`
--
ALTER TABLE `convenios`
  ADD CONSTRAINT `convenios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD CONSTRAINT `incidencias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones_usuario`
--
ALTER TABLE `notificaciones_usuario`
  ADD CONSTRAINT `notificaciones_usuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `recaudaciones`
--
ALTER TABLE `recaudaciones`
  ADD CONSTRAINT `recaudaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
