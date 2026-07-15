<?php
require 'includes/db_connect.php';

echo "=== PORTFOLIO ITEMS per fundi ===" . PHP_EOL;
$stmt = $pdo->query("SELECT user_id, COUNT(*) as cnt FROM portfolio_items GROUP BY user_id");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  fundi_id=" . $r['user_id'] . " portfolio_items=" . $r['cnt'] . PHP_EOL;
}

echo PHP_EOL . "=== GIGS per fundi ===" . PHP_EOL;
$stmt2 = $pdo->query("SELECT user_id, is_active, COUNT(*) as cnt FROM gigs GROUP BY user_id, is_active");
foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  fundi_id=" . $r['user_id'] . " is_active=" . $r['is_active'] . " gigs=" . $r['cnt'] . PHP_EOL;
}
