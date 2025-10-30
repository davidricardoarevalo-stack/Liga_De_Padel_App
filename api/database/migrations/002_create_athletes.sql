CREATE TABLE IF NOT EXISTS `athletes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(255),
  `last_name` VARCHAR(255),
  `birthdate` DATE,
  `document_type` VARCHAR(50),
  `document_number` VARCHAR(100),
  `mobile_phone` VARCHAR(50),
  `rep_legal_name` VARCHAR(255),
  `rep_legal_email` VARCHAR(255),
  `rep_legal_phone` VARCHAR(50),
  `club_id` INT NULL
);
