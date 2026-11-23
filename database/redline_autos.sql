-- =====================================================================
-- Redline Autos - database schema and sample data (Milestone 2)
-- Target: MySQL 8.0.16+ (CHECK constraints are enforced from that version)
-- Import instructions: see the PowerShell command in README.md.
--
-- All rows below are SAMPLE DATA for development only. Names, e-mails,
-- VINs and prices are made up. Every sample user has the same sample
-- password hash (see the users INSERT); replace it before real use.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS redline_autos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE redline_autos;

-- Drop in reverse dependency order so the file can be re-imported.
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS test_drives;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS brands;
DROP TABLE IF EXISTS users;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- brands
-- ---------------------------------------------------------------------
CREATE TABLE brands (
    id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name    VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_brands_name (name)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- vehicles (a brand has many vehicles)
-- A brand that still has vehicles cannot be deleted (ON DELETE RESTRICT).
-- ---------------------------------------------------------------------
CREATE TABLE vehicles (
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    brand_id INT UNSIGNED NOT NULL,
    model    VARCHAR(100) NOT NULL,
    year     SMALLINT UNSIGNED NOT NULL,
    price    DECIMAL(10, 2) NOT NULL,
    mileage  INT UNSIGNED NOT NULL,
    vin      CHAR(17) NOT NULL,
    status   ENUM('available', 'sold') NOT NULL DEFAULT 'available',
    PRIMARY KEY (id),
    UNIQUE KEY uq_vehicles_vin (vin),
    KEY idx_vehicles_brand (brand_id),
    CONSTRAINT fk_vehicles_brand
        FOREIGN KEY (brand_id) REFERENCES brands (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_vehicles_year  CHECK (year BETWEEN 1980 AND 2100),
    CONSTRAINT chk_vehicles_price CHECK (price >= 0)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- test_drives (a user books many, a vehicle is booked many times)
-- Deleting a user or a vehicle also deletes their test drives.
-- ---------------------------------------------------------------------
CREATE TABLE test_drives (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED NOT NULL,
    vehicle_id   INT UNSIGNED NOT NULL,
    scheduled_at DATETIME NOT NULL,
    status       ENUM('pending', 'approved', 'cancelled') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (id),
    KEY idx_test_drives_user (user_id),
    KEY idx_test_drives_vehicle (vehicle_id),
    CONSTRAINT fk_test_drives_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_test_drives_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- reviews (a user writes many, a vehicle receives many)
-- Deleting a user or a vehicle also deletes their reviews.
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    vehicle_id INT UNSIGNED NOT NULL,
    rating     TINYINT UNSIGNED NOT NULL,
    comment    TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_reviews_user (user_id),
    KEY idx_reviews_vehicle (vehicle_id),
    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE = InnoDB;

-- =====================================================================
-- SAMPLE DATA
-- =====================================================================

-- Sample password hash (bcrypt, from the PHP manual's password_hash
-- example; the plain password is "rasmuslerdorf"). Same for all users.
-- Milestone 4 (registration/login) will create real hashes.
INSERT INTO users (id, name, email, password_hash, role, created_at) VALUES
    (1, 'Admin Redline',  'admin@example.com',  '$2y$07$BCryptRequires22Chrcte/VlQH0piJtjXl.0t1XkA8pw9dMXTpOq', 'admin',    '2026-10-20 09:00:00'),
    (2, 'Sam Customer',   'sam@example.com',    '$2y$07$BCryptRequires22Chrcte/VlQH0piJtjXl.0t1XkA8pw9dMXTpOq', 'customer', '2026-10-22 14:30:00'),
    (3, 'Lena Hodzic',    'lena@example.com',   '$2y$07$BCryptRequires22Chrcte/VlQH0piJtjXl.0t1XkA8pw9dMXTpOq', 'customer', '2026-10-24 11:15:00'),
    (4, 'Marko Petrovic', 'marko@example.com',  '$2y$07$BCryptRequires22Chrcte/VlQH0piJtjXl.0t1XkA8pw9dMXTpOq', 'customer', '2026-10-26 16:45:00'),
    (5, 'Ana Kovac',      'ana@example.com',    '$2y$07$BCryptRequires22Chrcte/VlQH0piJtjXl.0t1XkA8pw9dMXTpOq', 'customer', '2026-10-28 10:05:00');

INSERT INTO brands (id, name, country) VALUES
    (1, 'Toyota',    'Japan'),
    (2, 'Ford',      'United States'),
    (3, 'Honda',     'Japan'),
    (4, 'BMW',       'Germany'),
    (5, 'Chevrolet', 'United States');

-- VINs are made up (17 characters, no I/O/Q) and are not real vehicles.
INSERT INTO vehicles (id, brand_id, model, year, price, mileage, vin, status) VALUES
    (1, 1, 'Camry',      2019, 18900.00, 42000, '4T1B11HK5KU000001', 'available'),
    (2, 2, 'F-150',      2018, 26400.00, 61500, '1FTEW1EP8JF000002', 'available'),
    (3, 3, 'Civic',      2020, 19700.00, 30200, '2HGFC2F69LH000003', 'available'),
    (4, 4, '330i',       2017, 21500.00, 54800, 'WBA8E1C50HK000004', 'available'),
    (5, 5, 'Malibu',     2021, 20900.00, 22300, '1G1ZD5ST3MF000005', 'sold'),
    (6, 1, 'Corolla',    2016, 11200.00, 78900, '2T1BURHE4GC000006', 'available'),
    (7, 2, 'Mustang',    2019, 27800.00, 35100, '1FA6P8TH2K5000007', 'available'),
    (8, 3, 'CR-V',       2018, 21300.00, 48700, '2HKRW2H59JH000008', 'sold'),
    (9, 1, 'Camry', 2015, 10500.00, 12000, 'SAMPLEREDLN000009', 'sold'),
    (10, 2, 'Mustang', 2016, 10850.00, 13800, 'SAMPLEREDLN000010', 'available'),
    (11, 3, 'Accord', 2017, 11200.00, 15600, 'SAMPLEREDLN000011', 'available'),
    (12, 4, 'X5', 2018, 11550.00, 17400, 'SAMPLEREDLN000012', 'available'),
    (13, 5, 'Tahoe', 2019, 11900.00, 19200, 'SAMPLEREDLN000013', 'available'),
    (14, 1, 'Camry', 2020, 12250.00, 21000, 'SAMPLEREDLN000014', 'available'),
    (15, 2, 'Mustang', 2021, 12600.00, 22800, 'SAMPLEREDLN000015', 'available'),
    (16, 3, 'Accord', 2022, 12950.00, 24600, 'SAMPLEREDLN000016', 'sold'),
    (17, 4, 'X5', 2023, 13300.00, 26400, 'SAMPLEREDLN000017', 'available'),
    (18, 5, 'Tahoe', 2024, 13650.00, 28200, 'SAMPLEREDLN000018', 'available'),
    (19, 1, 'Camry', 2015, 14000.00, 30000, 'SAMPLEREDLN000019', 'available'),
    (20, 2, 'Mustang', 2016, 14350.00, 31800, 'SAMPLEREDLN000020', 'available'),
    (21, 3, 'Accord', 2017, 14700.00, 33600, 'SAMPLEREDLN000021', 'available'),
    (22, 4, 'X5', 2018, 15050.00, 35400, 'SAMPLEREDLN000022', 'available'),
    (23, 5, 'Tahoe', 2019, 15400.00, 37200, 'SAMPLEREDLN000023', 'sold'),
    (24, 1, 'Camry', 2020, 15750.00, 39000, 'SAMPLEREDLN000024', 'available'),
    (25, 2, 'Mustang', 2021, 16100.00, 40800, 'SAMPLEREDLN000025', 'available'),
    (26, 3, 'Accord', 2022, 16450.00, 42600, 'SAMPLEREDLN000026', 'available'),
    (27, 4, 'X5', 2023, 16800.00, 44400, 'SAMPLEREDLN000027', 'available'),
    (28, 5, 'Tahoe', 2024, 17150.00, 46200, 'SAMPLEREDLN000028', 'available'),
    (29, 1, 'Camry', 2015, 17500.00, 48000, 'SAMPLEREDLN000029', 'available'),
    (30, 2, 'Mustang', 2016, 17850.00, 49800, 'SAMPLEREDLN000030', 'sold'),
    (31, 3, 'Accord', 2017, 18200.00, 51600, 'SAMPLEREDLN000031', 'available'),
    (32, 4, 'X5', 2018, 18550.00, 53400, 'SAMPLEREDLN000032', 'available'),
    (33, 5, 'Tahoe', 2019, 18900.00, 55200, 'SAMPLEREDLN000033', 'available'),
    (34, 1, 'Camry', 2020, 19250.00, 57000, 'SAMPLEREDLN000034', 'available'),
    (35, 2, 'Mustang', 2021, 19600.00, 58800, 'SAMPLEREDLN000035', 'available'),
    (36, 3, 'Accord', 2022, 19950.00, 60600, 'SAMPLEREDLN000036', 'available'),
    (37, 4, 'X5', 2023, 20300.00, 62400, 'SAMPLEREDLN000037', 'sold'),
    (38, 5, 'Tahoe', 2024, 20650.00, 64200, 'SAMPLEREDLN000038', 'available'),
    (39, 1, 'Camry', 2015, 21000.00, 66000, 'SAMPLEREDLN000039', 'available'),
    (40, 2, 'Mustang', 2016, 21350.00, 67800, 'SAMPLEREDLN000040', 'available'),
    (41, 3, 'Accord', 2017, 21700.00, 69600, 'SAMPLEREDLN000041', 'available'),
    (42, 4, 'X5', 2018, 22050.00, 71400, 'SAMPLEREDLN000042', 'available'),
    (43, 5, 'Tahoe', 2019, 22400.00, 73200, 'SAMPLEREDLN000043', 'available'),
    (44, 1, 'Camry', 2020, 22750.00, 75000, 'SAMPLEREDLN000044', 'sold'),
    (45, 2, 'Mustang', 2021, 23100.00, 76800, 'SAMPLEREDLN000045', 'available'),
    (46, 3, 'Accord', 2022, 23450.00, 78600, 'SAMPLEREDLN000046', 'available'),
    (47, 4, 'X5', 2023, 23800.00, 80400, 'SAMPLEREDLN000047', 'available'),
    (48, 5, 'Tahoe', 2024, 24150.00, 82200, 'SAMPLEREDLN000048', 'available'),
    (49, 1, 'Camry', 2015, 24500.00, 84000, 'SAMPLEREDLN000049', 'available'),
    (50, 2, 'Mustang', 2016, 24850.00, 85800, 'SAMPLEREDLN000050', 'available'),
    (51, 3, 'Accord', 2017, 25200.00, 87600, 'SAMPLEREDLN000051', 'sold'),
    (52, 4, 'X5', 2018, 25550.00, 89400, 'SAMPLEREDLN000052', 'available'),
    (53, 5, 'Tahoe', 2019, 25900.00, 91200, 'SAMPLEREDLN000053', 'available'),
    (54, 1, 'Camry', 2020, 26250.00, 93000, 'SAMPLEREDLN000054', 'available'),
    (55, 2, 'Mustang', 2021, 26600.00, 94800, 'SAMPLEREDLN000055', 'available'),
    (56, 3, 'Accord', 2022, 26950.00, 96600, 'SAMPLEREDLN000056', 'available'),
    (57, 4, 'X5', 2023, 27300.00, 98400, 'SAMPLEREDLN000057', 'available'),
    (58, 5, 'Tahoe', 2024, 27650.00, 100200, 'SAMPLEREDLN000058', 'sold');

INSERT INTO test_drives (id, user_id, vehicle_id, scheduled_at, status) VALUES
    (1, 2, 1, '2026-11-05 10:00:00', 'pending'),
    (2, 2, 3, '2026-11-06 15:30:00', 'approved'),
    (3, 3, 2, '2026-11-07 11:00:00', 'approved'),
    (4, 4, 7, '2026-11-08 13:00:00', 'pending'),
    (5, 5, 4, '2026-11-09 09:30:00', 'cancelled'),
    (6, 3, 6, '2026-11-10 16:00:00', 'pending');

INSERT INTO reviews (id, user_id, vehicle_id, rating, comment, created_at) VALUES
    (1, 2, 1, 5, 'Smooth ride and honest seller.',               '2026-10-30 12:00:00'),
    (2, 3, 2, 4, 'Powerful truck, a few scratches on the bed.',  '2026-10-31 09:20:00'),
    (3, 4, 3, 5, 'Great fuel economy and clean interior.',       '2026-11-01 18:10:00'),
    (4, 5, 4, 3, 'Nice car, but the price is a bit high.',       '2026-11-01 19:45:00'),
    (5, 2, 7, 5, 'Sounds amazing, drives even better.',          '2026-11-02 08:30:00'),
    (6, 3, 5, 4, 'Comfortable for long trips.',                  '2026-11-02 10:15:00');
