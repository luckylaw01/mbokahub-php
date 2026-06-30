-- Migration: 13_add_resume_to_fundi.sql
-- Description: Adds a resume_url column to fundi_profiles table to store CV/resume documents.

ALTER TABLE `fundi_profiles` ADD COLUMN `resume_url` varchar(255) DEFAULT NULL;
