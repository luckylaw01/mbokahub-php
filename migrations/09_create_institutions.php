<?php
/**
 * Migration 09: Institutions Table
 */
require_once __DIR__ . '/../includes/db_connect.php';

try {
    // 1. Create institutions table
    $sql1 = "CREATE TABLE IF NOT EXISTS `institutions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `type` enum('TVET', 'University', 'College', 'Vocational') DEFAULT 'TVET',
        `location` varchar(255) DEFAULT NULL,
        `website` varchar(255) DEFAULT NULL,
        `is_partner` tinyint(1) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $pdo->exec($sql1);

    // 2. Add institution_id to fundi_profiles
    $check = $pdo->query("SHOW COLUMNS FROM `fundi_profiles` LIKE 'institution_id'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `fundi_profiles` ADD COLUMN `institution_id` int(11) DEFAULT NULL AFTER `category_id`");
        $pdo->exec("ALTER TABLE `fundi_profiles` ADD CONSTRAINT `fk_fundi_institution` FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE SET NULL");
    }

    // 3. Seed
    $count = $pdo->query("SELECT COUNT(*) FROM institutions")->fetchColumn();
    if ($count == 0) {
        $insts = [
            ['Nairobi Technical Training Institute', 'TVET', 'Nairobi', 'https://nairobi-tti.ac.ke', 1],
            ['Mukurwe-ini Technical and Training Institute', 'TVET', 'Nyeri', 'https://mTTI.ac.ke', 1],
            ['Kabete National Polytechnic', 'TVET', 'Kabete', 'https://kabetepoly.ac.ke', 1]
        ];
        $stmt = $pdo->prepare("INSERT INTO institutions (name, type, location, website, is_partner) VALUES (?, ?, ?, ?, ?)");
        foreach ($insts as $i) {
            $stmt->execute($i);
        }
    }
    echo "Migration 09 successful\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
