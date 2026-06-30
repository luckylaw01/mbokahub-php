-- Migration: 12_sync_hosted_schema.sql
-- Description: Brings the hosted database schema up-to-date with local mbokahub_db_schema.sql.
-- Handles nullable timestamp adjustments, missing columns in institutions, and creates saved_jobs.

-- 1. Fix certifications created_at column nullability
UPDATE `certifications` SET `created_at` = CURRENT_TIMESTAMP WHERE `created_at` IS NULL;
ALTER TABLE `certifications` MODIFY `created_at` timestamp NOT NULL DEFAULT current_timestamp();

-- 2. Fix experiences created_at column nullability
UPDATE `experiences` SET `created_at` = CURRENT_TIMESTAMP WHERE `created_at` IS NULL;
ALTER TABLE `experiences` MODIFY `created_at` timestamp NOT NULL DEFAULT current_timestamp();

-- 3. Fix gigs created_at column nullability
UPDATE `gigs` SET `created_at` = CURRENT_TIMESTAMP WHERE `created_at` IS NULL;
ALTER TABLE `gigs` MODIFY `created_at` timestamp NOT NULL DEFAULT current_timestamp();

-- 4. Fix institutions missing columns (contact_email, logo_url)
ALTER TABLE `institutions`
  ADD COLUMN `contact_email` varchar(255) DEFAULT NULL AFTER `website`,
  ADD COLUMN `logo_url` varchar(255) DEFAULT NULL AFTER `contact_email`;

-- 5. Fix portfolio_items created_at column nullability
UPDATE `portfolio_items` SET `created_at` = CURRENT_TIMESTAMP WHERE `created_at` IS NULL;
ALTER TABLE `portfolio_items` MODIFY `created_at` timestamp NOT NULL DEFAULT current_timestamp();

-- 6. Create saved_jobs table (if not exists)
CREATE TABLE IF NOT EXISTS `saved_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_save` (`user_id`, `job_id`),
  KEY `fk_saved_job` (`job_id`),
  CONSTRAINT `fk_saved_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_saved_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
