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
    (8, 3, 'CR-V',       2018, 21300.00, 48700, '2HKRW2H59JH000008', 'sold');

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
