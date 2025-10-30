CREATE TABLE IF NOT EXISTS `clubs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255),
  `address` VARCHAR(512),
  `director_tecnico` VARCHAR(255),
  `fisioterapeuta` VARCHAR(255),
  `asistente_tecnico` VARCHAR(255),
  `delegado` VARCHAR(255)
);
