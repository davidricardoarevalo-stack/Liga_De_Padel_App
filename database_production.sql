-- Liga de Padel - Estructura de Base de Datos para Producción Bluehost
-- Ejecutar en phpMyAdmin o herramienta de gestión MySQL

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `second_last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `role` enum('Administrador','Asistente','Club','Deportista') NOT NULL DEFAULT 'Deportista',
  `status` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `club_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `clubs`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `clubs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `legal_representative` varchar(200) NOT NULL,
  `status` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `contact_person` varchar(200) DEFAULT NULL,
  `director_tecnico` varchar(200) DEFAULT NULL,
  `fisioterapeuta` varchar(200) DEFAULT NULL,
  `asistente_tecnico` varchar(200) DEFAULT NULL,
  `delegado` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `athletes`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `athletes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `second_last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `birthdate` date NOT NULL,
  `club_id` int(11) NOT NULL,
  `document_type` enum('Cedula','Tarjeta de Identidad','Registro Civil','Cedula extranjeria','Pasaporte') NOT NULL,
  `document_number` varchar(50) NOT NULL,
  `mobile_phone` varchar(20) DEFAULT NULL,
  `rep_legal_name` varchar(200) DEFAULT NULL,
  `rep_legal_email` varchar(255) DEFAULT NULL,
  `rep_legal_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_document` (`document_type`, `document_number`),
  KEY `idx_email` (`email`),
  KEY `idx_club` (`club_id`),
  FOREIGN KEY (`club_id`) REFERENCES `clubs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `tournaments`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tournaments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `place` varchar(200) DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Insertar usuario administrador por defecto
-- --------------------------------------------------------

INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `role`, `status`) VALUES
('Administrador', 'Sistema', 'app@app.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Activo');

-- Nota: La contraseña es "123" hasheada con bcrypt
-- Cambiar en producción por una contraseña segura

-- --------------------------------------------------------
-- Crear índices adicionales para optimización
-- --------------------------------------------------------

ALTER TABLE `users` ADD FOREIGN KEY (`club_id`) REFERENCES `clubs`(`id`) ON DELETE SET NULL;

COMMIT;