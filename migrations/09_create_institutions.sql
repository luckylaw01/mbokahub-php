-- =============================================
-- Migration 09: Institutions Table & Fundi Link
-- Execute this on the hosted database
-- =============================================

-- 1. Create the institutions table
CREATE TABLE IF NOT EXISTS `institutions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `type` enum('TVET', 'University', 'College', 'Vocational') DEFAULT 'TVET',
    `location` varchar(255) DEFAULT NULL,
    `website` varchar(255) DEFAULT NULL,
    `is_partner` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Link fundi_profiles to institutions
-- We check for column existence first (standard SQL ALTER)
ALTER TABLE `fundi_profiles` ADD COLUMN `institution_id` int(11) DEFAULT NULL AFTER `category_id`;

-- 3. Add Foreign Key Constraint
ALTER TABLE `fundi_profiles` 
ADD CONSTRAINT `fk_fundi_institution` 
FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) 
ON DELETE SET NULL;

-- 4. Seed initial TVET data
INSERT INTO `institutions` (`name`, `type`, `location`, `website`, `is_partner`) VALUES
('Nairobi Technical Training Institute', 'TVET', 'Nairobi', 'https://nairobi-tti.ac.ke', 1),
('Mukurwe-ini Technical and Training Institute', 'TVET', 'Nyeri', 'https://mTTI.ac.ke', 1),
('Kabete National Polytechnic', 'TVET', 'Kabete', 'https://kabetepoly.ac.ke', 1),
('PC Kinyanjui Technical Training Institute', 'TVET', 'Nairobi', 'https://pckinyanjui.ac.ke', 1);
