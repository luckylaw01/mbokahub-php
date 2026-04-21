-- Add remember_token to users table for "Remember Me" functionality
USE `mbokahub_db`;
ALTER TABLE `users` ADD COLUMN `remember_token` VARCHAR(100) NULL AFTER `language_pref`;
