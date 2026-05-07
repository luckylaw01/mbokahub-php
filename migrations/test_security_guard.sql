-- TEST QUERY: Adding a temporary column to verify the Safe Migration tool
-- Goal: See if 'ALTER' triggers the triple confirmation
ALTER TABLE `users` ADD COLUMN `test_security_flag` TINYINT(1) DEFAULT 0;

-- REVERSE QUERY: Removing the temporary column
-- Goal: Clean up after testing
ALTER TABLE `users` DROP COLUMN `test_security_flag`;
