-- Migration: 2026_07_16_create_tvet_institutions_table.sql
-- Description: Creates the tvet_institutions table and seeds it with
--              public TVET institutions across all 47 Kenyan counties.
-- Created: 2026-07-16
-- Source: TVETA (Technical and Vocational Education and Training Authority) Kenya
-- Note: Requires counties table to exist first. Run 2026_07_16_create_counties_table.sql first.

CREATE TABLE IF NOT EXISTS `tvet_institutions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) DEFAULT NULL COMMENT 'TVETA registration code if known',
  `type` enum('National Polytechnic','Technical Training Institute','Vocational Training Centre','Youth Polytechnic','Institute of Technology','Kenya Institute of Business Technology','Other') NOT NULL DEFAULT 'Technical Training Institute',
  `county_id` int(11) NOT NULL,
  `town` varchar(150) DEFAULT NULL COMMENT 'Town/sub-location within county',
  `is_public` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Public/Government, 0=Private',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tvet_county` (`county_id`),
  KEY `idx_tvet_type` (`type`),
  CONSTRAINT `fk_tvet_county` FOREIGN KEY (`county_id`) REFERENCES `counties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- SEED: TVET Institutions by County
-- county_id references the `counties` table (code 1-47)
-- ============================================================

INSERT INTO `tvet_institutions` (`name`, `type`, `county_id`, `town`, `is_public`) VALUES

-- ===========================
-- COUNTY 1: MOMBASA
-- ===========================
('Mombasa Technical Training Institute', 'Technical Training Institute', 1, 'Mombasa', 1),
('Coast Institute of Technology', 'Institute of Technology', 1, 'Mombasa', 1),
('Mombasa Polytechnic University College', 'National Polytechnic', 1, 'Mombasa', 1),
('Bandari Maritime Academy', 'Institute of Technology', 1, 'Mombasa', 1),
('Likoni Technical and Vocational College', 'Vocational Training Centre', 1, 'Likoni', 1),
('Tudor Technical Training Institute', 'Technical Training Institute', 1, 'Tudor', 1),

-- ===========================
-- COUNTY 2: KWALE
-- ===========================
('Kwale Technical Training Institute', 'Technical Training Institute', 2, 'Kwale', 1),
('Msambweni Technical Training Institute', 'Technical Training Institute', 2, 'Msambweni', 1),
('Ukunda Vocational Training Centre', 'Vocational Training Centre', 2, 'Ukunda', 1),
('Kinango Youth Polytechnic', 'Youth Polytechnic', 2, 'Kinango', 1),

-- ===========================
-- COUNTY 3: KILIFI
-- ===========================
('Kilifi Technical Training Institute', 'Technical Training Institute', 3, 'Kilifi', 1),
('Malindi Technical Training Institute', 'Technical Training Institute', 3, 'Malindi', 1),
('Kaloleni Youth Polytechnic', 'Youth Polytechnic', 3, 'Kaloleni', 1),
('Rabai Vocational Training Centre', 'Vocational Training Centre', 3, 'Rabai', 1),
('Ganze Youth Polytechnic', 'Youth Polytechnic', 3, 'Ganze', 1),

-- ===========================
-- COUNTY 4: TANA RIVER
-- ===========================
('Hola Technical Training Institute', 'Technical Training Institute', 4, 'Hola', 1),
('Garsen Youth Polytechnic', 'Youth Polytechnic', 4, 'Garsen', 1),
('Bura Technical and Vocational College', 'Vocational Training Centre', 4, 'Bura', 1),

-- ===========================
-- COUNTY 5: LAMU
-- ===========================
('Lamu Technical Training Institute', 'Technical Training Institute', 5, 'Lamu', 1),
('Mokowe Youth Polytechnic', 'Youth Polytechnic', 5, 'Mokowe', 1),
('Hindi Vocational Training Centre', 'Vocational Training Centre', 5, 'Hindi', 1),

-- ===========================
-- COUNTY 6: TAITA-TAVETA
-- ===========================
('Taita Taveta Technical and Vocational College', 'Technical Training Institute', 6, 'Voi', 1),
('Wundanyi Technical Training Institute', 'Technical Training Institute', 6, 'Wundanyi', 1),
('Mwatate Youth Polytechnic', 'Youth Polytechnic', 6, 'Mwatate', 1),
('Taveta Vocational Training Centre', 'Vocational Training Centre', 6, 'Taveta', 1),

-- ===========================
-- COUNTY 7: GARISSA
-- ===========================
('Garissa Technical Training Institute', 'Technical Training Institute', 7, 'Garissa', 1),
('Dadaab Youth Polytechnic', 'Youth Polytechnic', 7, 'Dadaab', 1),
('Fafi Vocational Training Centre', 'Vocational Training Centre', 7, 'Fafi', 1),

-- ===========================
-- COUNTY 8: WAJIR
-- ===========================
('Wajir Technical Training Institute', 'Technical Training Institute', 8, 'Wajir', 1),
('Bute Youth Polytechnic', 'Youth Polytechnic', 8, 'Bute', 1),
('Habaswein Vocational Training Centre', 'Vocational Training Centre', 8, 'Habaswein', 1),

-- ===========================
-- COUNTY 9: MANDERA
-- ===========================
('Mandera Technical Training Institute', 'Technical Training Institute', 9, 'Mandera', 1),
('Mandera East Youth Polytechnic', 'Youth Polytechnic', 9, 'Takaba', 1),
('Elwak Vocational Training Centre', 'Vocational Training Centre', 9, 'Elwak', 1),

-- ===========================
-- COUNTY 10: MARSABIT
-- ===========================
('Marsabit Technical Training Institute', 'Technical Training Institute', 10, 'Marsabit', 1),
('Moyale Youth Polytechnic', 'Youth Polytechnic', 10, 'Moyale', 1),
('North Horr Vocational Training Centre', 'Vocational Training Centre', 10, 'North Horr', 1),
('Laisamis Youth Polytechnic', 'Youth Polytechnic', 10, 'Laisamis', 1),

-- ===========================
-- COUNTY 11: ISIOLO
-- ===========================
('Isiolo Technical Training Institute', 'Technical Training Institute', 11, 'Isiolo', 1),
('Merti Youth Polytechnic', 'Youth Polytechnic', 11, 'Merti', 1),
('Garba Tulla Vocational Training Centre', 'Vocational Training Centre', 11, 'Garba Tulla', 1),

-- ===========================
-- COUNTY 12: MERU
-- ===========================
('Meru Technical Training Institute', 'Technical Training Institute', 12, 'Meru', 1),
('Nkubu Technical Training Institute', 'Technical Training Institute', 12, 'Nkubu', 1),
('Kiirua Technical Training Institute', 'Technical Training Institute', 12, 'Kiirua', 1),
('Maua Technical Training Institute', 'Technical Training Institute', 12, 'Maua', 1),
('Tigania Youth Polytechnic', 'Youth Polytechnic', 12, 'Kangeta', 1),
('Igembe Youth Polytechnic', 'Youth Polytechnic', 12, 'Mutuati', 1),

-- ===========================
-- COUNTY 13: THARAKA-NITHI
-- ===========================
('Chuka Technical Training Institute', 'Technical Training Institute', 13, 'Chuka', 1),
('Marimanti Youth Polytechnic', 'Youth Polytechnic', 13, 'Marimanti', 1),
('Tharaka Vocational Training Centre', 'Vocational Training Centre', 13, 'Tharaka', 1),

-- ===========================
-- COUNTY 14: EMBU
-- ===========================
('Embu Technical Training Institute', 'Technical Training Institute', 14, 'Embu', 1),
('Runyenjes Technical Training Institute', 'Technical Training Institute', 14, 'Runyenjes', 1),
('Mbeere Youth Polytechnic', 'Youth Polytechnic', 14, 'Siakago', 1),
('Kangaru Youth Polytechnic', 'Youth Polytechnic', 14, 'Embu', 1),

-- ===========================
-- COUNTY 15: KITUI
-- ===========================
('Kitui Technical Training Institute', 'Technical Training Institute', 15, 'Kitui', 1),
('Mutomo Technical Training Institute', 'Technical Training Institute', 15, 'Mutomo', 1),
('Kyuso Youth Polytechnic', 'Youth Polytechnic', 15, 'Kyuso', 1),
('Mwingi Technical Training Institute', 'Technical Training Institute', 15, 'Mwingi', 1),
('Ngomeni Youth Polytechnic', 'Youth Polytechnic', 15, 'Ngomeni', 1),

-- ===========================
-- COUNTY 16: MACHAKOS
-- ===========================
('Machakos Technical Training Institute', 'Technical Training Institute', 16, 'Machakos', 1),
('Kathiani Technical Training Institute', 'Technical Training Institute', 16, 'Kathiani', 1),
('Masinga Youth Polytechnic', 'Youth Polytechnic', 16, 'Masinga', 1),
('Matungulu Youth Polytechnic', 'Youth Polytechnic', 16, 'Tala', 1),
('Kangundo Technical Training Institute', 'Technical Training Institute', 16, 'Kangundo', 1),
('Athi River Youth Polytechnic', 'Youth Polytechnic', 16, 'Athi River', 1),

-- ===========================
-- COUNTY 17: MAKUENI
-- ===========================
('Makueni Technical Training Institute', 'Technical Training Institute', 17, 'Wote', 1),
('Sultan Hamud Youth Polytechnic', 'Youth Polytechnic', 17, 'Sultan Hamud', 1),
('Kibwezi Technical Training Institute', 'Technical Training Institute', 17, 'Kibwezi', 1),
('Makindu Youth Polytechnic', 'Youth Polytechnic', 17, 'Makindu', 1),

-- ===========================
-- COUNTY 18: NYANDARUA
-- ===========================
('Nyandarua Technical Training Institute', 'Technical Training Institute', 18, 'Ol Kalou', 1),
('Mirangine Youth Polytechnic', 'Youth Polytechnic', 18, 'Mirangine', 1),
('Engineer Vocational Training Centre', 'Vocational Training Centre', 18, 'Engineer', 1),
('Ndaragwa Youth Polytechnic', 'Youth Polytechnic', 18, 'Ndaragwa', 1),

-- ===========================
-- COUNTY 19: NYERI
-- ===========================
('Nyeri Technical Training Institute', 'Technical Training Institute', 19, 'Nyeri', 1),
('Karatina Technical Training Institute', 'Technical Training Institute', 19, 'Karatina', 1),
('Mukurweini Youth Polytechnic', 'Youth Polytechnic', 19, 'Mukurweini', 1),
('Othaya Youth Polytechnic', 'Youth Polytechnic', 19, 'Othaya', 1),
('Tetu Youth Polytechnic', 'Youth Polytechnic', 19, 'Tetu', 1),

-- ===========================
-- COUNTY 20: KIRINYAGA
-- ===========================
('Kerugoya Technical Training Institute', 'Technical Training Institute', 20, 'Kerugoya', 1),
('Mwea Technical Training Institute', 'Technical Training Institute', 20, 'Mwea', 1),
('Kutus Youth Polytechnic', 'Youth Polytechnic', 20, 'Kutus', 1),
('Gichugu Youth Polytechnic', 'Youth Polytechnic', 20, 'Gichugu', 1),

-- ===========================
-- COUNTY 21: MURANG'A
-- ===========================
('Murang\'a Technical Training Institute', 'Technical Training Institute', 21, 'Murang\'a', 1),
('Kangema Technical Training Institute', 'Technical Training Institute', 21, 'Kangema', 1),
('Kigumo Youth Polytechnic', 'Youth Polytechnic', 21, 'Kigumo', 1),
('Maragwa Youth Polytechnic', 'Youth Polytechnic', 21, 'Maragwa', 1),
('Kandara Technical Training Institute', 'Technical Training Institute', 21, 'Kandara', 1),

-- ===========================
-- COUNTY 22: KIAMBU
-- ===========================
('Thika Technical Training Institute', 'Technical Training Institute', 22, 'Thika', 1),
('Limuru Technical Training Institute', 'Technical Training Institute', 22, 'Limuru', 1),
('Kiambu Technical Training Institute', 'Technical Training Institute', 22, 'Kiambu', 1),
('Ruiru Technical Training Institute', 'Technical Training Institute', 22, 'Ruiru', 1),
('Gatundu Youth Polytechnic', 'Youth Polytechnic', 22, 'Gatundu', 1),
('Lari Youth Polytechnic', 'Youth Polytechnic', 22, 'Lari', 1),
('Kabete National Polytechnic', 'National Polytechnic', 22, 'Kabete', 1),

-- ===========================
-- COUNTY 23: TURKANA
-- ===========================
('Lodwar Technical Training Institute', 'Technical Training Institute', 23, 'Lodwar', 1),
('Lokichar Youth Polytechnic', 'Youth Polytechnic', 23, 'Lokichar', 1),
('Kakuma Youth Polytechnic', 'Youth Polytechnic', 23, 'Kakuma', 1),
('Loima Vocational Training Centre', 'Vocational Training Centre', 23, 'Loima', 1),

-- ===========================
-- COUNTY 24: WEST POKOT
-- ===========================
('Kapenguria Technical Training Institute', 'Technical Training Institute', 24, 'Kapenguria', 1),
('Sigor Youth Polytechnic', 'Youth Polytechnic', 24, 'Sigor', 1),
('Alale Vocational Training Centre', 'Vocational Training Centre', 24, 'Alale', 1),
('Kacheliba Youth Polytechnic', 'Youth Polytechnic', 24, 'Kacheliba', 1),

-- ===========================
-- COUNTY 25: SAMBURU
-- ===========================
('Maralal Technical Training Institute', 'Technical Training Institute', 25, 'Maralal', 1),
('Baragoi Youth Polytechnic', 'Youth Polytechnic', 25, 'Baragoi', 1),
('Archer\'s Post Vocational Training Centre', 'Vocational Training Centre', 25, 'Archer\'s Post', 1),

-- ===========================
-- COUNTY 26: TRANS-NZOIA
-- ===========================
('Trans-Nzoia Technical Training Institute', 'Technical Training Institute', 26, 'Kitale', 1),
('Kitale Technical Training Institute', 'Technical Training Institute', 26, 'Kitale', 1),
('Kiminini Youth Polytechnic', 'Youth Polytechnic', 26, 'Kiminini', 1),
('Kwanza Youth Polytechnic', 'Youth Polytechnic', 26, 'Kwanza', 1),
('Saboti Youth Polytechnic', 'Youth Polytechnic', 26, 'Saboti', 1),

-- ===========================
-- COUNTY 27: UASIN GISHU
-- ===========================
('Eldoret National Polytechnic', 'National Polytechnic', 27, 'Eldoret', 1),
('Eldoret Technical Training Institute', 'Technical Training Institute', 27, 'Eldoret', 1),
('Moiben Technical Training Institute', 'Technical Training Institute', 27, 'Moiben', 1),
('Turbo Youth Polytechnic', 'Youth Polytechnic', 27, 'Turbo', 1),
('Kesses Youth Polytechnic', 'Youth Polytechnic', 27, 'Kesses', 1),

-- ===========================
-- COUNTY 28: ELGEYO-MARAKWET
-- ===========================
('Iten Technical Training Institute', 'Technical Training Institute', 28, 'Iten', 1),
('Kapsowar Youth Polytechnic', 'Youth Polytechnic', 28, 'Kapsowar', 1),
('Keiyo Vocational Training Centre', 'Vocational Training Centre', 28, 'Keiyo', 1),
('Marakwet Youth Polytechnic', 'Youth Polytechnic', 28, 'Tot', 1),

-- ===========================
-- COUNTY 29: NANDI
-- ===========================
('Nandi Hills Technical Training Institute', 'Technical Training Institute', 29, 'Nandi Hills', 1),
('Kapsabet Technical Training Institute', 'Technical Training Institute', 29, 'Kapsabet', 1),
('Mosoriot Rural Training Centre', 'Vocational Training Centre', 29, 'Mosoriot', 1),
('Chepkumia Youth Polytechnic', 'Youth Polytechnic', 29, 'Chesumei', 1),

-- ===========================
-- COUNTY 30: BARINGO
-- ===========================
('Kabarnet Technical Training Institute', 'Technical Training Institute', 30, 'Kabarnet', 1),
('Eldama Ravine Technical Training Institute', 'Technical Training Institute', 30, 'Eldama Ravine', 1),
('Baringo Youth Polytechnic', 'Youth Polytechnic', 30, 'Kabarnet', 1),
('Marigat Youth Polytechnic', 'Youth Polytechnic', 30, 'Marigat', 1),

-- ===========================
-- COUNTY 31: LAIKIPIA
-- ===========================
('Nanyuki Technical Training Institute', 'Technical Training Institute', 31, 'Nanyuki', 1),
('Rumuruti Technical Training Institute', 'Technical Training Institute', 31, 'Rumuruti', 1),
('Laikipia Youth Polytechnic', 'Youth Polytechnic', 31, 'Laikipia', 1),
('Nyahururu Technical Training Institute', 'Technical Training Institute', 31, 'Nyahururu', 1),

-- ===========================
-- COUNTY 32: NAKURU
-- ===========================
('Nakuru Technical Training Institute', 'Technical Training Institute', 32, 'Nakuru', 1),
('Naivasha Technical Training Institute', 'Technical Training Institute', 32, 'Naivasha', 1),
('Gilgil Technical Training Institute', 'Technical Training Institute', 32, 'Gilgil', 1),
('Rongai Youth Polytechnic', 'Youth Polytechnic', 32, 'Rongai', 1),
('Njoro Youth Polytechnic', 'Youth Polytechnic', 32, 'Njoro', 1),
('Kuresoi Youth Polytechnic', 'Youth Polytechnic', 32, 'Kuresoi', 1),
('Subukia Youth Polytechnic', 'Youth Polytechnic', 32, 'Subukia', 1),

-- ===========================
-- COUNTY 33: NAROK
-- ===========================
('Narok Technical Training Institute', 'Technical Training Institute', 33, 'Narok', 1),
('Kilgoris Youth Polytechnic', 'Youth Polytechnic', 33, 'Kilgoris', 1),
('Narok North Youth Polytechnic', 'Youth Polytechnic', 33, 'Narok', 1),
('Emurua Dikirr Youth Polytechnic', 'Youth Polytechnic', 33, 'Emurua Dikirr', 1),

-- ===========================
-- COUNTY 34: KAJIADO
-- ===========================
('Kajiado Technical Training Institute', 'Technical Training Institute', 34, 'Kajiado', 1),
('Ngong Hills Technical Training Institute', 'Technical Training Institute', 34, 'Ngong', 1),
('Loitokitok Youth Polytechnic', 'Youth Polytechnic', 34, 'Loitokitok', 1),
('Isinya Youth Polytechnic', 'Youth Polytechnic', 34, 'Isinya', 1),

-- ===========================
-- COUNTY 35: KERICHO
-- ===========================
('Kericho Technical Training Institute', 'Technical Training Institute', 35, 'Kericho', 1),
('Londiani Technical Training Institute', 'Technical Training Institute', 35, 'Londiani', 1),
('Bureti Youth Polytechnic', 'Youth Polytechnic', 35, 'Litein', 1),
('Sigowet Youth Polytechnic', 'Youth Polytechnic', 35, 'Sigowet', 1),

-- ===========================
-- COUNTY 36: BOMET
-- ===========================
('Bomet Technical Training Institute', 'Technical Training Institute', 36, 'Bomet', 1),
('Sotik Technical Training Institute', 'Technical Training Institute', 36, 'Sotik', 1),
('Chepalungu Youth Polytechnic', 'Youth Polytechnic', 36, 'Chepalungu', 1),
('Konoin Youth Polytechnic', 'Youth Polytechnic', 36, 'Konoin', 1),

-- ===========================
-- COUNTY 37: KAKAMEGA
-- ===========================
('Kakamega Technical Training Institute', 'Technical Training Institute', 37, 'Kakamega', 1),
('Mumias Technical Training Institute', 'Technical Training Institute', 37, 'Mumias', 1),
('Butere Technical Training Institute', 'Technical Training Institute', 37, 'Butere', 1),
('Matungu Youth Polytechnic', 'Youth Polytechnic', 37, 'Matungu', 1),
('Lugari Youth Polytechnic', 'Youth Polytechnic', 37, 'Lugari', 1),
('Khwisero Youth Polytechnic', 'Youth Polytechnic', 37, 'Khwisero', 1),

-- ===========================
-- COUNTY 38: VIHIGA
-- ===========================
('Vihiga Technical Training Institute', 'Technical Training Institute', 38, 'Vihiga', 1),
('Hamisi Youth Polytechnic', 'Youth Polytechnic', 38, 'Hamisi', 1),
('Luanda Youth Polytechnic', 'Youth Polytechnic', 38, 'Luanda', 1),
('Emuhaya Youth Polytechnic', 'Youth Polytechnic', 38, 'Emuhaya', 1),

-- ===========================
-- COUNTY 39: BUNGOMA
-- ===========================
('Bungoma Technical Training Institute', 'Technical Training Institute', 39, 'Bungoma', 1),
('Webuye Technical Training Institute', 'Technical Training Institute', 39, 'Webuye', 1),
('Kimilili Technical Training Institute', 'Technical Training Institute', 39, 'Kimilili', 1),
('Mt. Elgon Youth Polytechnic', 'Youth Polytechnic', 39, 'Chwele', 1),
('Tongaren Youth Polytechnic', 'Youth Polytechnic', 39, 'Tongaren', 1),

-- ===========================
-- COUNTY 40: BUSIA
-- ===========================
('Busia Technical Training Institute', 'Technical Training Institute', 40, 'Busia', 1),
('Port Victoria Youth Polytechnic', 'Youth Polytechnic', 40, 'Port Victoria', 1),
('Nambale Technical Training Institute', 'Technical Training Institute', 40, 'Nambale', 1),
('Funyula Youth Polytechnic', 'Youth Polytechnic', 40, 'Funyula', 1),

-- ===========================
-- COUNTY 41: SIAYA
-- ===========================
('Siaya Technical Training Institute', 'Technical Training Institute', 41, 'Siaya', 1),
('Bondo Technical Training Institute', 'Technical Training Institute', 41, 'Bondo', 1),
('Gem Youth Polytechnic', 'Youth Polytechnic', 41, 'Yala', 1),
('Ugenya Youth Polytechnic', 'Youth Polytechnic', 41, 'Ugenya', 1),
('Rarieda Youth Polytechnic', 'Youth Polytechnic', 41, 'Rarieda', 1),

-- ===========================
-- COUNTY 42: KISUMU
-- ===========================
('Kisumu Technical Training Institute', 'Technical Training Institute', 42, 'Kisumu', 1),
('Maseno Technical Training Institute', 'Technical Training Institute', 42, 'Maseno', 1),
('Ahero Technical Training Institute', 'Technical Training Institute', 42, 'Ahero', 1),
('Kisumu Polytechnic', 'National Polytechnic', 42, 'Kisumu', 1),
('Muhoroni Youth Polytechnic', 'Youth Polytechnic', 42, 'Muhoroni', 1),
('Nyando Youth Polytechnic', 'Youth Polytechnic', 42, 'Nyando', 1),

-- ===========================
-- COUNTY 43: HOMA BAY
-- ===========================
('Homa Bay Technical Training Institute', 'Technical Training Institute', 43, 'Homa Bay', 1),
('Mbita Technical Training Institute', 'Technical Training Institute', 43, 'Mbita', 1),
('Ndhiwa Youth Polytechnic', 'Youth Polytechnic', 43, 'Ndhiwa', 1),
('Suba Youth Polytechnic', 'Youth Polytechnic', 43, 'Suba', 1),
('Rachuonyo Youth Polytechnic', 'Youth Polytechnic', 43, 'Kendu Bay', 1),

-- ===========================
-- COUNTY 44: MIGORI
-- ===========================
('Migori Technical Training Institute', 'Technical Training Institute', 44, 'Migori', 1),
('Rongo Technical Training Institute', 'Technical Training Institute', 44, 'Rongo', 1),
('Uriri Youth Polytechnic', 'Youth Polytechnic', 44, 'Uriri', 1),
('Awendo Youth Polytechnic', 'Youth Polytechnic', 44, 'Awendo', 1),
('Suna Youth Polytechnic', 'Youth Polytechnic', 44, 'Suna', 1),

-- ===========================
-- COUNTY 45: KISII
-- ===========================
('Kisii Technical Training Institute', 'Technical Training Institute', 45, 'Kisii', 1),
('Tabaka Technical Training Institute', 'Technical Training Institute', 45, 'Tabaka', 1),
('Gucha Technical Training Institute', 'Technical Training Institute', 45, 'Ogembo', 1),
('Bobasi Youth Polytechnic', 'Youth Polytechnic', 45, 'Sengera', 1),
('Kitutu Youth Polytechnic', 'Youth Polytechnic', 45, 'Kitutu', 1),
('South Mugirango Youth Polytechnic', 'Youth Polytechnic', 45, 'Mugirango', 1),

-- ===========================
-- COUNTY 46: NYAMIRA
-- ===========================
('Nyamira Technical Training Institute', 'Technical Training Institute', 46, 'Nyamira', 1),
('Manga Technical Training Institute', 'Technical Training Institute', 46, 'Manga', 1),
('Borabu Youth Polytechnic', 'Youth Polytechnic', 46, 'Borabu', 1),
('Masaba Youth Polytechnic', 'Youth Polytechnic', 46, 'Keroka', 1),

-- ===========================
-- COUNTY 47: NAIROBI
-- ===========================
('Kenya Technical Trainers College', 'National Polytechnic', 47, 'Gigiri', 1),
('Nairobi Technical Training Institute', 'Technical Training Institute', 47, 'Nairobi CBD', 1),
('Buruburu Technical Training Institute', 'Technical Training Institute', 47, 'Buruburu', 1),
('Industrial Training Institute', 'Technical Training Institute', 47, 'Industrial Area', 1),
('Kenya Institute of Business Technology (KIBT)', 'Kenya Institute of Business Technology', 47, 'Westlands', 1),
('Pumwani Technical Training Institute', 'Technical Training Institute', 47, 'Pumwani', 1),
('Highridge Technical Training Institute', 'Technical Training Institute', 47, 'Highridge', 1),
('Eastleigh Technical Training Institute', 'Technical Training Institute', 47, 'Eastleigh', 1),
('Makadara Technical Training Institute', 'Technical Training Institute', 47, 'Makadara', 1),
('Mathare Youth Polytechnic', 'Youth Polytechnic', 47, 'Mathare', 1),
('Kibera Youth Polytechnic', 'Youth Polytechnic', 47, 'Kibera', 1),
('Embakasi Youth Polytechnic', 'Youth Polytechnic', 47, 'Embakasi', 1),
('Kasarani Youth Polytechnic', 'Youth Polytechnic', 47, 'Kasarani', 1),
('Ruaraka Youth Polytechnic', 'Youth Polytechnic', 47, 'Ruaraka', 1),
('Langata Youth Polytechnic', 'Youth Polytechnic', 47, 'Langata', 1),
('Karen Youth Polytechnic', 'Youth Polytechnic', 47, 'Karen', 1),
('Roysambu Youth Polytechnic', 'Youth Polytechnic', 47, 'Roysambu', 1);
