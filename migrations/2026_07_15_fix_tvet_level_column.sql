-- Migration: 2026_07_15_fix_tvet_level_column.sql
-- Description: Changes tvet_level from a limited ENUM to VARCHAR to support
--              arbitrary TVET level values (Level 3, Level 4, Level 5, Level 6).
--              Also migrates existing values gracefully.
-- Created: 2026-07-15

-- Step 1: Change the column from ENUM to VARCHAR
ALTER TABLE `fundi_profiles` 
    MODIFY COLUMN `tvet_level` VARCHAR(50) NOT NULL DEFAULT 'None';

-- Step 2: Migrate legacy ENUM values to the new consistent format
UPDATE `fundi_profiles` SET `tvet_level` = 'None' WHERE `tvet_level` IN ('student', 'apprentice', 'master', '', 'Level 0', 'Level 1', 'Level 2');
