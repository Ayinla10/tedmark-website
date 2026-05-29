-- Tedmark Digital Agency - Database Schema
-- MySQL 8.0+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `tedmark_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tedmark_db`;

-- Admins
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','editor') DEFAULT 'admin',
  `avatar` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: admin@tedmark.com / Admin@2024
INSERT INTO `admins` (`name`, `email`, `password`, `role`) VALUES
('Tedmark Admin', 'admin@tedmark.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');

-- Blog Categories
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `color` varchar(20) DEFAULT '#2563eb',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`name`, `slug`, `description`, `color`) VALUES
('Business Systems', 'business-systems', 'Articles about business systems and operations', '#2563eb'),
('Automation', 'automation', 'Business automation insights', '#7c3aed'),
('Digital Marketing', 'digital-marketing', 'Digital marketing strategies', '#059669'),
('Web Development', 'web-development', 'Website and web app development', '#d97706'),
('Business Growth', 'business-growth', 'Growing your African business', '#dc2626');

-- Blog Posts
CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(280) NOT NULL UNIQUE,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `fk_posts_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_posts_author` FOREIGN KEY (`author_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Portfolio Projects
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(280) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `images` json DEFAULT NULL,
  `category` enum('websites','systems','automation','ecommerce','branding') NOT NULL,
  `technologies` json DEFAULT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `client_industry` varchar(100) DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `case_study_url` varchar(255) DEFAULT NULL,
  `status` enum('active','draft','archived') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `results` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Testimonials
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(150) NOT NULL,
  `client_title` varchar(150) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `rating` tinyint(1) DEFAULT 5,
  `video_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `testimonials` (`client_name`, `client_title`, `company`, `industry`, `content`, `rating`, `featured`) VALUES
('Amara Osei', 'CEO', 'Osei Enterprises', 'Retail', 'Tedmark transformed our entire business operations. We went from manual spreadsheets to a fully automated system in 6 weeks. Revenue increased by 40% in the first quarter.', 5, 1),
('Dr. Fatima Diallo', 'Director', 'HealthFirst Clinic', 'Healthcare', 'Our patient management system is now completely digital. Appointment bookings, medical records, billing — all automated. The team at Tedmark understood exactly what we needed.', 5, 1),
('Kwame Mensah', 'Founder', 'SwiftLogix', 'Logistics', 'The custom logistics platform Tedmark built for us handles 500+ deliveries daily without any manual intervention. Exceptional work.', 5, 1),
('Aisha Bello', 'Operations Manager', 'EduBright Schools', 'Education', 'From student enrollment to fee management to parent communication — everything is now automated. Our administrative work reduced by 70%.', 5, 0),
('Chidi Okafor', 'MD', 'Okafor Trading Co.', 'SME', 'The e-commerce platform and inventory system Tedmark built has allowed us to sell across Africa. Game changer for our business.', 5, 1);

-- Consultations / Bookings
CREATE TABLE `consultations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `company_size` varchar(50) DEFAULT NULL,
  `service_interest` varchar(255) DEFAULT NULL,
  `current_challenge` text DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` varchar(30) DEFAULT NULL,
  `meeting_type` enum('video_call','phone_call','in_person') DEFAULT 'video_call',
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Resources / Downloads
CREATE TABLE `resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` varchar(30) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `download_count` int(11) DEFAULT 0,
  `status` enum('active','draft') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Resource Downloads (email capture)
CREATE TABLE `resource_downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `company` varchar(150) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `downloaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `resource_id` (`resource_id`),
  CONSTRAINT `fk_downloads_resource` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact Messages
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied','archived') DEFAULT 'unread',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Leads / Newsletter
CREATE TABLE `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `phone` varchar(30) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `status` enum('new','qualified','converted','unsubscribed') DEFAULT 'new',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Business Health Checker Results
CREATE TABLE `health_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `answers` json NOT NULL,
  `score` int(11) DEFAULT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `recommendations` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Settings
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL UNIQUE,
  `value` text DEFAULT NULL,
  `group` varchar(50) DEFAULT 'general',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`key`, `value`, `group`) VALUES
('site_name', 'Tedmark Digital Agency', 'general'),
('site_tagline', 'Helping Businesses Run Smarter With Technology', 'general'),
('site_email', 'hello@tedmark.com', 'general'),
('site_phone', '+234 800 000 0000', 'general'),
('site_address', 'Lagos, Nigeria', 'general'),
('social_twitter', 'https://twitter.com/tedmarkdigital', 'social'),
('social_linkedin', 'https://linkedin.com/company/tedmarkdigital', 'social'),
('social_instagram', 'https://instagram.com/tedmarkdigital', 'social'),
('social_facebook', 'https://facebook.com/tedmarkdigital', 'social'),
('smtp_host', '', 'email'),
('smtp_port', '587', 'email'),
('smtp_user', '', 'email'),
('smtp_pass', '', 'email'),
('notification_email', 'admin@tedmark.com', 'email'),
('google_analytics', '', 'analytics'),
('stats_projects', '150+', 'stats'),
('stats_clients', '80+', 'stats'),
('stats_countries', '8', 'stats'),
('stats_satisfaction', '98%', 'stats');
