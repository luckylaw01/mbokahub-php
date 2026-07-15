<?php
require 'includes/db_connect.php';

echo "=== FUNDI PROFILES (tvet_level check) ===" . PHP_EOL;
$stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name, f.tvet_level, f.skills FROM users u JOIN fundi_profiles f ON u.id = f.user_id WHERE u.role = 'fundi' LIMIT 10");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  User ID " . $r['id'] . ": " . $r['first_name'] . " " . $r['last_name'];
    echo " | tvet_level=[" . var_export($r['tvet_level'], true) . "]" . PHP_EOL;
}

echo PHP_EOL . "=== JOBS TABLE (status breakdown) ===" . PHP_EOL;
$stmt2 = $pdo->query("SELECT assigned_fundi_id, status, COUNT(*) as cnt FROM jobs GROUP BY assigned_fundi_id, status ORDER BY assigned_fundi_id");
foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $j) {
    echo "  fundi_id=" . $j['assigned_fundi_id'] . " status=" . $j['status'] . " count=" . $j['cnt'] . PHP_EOL;
}

echo PHP_EOL . "=== fundi_profiles columns ===" . PHP_EOL;
$stmt3 = $pdo->query("SHOW COLUMNS FROM fundi_profiles");
foreach ($stmt3->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "  " . $col['Field'] . " [" . $col['Type'] . "] default=" . $col['Default'] . PHP_EOL;
}
