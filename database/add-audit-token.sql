-- ============================================================
-- Adds a persistent share token to website_audits so the full
-- report can be emailed and viewed without depending on session.
-- Run ONCE in phpMyAdmin on database: hopewwkz_tedmark
-- ============================================================

ALTER TABLE `website_audits`
  ADD COLUMN `token` VARCHAR(64) DEFAULT NULL AFTER `unlocked`,
  ADD UNIQUE KEY `token_unique` (`token`);
