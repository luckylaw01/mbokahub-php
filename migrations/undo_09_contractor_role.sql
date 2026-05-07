-- Undo Phase 4 Changes
-- 1. Revert users table role enum (Removes 'contractor' role)
-- WARNING: If any user already has the 'contractor' role, they will be reverted to 'hirer' (default)
ALTER TABLE `users` MODIFY `role` ENUM('hirer', 'fundi', 'admin') DEFAULT 'hirer';

-- 2. Drop contractor_profiles table
DROP TABLE IF EXISTS `contractor_profiles`;
