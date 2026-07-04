-- ============================================================
-- Tedmark Digital Agency — Leads & Consultations Migration
-- Run ONCE in phpMyAdmin on database: hopewwkz_tedmark
-- Fixes: newsletter signup (api/subscribe.php) and consultation
-- booking (consultation.php) were silently failing because these
-- tables never existed.
-- ============================================================

CREATE TABLE IF NOT EXISTS `leads` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(150) DEFAULT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `company`    VARCHAR(150) DEFAULT NULL,
  `source`     VARCHAR(50)  DEFAULT 'website',
  `status`     ENUM('new','contacted','converted') DEFAULT 'new',
  `ip_address` VARCHAR(45)  DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `consultations` (
  `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`              VARCHAR(150) NOT NULL,
  `email`             VARCHAR(150) NOT NULL,
  `phone`             VARCHAR(50)  DEFAULT NULL,
  `business_name`     VARCHAR(150) DEFAULT NULL,
  `industry`          VARCHAR(100) DEFAULT NULL,
  `package_interest`  VARCHAR(150) DEFAULT NULL,
  `main_challenge`    TEXT DEFAULT NULL,
  `status`            ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `resources` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`           VARCHAR(200) NOT NULL,
  `description`     TEXT DEFAULT NULL,
  `category`        VARCHAR(100) DEFAULT NULL,
  `file_path`       VARCHAR(255) DEFAULT NULL,
  `file_type`       VARCHAR(20)  DEFAULT NULL,
  `file_size`       VARCHAR(20)  DEFAULT NULL,
  `download_count`  INT UNSIGNED DEFAULT 0,
  `featured`        TINYINT(1) DEFAULT 0,
  `status`          ENUM('active','draft') DEFAULT 'active',
  `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
