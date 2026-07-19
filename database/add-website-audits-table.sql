-- ============================================================
-- Website Audit tool: stores lead-gated audit results.
-- Run ONCE in phpMyAdmin on database: hopewwkz_tedmark
-- ============================================================

CREATE TABLE IF NOT EXISTS `website_audits` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `target_url`  VARCHAR(500) NOT NULL,
  `email`       VARCHAR(150) DEFAULT NULL,
  `name`        VARCHAR(150) DEFAULT NULL,
  `score`       TINYINT UNSIGNED DEFAULT NULL,
  `results`     LONGTEXT DEFAULT NULL,
  `unlocked`    TINYINT(1) DEFAULT 0,
  `ip_address`  VARCHAR(64) DEFAULT NULL,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
