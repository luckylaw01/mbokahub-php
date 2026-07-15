<?php
require 'includes/db_connect.php';
try {
    $pdo->exec("ALTER TABLE fundi_profiles MODIFY COLUMN tvet_level VARCHAR(50) NOT NULL DEFAULT 'None'");
    echo "Column altered successfully\n";
    $pdo->exec("UPDATE fundi_profiles SET tvet_level = 'None' WHERE tvet_level IN ('student','apprentice','master','','Level 0','Level 1','Level 2')");
    echo "Legacy values migrated\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM fundi_profiles LIKE 'tvet_level'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Column is now: Type=" . $col['Type'] . " Default=" . $col['Default'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
