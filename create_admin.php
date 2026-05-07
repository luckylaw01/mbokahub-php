<?php
/**
 * ONE-TIME ADMIN CREATION SCRIPT
 * Usage: Run this script once in your browser, then DELETE IT IMMEDIATELY.
 */
require_once 'includes/db_connect.php';

$admin_user = 'admin';
$admin_email = 'admin@mbokahub.co.ke';
$admin_pass = 'Admin1234#';
$first_name = 'System';
$last_name = 'Administrator';
$role = 'admin';

// Hash the password securely
$password_hash = password_hash($admin_pass, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();

    // 1. Check if user already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR user_name = ?");
    $check->execute([$admin_email, $admin_user]);
    
    if ($check->fetch()) {
        die("<h2 style='color:orange;'>Action Canceled: A user with this email or username already exists.</h2>");
    }

    // 2. Insert the Admin user
    $sql = "INSERT INTO users (user_name, first_name, last_name, email, password_hash, role) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$admin_user, $first_name, $last_name, $admin_email, $password_hash, $role]);
    
    $pdo->commit();

    echo "<body style='font-family: sans-serif; padding: 50px; text-align: center;'>";
    echo "<div style='display:inline-block; border: 2px solid #10b981; padding: 30px; border-radius: 20px; background: #f0fdf4;'>";
    echo "<h1 style='color: #10b981;'>✅ Admin Created Successfully</h1>";
    echo "<p><b>Email:</b> $admin_email</p>";
    echo "<p><b>Password:</b> $admin_pass</p>";
    echo "<hr style='border: 1px solid #10b981; opacity: 0.2;'>";
    echo "<p style='color: #b91c1c; font-weight: bold;'>⚠️ CRITICAL SAFETY STEP: Delete this file (create_admin.php) from your server immediately.</p>";
    echo "</div></body>";

} catch (PDOException $e) {
    $pdo->rollBack();
    die("<h2 style='color:red;'>Error creating admin: " . $e->getMessage() . "</h2>");
}
