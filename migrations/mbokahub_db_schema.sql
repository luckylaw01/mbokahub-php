-- MbokaHub Database Schema Migration
-- Target: MySQL 8.x / MariaDB (XAMPP compatible)
-- Database: mbokahub_db

CREATE DATABASE IF NOT EXISTS `mbokahub_db` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;
USE `mbokahub_db`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_name` VARCHAR(50) UNIQUE NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `phone` VARCHAR(20),
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('hirer', 'fundi', 'admin') DEFAULT 'hirer',
    `language_pref` ENUM('en', 'sw') DEFAULT 'en',
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_role` (`role`),
    INDEX `idx_user_email` (`email`)
) ENGINE=InnoDB;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name_en` VARCHAR(100) NOT NULL,
    `name_sw` VARCHAR(100) NOT NULL,
    `icon_class` VARCHAR(50), -- FontAwesome class
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_category_name_en` (`name_en`),
    INDEX `idx_category_name_sw` (`name_sw`)
) ENGINE=InnoDB;

-- 3. Fundi Profiles Table (Extension of Users)
CREATE TABLE IF NOT EXISTS `fundi_profiles` (
    `user_id` INT PRIMARY KEY,
    `category_id` INT NULL,
    `avatar_url` VARCHAR(255),
    `cover_url` VARCHAR(255),
    `bio` TEXT,
    `location` VARCHAR(255),
    `tvet_level` ENUM('student', 'apprentice', 'master') DEFAULT 'student',
    `is_verified` BOOLEAN DEFAULT FALSE,
    `rating` DECIMAL(3,2) DEFAULT 0.00,
    `review_count` INT DEFAULT 0,
    CONSTRAINT `fk_fundi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fundi_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
    INDEX `idx_fundi_location` (`location`),
    INDEX `idx_fundi_rating` (`rating`),
    INDEX `idx_fundi_verified` (`is_verified`)
) ENGINE=InnoDB;

-- 4. Jobs Table
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `hirer_id` INT NOT NULL,
    `assigned_fundi_id` INT NULL,
    `category_id` INT NULL,
    `status` ENUM('open', 'direct_request', 'in_progress', 'completed', 'cancelled') DEFAULT 'open',
    `description` TEXT NOT NULL,
    `location` VARCHAR(255),
    `budget_expectation` DECIMAL(10,2),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_job_hirer` FOREIGN KEY (`hirer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_job_fundi` FOREIGN KEY (`assigned_fundi_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_job_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
    INDEX `idx_job_status` (`status`),
    INDEX `idx_job_location` (`location`),
    INDEX `idx_job_created_at` (`created_at`)
) ENGINE=InnoDB;

-- 5. Job Bids Table
CREATE TABLE IF NOT EXISTS `job_bids` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT NOT NULL,
    `fundi_id` INT NOT NULL,
    `proposal_text` TEXT,
    `status` ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_bid_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bid_fundi` FOREIGN KEY (`fundi_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_bid_status` (`status`)
) ENGINE=InnoDB;

-- 6. Reviews Table
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT NOT NULL,
    `reviewer_id` INT NOT NULL,
    `reviewee_id` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_review_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_review_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_review_reviewee` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_review_rating` (`rating`)
) ENGINE=InnoDB;

-- Seed Initial Categories
INSERT INTO `categories` (`name_en`, `name_sw`, `icon_class`) VALUES 
('Plumbing', 'Plumbing', 'fa-faucet'),
('Electrical', 'Umeme', 'fa-bolt'),
('Masonry', 'Ujenzi', 'fa-trowel-bricks'),
('Carpentry', 'Useremala', 'fa-hammer'),
('Painting', 'Upakaji Rangi', 'fa-paint-roller');
