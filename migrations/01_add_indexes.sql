-- MbokaHub Database Indexing Migration
-- Use this file if you have already created the tables and want to add indexes for performance.
-- Target: MySQL 8.x / MariaDB (XAMPP compatible)
-- Database: mbokahub_db

USE `mbokahub_db`;

-- 1. Users Table Indexes
-- Speeds up login by email and dashboard role filtering
ALTER TABLE `users` ADD INDEX `idx_user_role` (`role`);
-- Note: email is already UNIQUE so it's already indexed by default, 
-- but we can explicitly index phone if needed for search.
ALTER TABLE `users` ADD INDEX `idx_user_phone` (`phone`);

-- 2. Categories Table Indexes
-- Speeds up bilingual category searches
ALTER TABLE `categories` ADD INDEX `idx_category_name_en` (`name_en`);
ALTER TABLE `categories` ADD INDEX `idx_category_name_sw` (`name_sw`);

-- 3. Fundi Profiles Table Indexes
-- Critical for the "Find Your Expert Fundi" search and discovery feed
ALTER TABLE `fundi_profiles` ADD INDEX `idx_fundi_location` (`location`);
ALTER TABLE `fundi_profiles` ADD INDEX `idx_fundi_rating` (`rating`);
ALTER TABLE `fundi_profiles` ADD INDEX `idx_fundi_verified` (`is_verified`);

-- 4. Jobs Table Indexes
-- Key for the public job feed and status-based dashboards
ALTER TABLE `jobs` ADD INDEX `idx_job_status` (`status`);
ALTER TABLE `jobs` ADD INDEX `idx_job_location` (`location`);
ALTER TABLE `jobs` ADD INDEX `idx_job_created_at` (`created_at`);

-- 5. Job Bids Table Indexes
-- Speeds up listing bids for specific statuses (e.g., pending)
ALTER TABLE `job_bids` ADD INDEX `idx_bid_status` (`status`);

-- 6. Reviews Table Indexes
-- Speeds up rating calculations and profile displays
ALTER TABLE `reviews` ADD INDEX `idx_review_rating` (`rating`);
