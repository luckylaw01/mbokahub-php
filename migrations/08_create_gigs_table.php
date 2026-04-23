<?php
/**
 * Migration: Create Gigs table for rapid-task showcase
 */
require_once '../includes/db_connect.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gigs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            price_amount DECIMAL(10,2) NOT NULL,
            price_unit VARCHAR(50) DEFAULT 'job', -- 'hour', 'day', 'job'
            description TEXT,
            image_url VARCHAR(255),
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Gigs table created successfully!";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
