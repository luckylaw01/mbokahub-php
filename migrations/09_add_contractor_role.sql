-- Phase 4: Add Contractor Role support
-- 1. Update users table role enum
ALTER TABLE `users` MODIFY `role` ENUM('hirer', 'fundi', 'admin', 'contractor') DEFAULT 'hirer';

-- 2. Create contractor_profiles table for business-specific details
CREATE TABLE IF NOT EXISTS `contractor_profiles` (
    `user_id` int(11) NOT NULL,
    `company_name` varchar(255) DEFAULT NULL,
    `reg_number` varchar(100) DEFAULT NULL,
    `kra_pin` varchar(50) DEFAULT NULL,
    `business_description` text DEFAULT NULL,
    `website_url` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    CONSTRAINT `fk_contractor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
