-- Migration script from SQLite to MySQL
-- Liga de Padel App Data Migration

USE liga;

-- Clear existing data (optional - comment out if you want to keep existing data)
-- DELETE FROM tournaments;
-- DELETE FROM athletes;
-- DELETE FROM clubs;
-- DELETE FROM users WHERE id > 2; -- Keep the test users we created

-- Insert Users from SQLite (adjusted for MySQL schema)
INSERT INTO users (id, email, password, role, name, birthdate, club_id) VALUES
(3, 'app@app.com', '$2b$10$xsG26c0xdiFb.5L46hpbd.4XjhaXDOqok02y1.GtIXrqYSwYoULi6', 'Administrador', 'App Administrator', '1990-01-01', NULL),
(4, 'david@david.com', '$2b$10$6kfGRYNDyZ9/9JEoTRcJ3.8DDzZL1b1vj7ys1ZWuuR.eC9O2bAJhW', 'Club', 'David Ricardo Arevalo Lizarazo', '1980-06-19', 3),
(5, 'test2@user.com', '$2b$10$RE2nwxVfKUKfG4iVoOKmBeNP6vnMiFb53aNCyhFc2z9w1fKx8qsFW', 'User', 'Test User', NULL, NULL),
(6, 'paula@paula.com', '$2b$10$J0ZZ6ttqJQG4Wl536kFs7OwRg5UN42./N3wer8WB8Z/iBhRZKWsw2', 'Club', 'Paula Andrea Viteri Villamarin', '1980-08-15', 4);

-- Insert Clubs from SQLite (adjusted for MySQL schema)
INSERT INTO clubs (id, name, address, director_tecnico, delegado, fisioterapeuta, asistente_tecnico) VALUES
(1, 'Slam Pádel', '100 con 15', 'Dir tec slam', 'Delegado slam', 'Fisio slam', 'Asistente slam'),
(2, 'Locos por Pádel', 'Boyaca con 134', NULL, NULL, NULL, NULL),
(3, 'Club Imperio Pádel', 'Cr 107 135 a 36', NULL, NULL, NULL, NULL),
(4, 'Club deportivo de padel la Pala', 'Cl 127 15 80', 'Dirctor la pala', 'delegado la pala', 'fisio la Pala', 'asistente la pala');

-- Insert Athletes from SQLite (mapping to new schema)
INSERT INTO athletes (id, first_name, last_name, birthdate, document_type, document_number, mobile_phone, rep_legal_name, rep_legal_phone, rep_legal_email, club_id) VALUES
(4, 'Julian David', 'Arévalo Carrero', '2004-10-18', NULL, '1234567890', NULL, NULL, NULL, NULL, 1),
(5, 'Manuela', 'Abella Viteri', '2007-07-07', NULL, NULL, NULL, NULL, NULL, 'manu@manu.com', 2),
(6, 'Estefania', 'Arevalo Carrero', '2013-02-05', 'Tarjeta de Identidad', '1014883481', '3178959204', NULL, NULL, 'estefi@estefi.com', 3),
(7, 'Imperio 1', 'imperio imperio', '1982-06-19', NULL, NULL, NULL, NULL, NULL, 'imperio@imperio.com', 3),
(8, 'Primer segurndo', 'apellido app3', '1980-06-19', 'Cedula', '80230553', '3178959204', NULL, NULL, 'primer@primer.com', 3),
(9, 'Maria Paula', 'Abella Vitgeri', '2007-07-07', 'Cedula extranjeria', '12345678', '12346578', NULL, NULL, 'maria@maria.com', 3),
(10, 'Manuela a secas', 'Abella Viteri', '2007-07-07', 'Cedula', '12345678', '12345678', NULL, NULL, 'manuela@manuela.com', 4),
(11, 'Esteban', 'Chavez', '2007-10-24', 'Tarjeta de Identidad', '12345678', '12346578', 'Paula Viteri', '3195407836', 'paula@viteri.com', 4);

-- Insert Tournaments from SQLite (adjusted for MySQL schema)
INSERT INTO tournaments (id, name, start_date, end_date, club_id) VALUES
(1, '1er torneo de liga de Bogotá', '2025-09-21', '2025-09-21', 4),
(2, '2o torneo Liga de Bogotá 2025', '2025-10-18', '2025-10-18', 1),
(3, 'TorneoPrueba', '2025-12-01', '2025-12-01', 1),
(4, 'TorneoClub', '2025-11-11', '2025-11-11', 2);

-- Update AUTO_INCREMENT values to continue from imported data
ALTER TABLE users AUTO_INCREMENT = 7;
ALTER TABLE clubs AUTO_INCREMENT = 5;
ALTER TABLE athletes AUTO_INCREMENT = 12;
ALTER TABLE tournaments AUTO_INCREMENT = 5;

-- Verification queries
SELECT 'Users imported:' as info, COUNT(*) as count FROM users;
SELECT 'Clubs imported:' as info, COUNT(*) as count FROM clubs;
SELECT 'Athletes imported:' as info, COUNT(*) as count FROM athletes;
SELECT 'Tournaments imported:' as info, COUNT(*) as count FROM tournaments;