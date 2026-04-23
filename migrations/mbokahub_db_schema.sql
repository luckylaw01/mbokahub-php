-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2026 at 02:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mbokahub_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_sw` varchar(100) NOT NULL,
  `icon_class` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name_en`, `name_sw`, `icon_class`, `created_at`) VALUES
(1, 'Plumbing', 'Plumbing', 'fa-faucet', '2026-04-17 08:54:28'),
(2, 'Electrical', 'Umeme', 'fa-bolt', '2026-04-17 08:54:28'),
(3, 'Masonry', 'Ujenzi', 'fa-trowel-bricks', '2026-04-17 08:54:28'),
(4, 'Carpentry', 'Useremala', 'fa-hammer', '2026-04-17 08:54:28'),
(5, 'Painting', 'Upakaji Rangi', 'fa-paint-roller', '2026-04-17 08:54:28');

-- --------------------------------------------------------

--
-- Table structure for table `fundi_profiles`
--

CREATE TABLE `fundi_profiles` (
  `user_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `cover_url` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `tvet_level` enum('student','apprentice','master') DEFAULT 'student',
  `is_verified` tinyint(1) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fundi_profiles`
--

INSERT INTO `fundi_profiles` (`user_id`, `category_id`, `avatar_url`, `cover_url`, `bio`, `location`, `tvet_level`, `is_verified`, `rating`, `review_count`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, 'student', 0, 0.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_fundi_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('open','direct_request','in_progress','completed','cancelled') DEFAULT 'open',
  `description` text NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `budget_range` varchar(100) DEFAULT NULL,
  `urgency` enum('standard','emergency') DEFAULT 'standard',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `user_id`, `assigned_fundi_id`, `category_id`, `title`, `status`, `description`, `location`, `budget_range`, `urgency`, `created_at`) VALUES
(1, 2, NULL, 1, 'Fix leaking sink in the kitchen', 'open', 'I want this fixed now!', 'Maasai Lodge, Ongata Rongai', '1000', 'emergency', '2026-04-17 09:41:35');

-- --------------------------------------------------------

--
-- Table structure for table `job_bids`
--

CREATE TABLE `job_bids` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `fundi_id` int(11) NOT NULL,
  `proposal_text` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewee_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('hirer','fundi','admin') DEFAULT 'hirer',
  `language_pref` enum('en','sw') DEFAULT 'en',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_name`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `role`, `language_pref`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'andrewkm', 'Andrew', 'Kimani', 'kimaniandrew@gmail.com', NULL, '$2y$10$YlKlnkyVQ3YK7uKTOG2MqOkUyy9ft4L2ruJeyaABWBJiqBT7cVSSC', 'fundi', 'en', NULL, '2026-04-17 09:09:50', '2026-04-17 09:37:41'),
(2, 'anderson.makori82', 'Anderson', 'Makori', 'makorianderson@gmail.com', NULL, '$2y$10$RV1NUDZ1L09NuCDxIcshSOu320fC6ngPSU65gP9OxeEEwIfYaIR7u', 'hirer', 'en', NULL, '2026-04-17 09:38:44', '2026-04-17 09:38:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_name_en` (`name_en`),
  ADD KEY `idx_category_name_sw` (`name_sw`);

--
-- Indexes for table `fundi_profiles`
--
ALTER TABLE `fundi_profiles`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_fundi_category` (`category_id`),
  ADD KEY `idx_fundi_location` (`location`),
  ADD KEY `idx_fundi_rating` (`rating`),
  ADD KEY `idx_fundi_verified` (`is_verified`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_job_hirer` (`user_id`),
  ADD KEY `fk_job_fundi` (`assigned_fundi_id`),
  ADD KEY `fk_job_category` (`category_id`),
  ADD KEY `idx_job_status` (`status`),
  ADD KEY `idx_job_location` (`location`),
  ADD KEY `idx_job_created_at` (`created_at`);

--
-- Indexes for table `job_bids`
--
ALTER TABLE `job_bids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bid_job` (`job_id`),
  ADD KEY `fk_bid_fundi` (`fundi_id`),
  ADD KEY `idx_bid_status` (`status`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_review_job` (`job_id`),
  ADD KEY `fk_review_reviewer` (`reviewer_id`),
  ADD KEY `fk_review_reviewee` (`reviewee_id`),
  ADD KEY `idx_review_rating` (`rating`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_name` (`user_name`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_role` (`role`),
  ADD KEY `idx_user_phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `job_bids`
--
ALTER TABLE `job_bids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fundi_profiles`
--
ALTER TABLE `fundi_profiles`
  ADD CONSTRAINT `fk_fundi_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_fundi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `fk_job_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_job_fundi` FOREIGN KEY (`assigned_fundi_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_job_hirer` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_bids`
--
ALTER TABLE `job_bids`
  ADD CONSTRAINT `fk_bid_fundi` FOREIGN KEY (`fundi_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bid_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_reviewee` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
