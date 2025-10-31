CREATE TABLE IF NOT EXISTS `tournaments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255),
  `start_date` DATE,
  `end_date` DATE,
  `club_id` INT NULL
);
