<?php
/**
 * AJAX - Create New User (Admin Only)
 */
require_once '../../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$first_name = $_POST['first_name'] ?? null;
$last_name = $_POST['last_name'] ?? null;
$user_name = $_POST['user_name'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;
$role = $_POST['role'] ?? 'hirer';

if (!$first_name || !$last_name || !$user_name || !$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'All fields including password are required for new users.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Check if email/username exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR user_name = ?");
    $check->execute([$email, $user_name]);
    if ($check->rowCount() > 0) {
        throw new Exception("Email or Username already exists");
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, user_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$first_name, $last_name, $user_name, $email, $password_hash, $role]);
    $new_user_id = $pdo->lastInsertId();

    // Create profile if needed
    if ($role === 'fundi') {
        $pdo->prepare("INSERT INTO fundi_profiles (user_id) VALUES (?)")->execute([$new_user_id]);
    } elseif ($role === 'contractor') {
        $pdo->prepare("INSERT INTO contractor_profiles (user_id) VALUES (?)")->execute([$new_user_id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
