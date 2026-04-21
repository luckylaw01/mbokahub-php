<?php
/**
 * MbokaHub Database Connection Handler
 * Uses PDO for secure, prepared SQL execution.
 */

// Database configuration. The local database is called 'mbokahub_db' with user 'root' and no password.
$host = 'localhost';
$dbname = 'mbokahub_db';
$username = 'root';
$password = '';
   

try {
    // Create a new PDO instance
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // In production, log error and show friendly message
    die("Connection failed: " . $e->getMessage());
}
?>
