-- Script para corregir caracteres con tildes corruptas
-- Clubs
UPDATE clubs SET name = REPLACE(name, 'PÃ¡del', 'Pádel');
UPDATE clubs SET name = REPLACE(name, 'BogotÃ¡', 'Bogotá');

-- Tournaments
UPDATE tournaments SET name = REPLACE(name, 'BogotÃ¡', 'Bogotá');
UPDATE tournaments SET name = REPLACE(name, 'PÃ¡del', 'Pádel');

-- Athletes (revisar nombres con tildes)
UPDATE athletes SET first_name = REPLACE(first_name, 'JuliÃ¡n', 'Julián');
UPDATE athletes SET first_name = REPLACE(first_name, 'AndrÃ©s', 'Andrés');
UPDATE athletes SET first_name = 'Julián David' WHERE first_name = 'Julian David' AND last_name = 'Arévalo Carrero';
UPDATE athletes SET last_name = 'Arévalo Carrero' WHERE last_name = 'ArÃ©valo Carrero';

-- Users (revisar nombres con tildes)
UPDATE users SET name = REPLACE(name, 'RicÃ¡rdo', 'Ricardo');
UPDATE users SET name = 'David Ricardo Arévalo Lizarazo' WHERE name = 'David Ricardo Arevalo Lizarazo';

-- Set proper charset for the database
ALTER DATABASE liga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Set proper charset for all tables
ALTER TABLE clubs CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tournaments CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE athletes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;