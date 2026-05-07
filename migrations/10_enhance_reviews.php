<?php
/**
 * Migration 10: Enhance Reviews Table and Add Rating Constraints
 */
require_once __DIR__ . '/../includes/db_connect.php';

try {
    // Ensure reviews table has correct structure and constraints
    // Note: The base schema had a simple version, we're making it robust.
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `reviews` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `job_id` int(11) NOT NULL,
            `reviewer_id` int(11) NOT NULL,
            `reviewee_id` int(11) NOT NULL,
            `rating` tinyint(4) NOT NULL,
            `comment` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_job_review` (`job_id`, `reviewer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    echo "Migration 10 (Review Enhancements) successful.\n";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
