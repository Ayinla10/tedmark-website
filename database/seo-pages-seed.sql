-- ============================================================
-- Tedmark Digital Agency — SEO Pages Seed (Ghana-targeted)
-- Run ONCE in phpMyAdmin on database: hopewwkz_tedmark
-- ============================================================

INSERT INTO `seo_pages` (`page_key`, `meta_title`, `meta_description`, `meta_keywords`) VALUES

('home',
 'Digital Agency in Accra Ghana | Web Design, Automation & Business Systems — Tedmark',
 'Tedmark Digital Agency builds custom websites, business systems, and automation solutions for companies in Ghana and across Africa. Based in Accra. Book a free consultation today.',
 'digital agency Ghana, web design Accra, business automation Ghana, website design Ghana, software company Accra, IT company Ghana')

ON DUPLICATE KEY UPDATE
  `meta_title`       = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`),
  `meta_keywords`    = VALUES(`meta_keywords`);

INSERT INTO `seo_pages` (`page_key`, `meta_title`, `meta_description`, `meta_keywords`) VALUES

('about',
 'About Tedmark Digital Agency | IT & Web Development Company in Accra, Ghana',
 'Learn about Tedmark Digital Agency — a technology company in Accra, Ghana helping African businesses grow with custom software, websites, and automation since 2019.',
 'about Tedmark Digital, IT company Accra Ghana, tech agency Ghana, web development company Ghana, software company Accra')

ON DUPLICATE KEY UPDATE
  `meta_title`       = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`),
  `meta_keywords`    = VALUES(`meta_keywords`);

INSERT INTO `seo_pages` (`page_key`, `meta_title`, `meta_description`, `meta_keywords`) VALUES

('services',
 'Web Design, Business Systems & Automation Services in Ghana | Tedmark Digital',
 'Tedmark Digital offers web development, business automation, ERP systems, e-commerce, digital marketing, and IT consulting for businesses in Ghana and across Africa.',
 'web design Ghana, business systems Ghana, ERP software Accra, automation Ghana, e-commerce Ghana, digital marketing Accra, IT consulting Ghana')

ON DUPLICATE KEY UPDATE
  `meta_title`       = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`),
  `meta_keywords`    = VALUES(`meta_keywords`);

INSERT INTO `seo_pages` (`page_key`, `meta_title`, `meta_description`, `meta_keywords`) VALUES

('portfolio',
 'Our Work — Web & Software Projects for African Businesses | Tedmark Digital Ghana',
 'See the websites, business systems, e-commerce stores, and automation tools Tedmark Digital has built for clients across Ghana, Nigeria, Kenya, and beyond.',
 'web design portfolio Ghana, software projects Africa, Tedmark Digital work, website examples Ghana, business systems Ghana')

ON DUPLICATE KEY UPDATE
  `meta_title`       = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`),
  `meta_keywords`    = VALUES(`meta_keywords`);

INSERT INTO `seo_pages` (`page_key`, `meta_title`, `meta_description`, `meta_keywords`) VALUES

('blog',
 'Business Technology Blog for African Entrepreneurs | Tedmark Digital Ghana',
 'Practical guides, case studies, and insights on web development, automation, and business systems for African business owners. Written by Tedmark Digital, Accra.',
 'business technology Ghana, tech blog Africa, web development tips Ghana, business automation guide, African entrepreneur blog')

ON DUPLICATE KEY UPDATE
  `meta_title`       = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`),
  `meta_keywords`    = VALUES(`meta_keywords`);

INSERT INTO `seo_pages` (`page_key`, `meta_title`, `meta_description`, `meta_keywords`) VALUES

('contact',
 'Contact Tedmark Digital Agency | Web Design & IT Company in Accra, Ghana',
 'Get in touch with Tedmark Digital Agency in Accra, Ghana. Book a free consultation for web design, business systems, or automation. We reply within 24 hours.',
 'contact Tedmark Digital, digital agency Accra contact, web design Ghana contact, IT company Ghana phone, book consultation Ghana')

ON DUPLICATE KEY UPDATE
  `meta_title`       = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`),
  `meta_keywords`    = VALUES(`meta_keywords`);

INSERT INTO `seo_pages` (`page_key`, `meta_title`, `meta_description`, `meta_keywords`) VALUES

('industries',
 'Industries We Serve in Ghana | Education, Healthcare, Retail & More | Tedmark Digital',
 'Tedmark Digital builds technology solutions for education, healthcare, retail, logistics, NGOs, SMEs, and more across Ghana and Africa. Sector-specific expertise.',
 'tech solutions Ghana industries, education software Ghana, healthcare system Ghana, retail software Accra, logistics software Ghana')

ON DUPLICATE KEY UPDATE
  `meta_title`       = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`),
  `meta_keywords`    = VALUES(`meta_keywords`);

INSERT INTO `seo_pages` (`page_key`, `meta_title`, `meta_description`, `meta_keywords`) VALUES

('resources',
 'Free Business Technology Resources for African Businesses | Tedmark Digital Ghana',
 'Download free guides, templates, and tools to help your business run smarter with technology. From Tedmark Digital Agency, Accra, Ghana.',
 'free business resources Ghana, technology guides Africa, business templates Ghana, digital transformation resources')

ON DUPLICATE KEY UPDATE
  `meta_title`       = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`),
  `meta_keywords`    = VALUES(`meta_keywords`);
