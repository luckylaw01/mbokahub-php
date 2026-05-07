<?php
/**
 * AJAX - Manage User (Create or Update)
 */
require_once '../../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_POST['user_id'] ?? null;
$first_name = $_POST['first_name'] ?? null;
$last_name = $_POST['last_name'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;
$role = $_POST['role'] ?? 'hirer';
$is_verified = isset($_POST['is_verified']) ? 1 : 0;

// Simple username generation if not provided (for new users)
$user_name = $_POST['user_name'] ?? strtolower($first_name . '.' . $last_name . rand(10, 99));

if (!$first_name || !$last_name || !$email) {
    echo json_encode(['success' => false, 'message' => 'First Name, Last Name, and Email are required.']);
    exit();
}

try {
    if ($user_id) {
        // UPDATE Existing User
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, is_verified = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $email, $role, $is_verified, $user_id]);

        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$password_hash, $user_id]);
        }
        echo json_encode(['success' => true]);
    } else {
        // CREATE New User
        if (empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Password is required for new members.']);
            exit();
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, user_name, email, password_hash, role, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $user_name, $email, $password_hash, $role, $is_verified]);
        
        echo json_encode(['success' => true]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
