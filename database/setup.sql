-- ============================================================
-- Tedmark Digital Agency — Full Database Setup
-- Run this in phpMyAdmin on database: hopewwkz_tedmark
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ─────────────────────────────────────────
-- ADMIN USERS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('admin','editor') DEFAULT 'admin',
  `avatar`     VARCHAR(255) DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: email=admin@tedmarkdigital.com password=Admin@1234
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Tedmark Admin', 'admin@tedmarkdigital.com', '$2y$12$Y5Q1Q2Q3Q4Q5Q6Q7Q8Q9QuZkL8vXwNpA7mR3tS6yD0fH1gI2jK4lM', 'admin');

-- ─────────────────────────────────────────
-- SETTINGS (key-value store)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key`        VARCHAR(100) NOT NULL UNIQUE,
  `value`      TEXT DEFAULT NULL,
  `group`      VARCHAR(50) DEFAULT 'general',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`key`, `value`, `group`) VALUES
('site_name',        'Tedmark Digital Agency',         'general'),
('site_tagline',     'We Build Systems. We Automate Work. We Grow Businesses.', 'general'),
('site_email',       'hello@tedmarkdigital.com',       'general'),
('site_phone',       '+233 XX XXX XXXX',               'general'),
('site_address',     'Accra, Ghana',                   'general'),
('site_logo',        '/assets/images/tedmark logo copy2.png', 'general'),
('hero_badge',       'Helping Businesses Run Smarter', 'homepage'),
('hero_h1_line1',    'We Build Systems.',              'homepage'),
('hero_h1_line2',    'We Automate Work.',              'homepage'),
('hero_h1_line3',    'We Grow Businesses.',            'homepage'),
('hero_subtext',     'From custom websites to full business automation — we help businesses work smarter, faster, and more profitably.', 'homepage'),
('hero_btn_primary', 'Book a Free Strategy Session',  'homepage'),
('hero_btn_secondary','See Our Work',                  'homepage'),
('stat_1_value',     '80+',                            'homepage'),
('stat_1_label',     'Projects Delivered',             'homepage'),
('stat_2_value',     '95%',                            'homepage'),
('stat_2_label',     'Client Satisfaction',            'homepage'),
('stat_3_value',     '8',                              'homepage'),
('stat_3_label',     'Industries Served',              'homepage'),
('stat_4_value',     '3yrs',                           'homepage'),
('stat_4_label',     'In Business',                    'homepage'),
('cta_heading',      'Ready to Transform Your Business?', 'cta'),
('cta_subtext',      'Let\'s build something that works for you — systems, websites, automation, and more.', 'cta'),
('cta_btn_primary',  'Book a Free Strategy Session',  'cta'),
('cta_btn_secondary','Talk to an Expert',              'cta'),
('footer_tagline',   'Building smarter businesses through technology, automation, and strategy.', 'footer'),
('social_twitter',   '#',                              'social'),
('social_linkedin',  '#',                              'social'),
('social_instagram', '#',                              'social'),
('social_facebook',  '#',                              'social'),
('google_analytics', '',                               'general'),
('consultation_url', '/consultation.php',              'general');

-- ─────────────────────────────────────────
-- BLOG POSTS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `posts` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`         VARCHAR(255) NOT NULL,
  `slug`          VARCHAR(255) NOT NULL UNIQUE,
  `excerpt`       TEXT DEFAULT NULL,
  `body`          LONGTEXT DEFAULT NULL,
  `category`      VARCHAR(100) DEFAULT NULL,
  `tags`          VARCHAR(255) DEFAULT NULL,
  `featured_image`VARCHAR(255) DEFAULT NULL,
  `has_audio`     TINYINT(1) DEFAULT 0,
  `audio_url`     VARCHAR(255) DEFAULT NULL,
  `author_id`     INT UNSIGNED DEFAULT 1,
  `status`        ENUM('draft','published') DEFAULT 'draft',
  `views`         INT UNSIGNED DEFAULT 0,
  `read_time`     INT UNSIGNED DEFAULT 5,
  `published_at`  DATETIME DEFAULT NULL,
  `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- PORTFOLIO PROJECTS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `projects` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(255) NOT NULL,
  `slug`        VARCHAR(255) NOT NULL UNIQUE,
  `client`      VARCHAR(150) DEFAULT NULL,
  `category`    VARCHAR(100) DEFAULT NULL,
  `tags`        VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `challenge`   TEXT DEFAULT NULL,
  `solution`    TEXT DEFAULT NULL,
  `result`      VARCHAR(150) DEFAULT NULL,
  `icon`        VARCHAR(100) DEFAULT 'fa-solid fa-briefcase',
  `color`       VARCHAR(20)  DEFAULT '#22c55e',
  `bg`          VARCHAR(255) DEFAULT 'linear-gradient(135deg,#0f172a,#1e3a5f)',
  `cover_image` VARCHAR(255) DEFAULT NULL,
  `year`        VARCHAR(10)  DEFAULT NULL,
  `status`      ENUM('active','draft') DEFAULT 'active',
  `sort_order`  INT UNSIGNED DEFAULT 0,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- SERVICES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `services` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(150) NOT NULL,
  `slug`        VARCHAR(150) NOT NULL UNIQUE,
  `icon`        VARCHAR(100) DEFAULT 'fa-solid fa-star',
  `color`       VARCHAR(20)  DEFAULT '#22c55e',
  `description` TEXT DEFAULT NULL,
  `features`    TEXT DEFAULT NULL,
  `status`      ENUM('active','draft') DEFAULT 'active',
  `sort_order`  INT UNSIGNED DEFAULT 0,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `services` (`title`, `slug`, `icon`, `color`, `description`, `features`, `sort_order`) VALUES
('AI Agent Development', 'ai-agent-development', 'fa-solid fa-robot', '#22c55e', 'Build intelligent AI agents for customer support, internal operations, documents, voice, chat, and workflow automation.', '', 1),
('AI Operating System', 'ai-operating-system', 'fa-solid fa-server', '#a78bfa', 'Deploy, manage, monitor, and govern every AI agent, workflow, and business knowledge base from one central platform.', '', 2),
('AI Marketing', 'ai-marketing', 'fa-solid fa-bullhorn', '#f43f5e', 'Automate lead generation, CRM, outreach, content, and customer engagement with AI-powered marketing systems.', '', 3),
('AI Strategy & Consulting', 'ai-strategy-consulting', 'fa-solid fa-compass', '#f59e0b', 'Identify high-impact AI opportunities, define implementation roadmaps, and guide your organization from idea to deployment.', '', 4),
('AI Adoption & Training', 'ai-adoption-training', 'fa-solid fa-graduation-cap', '#14b8a6', 'Equip your teams with practical AI skills, playbooks, and workflows that drive real adoption and measurable productivity.', '', 5),
('Web/App Development', 'web-app-development', 'fa-solid fa-code', '#60a5fa', 'Design and build AI-powered web and mobile applications using modern technologies like React, Next.js, React Native, etc.', '', 6);

-- ─────────────────────────────────────────
-- TEAM MEMBERS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `team_members` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(150) NOT NULL,
  `role`       VARCHAR(150) DEFAULT NULL,
  `bio`        TEXT DEFAULT NULL,
  `avatar`     VARCHAR(255) DEFAULT NULL,
  `linkedin`   VARCHAR(255) DEFAULT NULL,
  `twitter`    VARCHAR(255) DEFAULT NULL,
  `status`     ENUM('active','hidden') DEFAULT 'active',
  `sort_order` INT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- TESTIMONIALS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(150) NOT NULL,
  `company`    VARCHAR(150) DEFAULT NULL,
  `role`       VARCHAR(150) DEFAULT NULL,
  `quote`      TEXT NOT NULL,
  `avatar`     VARCHAR(255) DEFAULT NULL,
  `rating`     TINYINT UNSIGNED DEFAULT 5,
  `status`     ENUM('active','hidden') DEFAULT 'active',
  `sort_order` INT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- INDUSTRIES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `industries` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(150) NOT NULL,
  `icon`        VARCHAR(100) DEFAULT 'fa-solid fa-building',
  `color`       VARCHAR(20)  DEFAULT '#22c55e',
  `description` TEXT DEFAULT NULL,
  `status`      ENUM('active','hidden') DEFAULT 'active',
  `sort_order`  INT UNSIGNED DEFAULT 0,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- CONTACT MESSAGES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `messages` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(150) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `phone`      VARCHAR(50)  DEFAULT NULL,
  `subject`    VARCHAR(255) DEFAULT NULL,
  `message`    TEXT NOT NULL,
  `service`    VARCHAR(100) DEFAULT NULL,
  `status`     ENUM('unread','read','replied') DEFAULT 'unread',
  `ip`         VARCHAR(45)  DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
