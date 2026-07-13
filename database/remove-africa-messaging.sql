-- ============================================================
-- Tedmark Digital Agency — Broaden messaging beyond Africa
-- Run ONCE in phpMyAdmin on database: hopewwkz_tedmark
--
-- Keeps Accra/Ghana as factual location info (address, schema,
-- local SEO), but removes "African businesses" / "across Africa"
-- audience-limiting language from settings and SEO copy that was
-- already seeded into the live database.
-- ============================================================

-- ── Homepage / footer settings ─────────────────────────
UPDATE `settings` SET `value` = 'Helping Businesses Run Smarter'
  WHERE `key` = 'hero_badge';

UPDATE `settings` SET `value` = 'From custom websites to full business automation — we help businesses work smarter, faster, and more profitably.'
  WHERE `key` = 'hero_subtext';

UPDATE `settings` SET `value` = 'Building smarter businesses through technology, automation, and strategy.'
  WHERE `key` = 'footer_tagline';

-- ── Global SEO description ─────────────────────────────
UPDATE `settings` SET `value` = 'Tedmark Digital Agency helps businesses grow with custom web development, business automation, and IT consulting. Based in Accra, Ghana.'
  WHERE `key` = 'seo_default_description';

-- ── Per-page SEO (seo_pages table) ─────────────────────
UPDATE `seo_pages` SET
  `meta_description` = 'Tedmark Digital Agency builds custom websites, business systems, and automation solutions for growing businesses. Based in Accra, Ghana. Book a free consultation today.'
  WHERE `page_key` = 'home';

UPDATE `seo_pages` SET
  `meta_description` = 'Learn about Tedmark Digital Agency — a technology company in Accra, Ghana helping businesses grow with custom software, websites, and automation since 2019.'
  WHERE `page_key` = 'about';

UPDATE `seo_pages` SET
  `meta_description` = 'Tedmark Digital offers web development, business automation, ERP systems, e-commerce, digital marketing, and IT consulting for growing businesses.'
  WHERE `page_key` = 'services';

UPDATE `seo_pages` SET
  `meta_title`       = 'Our Work — Web & Software Projects for Growing Businesses | Tedmark Digital',
  `meta_keywords`    = 'web design portfolio Ghana, software projects, Tedmark Digital work, website examples Ghana, business systems Ghana'
  WHERE `page_key` = 'portfolio';

UPDATE `seo_pages` SET
  `meta_title`       = 'Business Technology Blog for Entrepreneurs | Tedmark Digital Ghana',
  `meta_description` = 'Practical guides, case studies, and insights on web development, automation, and business systems for business owners. Written by Tedmark Digital, Accra.',
  `meta_keywords`    = 'business technology Ghana, tech blog, web development tips Ghana, business automation guide, entrepreneur blog'
  WHERE `page_key` = 'blog';

UPDATE `seo_pages` SET
  `meta_description` = 'Tedmark Digital builds technology solutions for education, healthcare, retail, logistics, NGOs, SMEs, and more. Sector-specific expertise, based in Ghana.'
  WHERE `page_key` = 'industries';

UPDATE `seo_pages` SET
  `meta_title`       = 'Free Business Technology Resources | Tedmark Digital Ghana',
  `meta_keywords`    = 'free business resources Ghana, technology guides, business templates Ghana, digital transformation resources'
  WHERE `page_key` = 'resources';
