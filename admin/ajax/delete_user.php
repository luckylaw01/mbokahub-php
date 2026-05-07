<?php
/**
 * AJAX - Delete User (Admin Only)
 */
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Missing User ID']);
    exit();
}

try {
    // Prevent admin from deleting themselves
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'You cannot delete yourself']);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'User not found or restricted']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
