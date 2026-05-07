<?php
require_once '../includes/db_connect.php';

echo "<h1>Database Diagnostic</h1>";

// 1. Check Categories
echo "<h2>Categories</h2>";
try {
    $cats = $pdo->query("SELECT * FROM categories")->fetchAll();
    echo "<table border='1'><tr><th>ID</th><th>Name</th></tr>";
    foreach($cats as $c) echo "<tr><td>{$c['id']}</td><td>{$c['name_en']}</td></tr>";
    echo "</table>";
} catch(Exception $e) { echo "Error: " . $e->getMessage(); }

// 2. Check Fundis
echo "<h2>Fundis</h2>";
try {
    $fundis = $pdo->query("SELECT u.id, u.first_name, u.role, f.category_id FROM users u LEFT JOIN fundi_profiles f ON u.id = f.user_id WHERE u.role = 'fundi'")->fetchAll();
    echo "<table border='1'><tr><th>User ID</th><th>Name</th><th>Role</th><th>Cat ID</th></tr>";
    foreach($fundis as $f) echo "<tr><td>{$f['id']}</td><td>{$f['first_name']}</td><td>{$f['role']}</td><td>" . ($f['category_id'] ?? 'NULL') . "</td></tr>";
    echo "</table>";
} catch(Exception $e) { echo "Error: " . $e->getMessage(); }
