-- ============================================================
-- Actualizacion de base de datos - Vallermosso II
-- Ejecutar en phpMyAdmin de InfinityFree (opcional):
-- El sistema tambien migra la columna automaticamente al
-- bloquear el primer usuario, pero puedes ejecutarlo manualmente.
-- ============================================================

-- Permite el estado BLOQUEADO ademas de ACTIVO e INACTIVO
ALTER TABLE `usuarios`
  MODIFY `estado` ENUM('ACTIVO','INACTIVO','BLOQUEADO') DEFAULT 'ACTIVO';

-- Tabla de espacios comunes (por si no existe aun)
CREATE TABLE IF NOT EXISTS `espacios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
