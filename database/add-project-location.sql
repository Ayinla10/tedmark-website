-- ============================================================
-- Add a `location` field to projects, so client locations can
-- be shown naturally on the portfolio (per user request: show
-- global reach through real client locations, not blanket
-- "African businesses" copy).
-- Run ONCE in phpMyAdmin on database: hopewwkz_tedmark
-- ============================================================

ALTER TABLE `projects`
  ADD COLUMN `location` VARCHAR(100) DEFAULT NULL AFTER `client`;
