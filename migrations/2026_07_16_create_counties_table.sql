-- Migration: 2026_07_16_create_counties_table.sql
-- Description: Creates the counties table with all 47 Kenyan counties,
--              their county codes, and official administrative regions.
-- Created: 2026-07-16
-- Source: Kenya Constitution 2010, Schedule 1 (County Government Act)

CREATE TABLE IF NOT EXISTS `counties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) NOT NULL COMMENT 'Official GoK county code (1-47)',
  `name` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL COMMENT 'Official administrative region grouping',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_counties_code` (`code`),
  UNIQUE KEY `uq_counties_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- SEED: All 47 Counties of Kenya
-- Regions: Nairobi, Central, Eastern, Nyanza, Rift Valley,
--          Western, North Eastern, Coast
-- ============================================================

INSERT INTO `counties` (`code`, `name`, `region`) VALUES

-- COAST REGION
(1,  'Mombasa',           'Coast'),
(2,  'Kwale',             'Coast'),
(3,  'Kilifi',            'Coast'),
(4,  'Tana River',        'Coast'),
(5,  'Lamu',              'Coast'),
(6,  'Taita-Taveta',      'Coast'),

-- NORTH EASTERN REGION
(7,  'Garissa',           'North Eastern'),
(8,  'Wajir',             'North Eastern'),
(9,  'Mandera',           'North Eastern'),

-- EASTERN REGION
(10, 'Marsabit',          'Eastern'),
(11, 'Isiolo',            'Eastern'),
(12, 'Meru',              'Eastern'),
(13, 'Tharaka-Nithi',     'Eastern'),
(14, 'Embu',              'Eastern'),
(15, 'Kitui',             'Eastern'),
(16, 'Machakos',          'Eastern'),
(17, 'Makueni',           'Eastern'),

-- CENTRAL REGION
(18, 'Nyandarua',         'Central'),
(19, 'Nyeri',             'Central'),
(20, 'Kirinyaga',         'Central'),
(21, 'Murang\'a',         'Central'),
(22, 'Kiambu',            'Central'),

-- RIFT VALLEY REGION
(23, 'Turkana',           'Rift Valley'),
(24, 'West Pokot',        'Rift Valley'),
(25, 'Samburu',           'Rift Valley'),
(26, 'Trans-Nzoia',       'Rift Valley'),
(27, 'Uasin Gishu',       'Rift Valley'),
(28, 'Elgeyo-Marakwet',   'Rift Valley'),
(29, 'Nandi',             'Rift Valley'),
(30, 'Baringo',           'Rift Valley'),
(31, 'Laikipia',          'Rift Valley'),
(32, 'Nakuru',            'Rift Valley'),
(33, 'Narok',             'Rift Valley'),
(34, 'Kajiado',           'Rift Valley'),
(35, 'Kericho',           'Rift Valley'),
(36, 'Bomet',             'Rift Valley'),

-- WESTERN REGION
(37, 'Kakamega',          'Western'),
(38, 'Vihiga',            'Western'),
(39, 'Bungoma',           'Western'),
(40, 'Busia',             'Western'),

-- NYANZA REGION
(41, 'Siaya',             'Nyanza'),
(42, 'Kisumu',            'Nyanza'),
(43, 'Homa Bay',          'Nyanza'),
(44, 'Migori',            'Nyanza'),
(45, 'Kisii',             'Nyanza'),
(46, 'Nyamira',           'Nyanza'),

-- NAIROBI REGION
(47, 'Nairobi',           'Nairobi');
