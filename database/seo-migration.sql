-- ============================================================
-- Tedmark Digital Agency — SEO System Migration
-- Run this ONCE in phpMyAdmin on database: hopewwkz_tedmark
-- ============================================================

-- ─────────────────────────────────────────
-- PER-PAGE SEO TABLE (static pages)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `seo_pages` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `page_key`         VARCHAR(100) NOT NULL UNIQUE,
  `meta_title`       VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `meta_keywords`    VARCHAR(500) DEFAULT NULL,
  `og_image`         VARCHAR(500) DEFAULT NULL,
  `canonical_url`    VARCHAR(500) DEFAULT NULL,
  `no_index`         TINYINT(1) DEFAULT 0,
  `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default page keys
INSERT IGNORE INTO `seo_pages` (`page_key`) VALUES
('home'),('about'),('services'),('portfolio'),('blog'),('contact'),('industries'),('resources');

-- ─────────────────────────────────────────
-- ADD SEO COLUMNS TO POSTS
-- ─────────────────────────────────────────
ALTER TABLE `posts`
  ADD COLUMN `seo_title`       VARCHAR(255) DEFAULT NULL,
  ADD COLUMN `seo_description` TEXT DEFAULT NULL,
  ADD COLUMN `focus_keyword`   VARCHAR(150) DEFAULT NULL,
  ADD COLUMN `og_image`        VARCHAR(255) DEFAULT NULL,
  ADD COLUMN `canonical_url`   VARCHAR(500) DEFAULT NULL,
  ADD COLUMN `no_index`        TINYINT(1) DEFAULT 0;

-- ─────────────────────────────────────────
-- ADD SEO COLUMNS TO PROJECTS
-- ─────────────────────────────────────────
ALTER TABLE `projects`
  ADD COLUMN `seo_title`       VARCHAR(255) DEFAULT NULL,
  ADD COLUMN `seo_description` TEXT DEFAULT NULL,
  ADD COLUMN `og_image`        VARCHAR(255) DEFAULT NULL;

-- ─────────────────────────────────────────
-- GLOBAL SEO SETTINGS
-- ─────────────────────────────────────────
INSERT IGNORE INTO `settings` (`key`, `value`, `group`) VALUES
('seo_title_template',       '{title} | Tedmark Digital Agency',   'seo'),
('seo_default_description',  'Tedmark Digital Agency helps African businesses grow with custom web development, business automation, and IT consulting. Based in Accra, Ghana.', 'seo'),
('seo_default_keywords',     'digital agency Ghana, web development Accra, business automation Ghana, IT consulting Accra, website design Ghana, software company Accra', 'seo'),
('seo_default_og_image',     '',   'seo'),
('seo_ga4_id',               '',   'seo'),
('seo_gsc_verification',     '',   'seo'),
('seo_bing_verification',    '',   'seo'),
('seo_twitter_handle',       '',   'seo'),
('schema_city',              'Accra',          'schema'),
('schema_region',            'Greater Accra',  'schema'),
('schema_address',           '',               'schema'),
('schema_lat',               '5.6037',         'schema'),
('schema_lng',               '-0.1870',        'schema'),
('schema_price_range',       '$$',             'schema'),
('schema_opens',             '08:00',          'schema'),
('schema_closes',            '18:00',          'schema'),
('robots_txt_content',       'User-agent: *\r\nAllow: /\r\nDisallow: /admin/\r\nDisallow: /includes/\r\nDisallow: /database/\r\n\r\nSitemap: https://new.tedmarkdigital.com/sitemap.xml.php', 'seo');
