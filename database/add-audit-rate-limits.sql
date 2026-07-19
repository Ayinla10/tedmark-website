-- ============================================================
-- Rate limiting for the Website Audit tool (scans, OTP emails,
-- AI report generation all cost real money/time per request).
-- Run ONCE in phpMyAdmin on database: hopewwkz_tedmark
-- ============================================================

CREATE TABLE IF NOT EXISTS `audit_rate_limits` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip_address`  VARCHAR(64) NOT NULL,
  `action`      VARCHAR(30) NOT NULL,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `ip_action_time` (`ip_address`, `action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
