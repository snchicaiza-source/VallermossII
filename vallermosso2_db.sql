-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-08-2026 a las 05:53:40
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
-- Estructura de tabla para la tabla `comunicados`
--

CREATE TABLE `comunicados` (
  `id_comunicado` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `mensaje` text NOT NULL,
  `canal` enum('EMAIL','WHATSAPP','AMBOS') NOT NULL,
  `enviado_por` int(11) NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comunicados`
--

INSERT INTO `comunicados` (`id_comunicado`, `titulo`, `mensaje`, `canal`, `enviado_por`, `fecha_envio`) VALUES
(1, 'mantenimiento', 'se cerraran las calles S y M', 'AMBOS', 1, '2026-08-10 07:15:30');

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
  `estado` enum('PENDIENTE','EN_REVISION','PAGADO') DEFAULT 'PENDIENTE',
  `comprobante_url` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_usuario`, `concepto`, `monto`, `fecha_vencimiento`, `estado`, `comprobante_url`, `fecha_subida`, `created_at`) VALUES
(1, 2, 'Alícuota Ordinaria - Agosto 2026', 45.00, '2026-08-05', 'PENDIENTE', NULL, NULL, '2026-08-10 07:21:44'),
(2, 2, 'Alícuota Ordinaria - Julio 2026', 45.00, '2026-07-05', 'PAGADO', NULL, NULL, '2026-08-10 07:21:44'),
(3, 2, 'Cuota Extraordinaria Mantenimiento Portón', 20.00, '2026-06-15', 'PAGADO', NULL, NULL, '2026-08-10 07:21:44'),
(4, 3, 'Alícuota Ordinaria - Agosto 2026', 45.00, '2026-08-05', 'PAGADO', 'public/uploads/comprobantes/voucher_3_4_1786346952.jpeg', '2026-08-10 02:29:12', '2026-08-10 07:28:00'),
(5, 3, 'Alícuota Ordinaria - Julio 2026', 45.00, '2026-07-05', 'PAGADO', NULL, NULL, '2026-08-10 07:28:00'),
(6, 3, 'Cuota Extraordinaria Mantenimiento Portón', 20.00, '2026-06-15', 'PAGADO', NULL, NULL, '2026-08-10 07:28:00');

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
  `clave_hash` varchar(255) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `cedula`, `nombres`, `correo`, `telefono_whatsapp`, `numero_vivienda`, `rol`, `clave_hash`, `estado`, `creado_en`) VALUES
(1, '1700000001', 'Administrador General', 'admin@vallermosso.com', '593999999991', 'Oficina Admin', 'ADMINISTRADOR', '$2y$10$R4xNZ7CBwRB82Eckwbykxe61BDBQ0mv6PL6q1.NAr3ToqSlhkbVqG', 'ACTIVO', '2026-08-10 06:37:15'),
(2, '1700000002', 'Presidente Directiva', 'directiva@vallermosso.com', '593999999992', 'Casa 01', 'DIRECTIVA', '$2y$10$R4xNZ7CBwRB82Eckwbykxe61BDBQ0mv6PL6q1.NAr3ToqSlhkbVqG', 'ACTIVO', '2026-08-10 06:37:15'),
(3, '1700000003', 'Juan Pérez (Residente)', 'residente@vallermosso.com', '593999999993', 'Casa 15', 'RESIDENTE', '$2y$10$R4xNZ7CBwRB82Eckwbykxe61BDBQ0mv6PL6q1.NAr3ToqSlhkbVqG', 'ACTIVO', '2026-08-10 06:37:15');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`id_comunicado`),
  ADD KEY `enviado_por` (`enviado_por`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `recaudaciones`
--
ALTER TABLE `recaudaciones`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_usuario` (`id_usuario`);

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
-- AUTO_INCREMENT de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id_comunicado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `recaudaciones`
--
ALTER TABLE `recaudaciones`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD CONSTRAINT `comunicados_ibfk_1` FOREIGN KEY (`enviado_por`) REFERENCES `usuarios` (`id_usuario`);

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
