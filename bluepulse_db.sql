-- ============================================================
-- BluePulse Student Panel — SQL Dump
-- Database: bluepulse_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS bluepulse_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE bluepulse_db;

-- -----------------------------------------------
-- Tabel: students
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id          INT(11)      AUTO_INCREMENT PRIMARY KEY,
    nis         VARCHAR(20)  DEFAULT NULL,
    name        VARCHAR(100) NOT NULL,
    class       VARCHAR(50)  NOT NULL,
    address     TEXT         DEFAULT NULL,
    attendance  ENUM('Hadir','Tidak') DEFAULT 'Tidak',
    photo       VARCHAR(255) DEFAULT NULL,   -- nama file foto bukti kehadiran
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Data dummy
-- -----------------------------------------------
INSERT INTO students (nis, name, class, address, attendance, photo) VALUES
('2024001', 'Aldi Firmansyah',       'X RPL 1',   'Jl. Merdeka No. 12, Bandung',      'Hadir',  NULL),
('2024002', 'Bunga Lestari',         'X RPL 1',   'Jl. Sudirman No. 5, Bandung',       'Hadir',  NULL),
('2024003', 'Cahyo Nugroho',         'X RPL 2',   'Jl. Pahlawan No. 8, Cimahi',        'Tidak',  NULL),
('2024004', 'Dewi Rahayu',           'X RPL 2',   'Jl. Gatot Subroto No. 3, Bandung',  'Hadir',  NULL),
('2024005', 'Eko Prasetyo',          'XI RPL 1',  'Jl. Ahmad Yani No. 17, Bandung',    'Hadir',  NULL),
('2024006', 'Fitri Handayani',       'XI RPL 1',  'Jl. Diponegoro No. 22, Bandung',    'Tidak',  NULL),
('2024007', 'Gilang Ramadan',        'XI RPL 2',  'Jl. Imam Bonjol No. 9, Cimahi',     'Hadir',  NULL),
('2024008', 'Hana Pertiwi',          'XI RPL 2',  'Jl. Kartini No. 14, Bandung',       'Hadir',  NULL),
('2024009', 'Irvan Setiawan',        'XII RPL 1', 'Jl. Veteran No. 6, Bandung',        'Tidak',  NULL),
('2024010', 'Jasmine Aulia',         'XII RPL 1', 'Jl. Lombok No. 11, Bandung',        'Hadir',  NULL),
('2024011', 'Kevin Raditya',         'XII RPL 2', 'Jl. Flores No. 19, Cimahi',         'Hadir',  NULL),
('2024012', 'Lisa Amelia',           'XII RPL 2', 'Jl. Sulawesi No. 7, Bandung',       'Tidak',  NULL),
('2024013', 'Muhammad Farhan',       'X RPL 1',   'Jl. Kalimantan No. 2, Bandung',     'Hadir',  NULL),
('2024014', 'Nadia Khairunnisa',     'XI RPL 1',  'Jl. Jawa No. 33, Bandung',          'Hadir',  NULL),
('2024015', 'Oscar Adriansyah',      'XII RPL 1', 'Jl. Sumatra No. 4, Cimahi',         'Tidak',  NULL);