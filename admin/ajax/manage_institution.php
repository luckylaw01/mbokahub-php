<?php
/**
 * AJAX - Manage Institution (Create or Update)
 */
require_once '../../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? null;
$type = $_POST['type'] ?? 'TVET';
$location = $_POST['location'] ?? null;
$website = $_POST['website'] ?? null;
$is_partner = isset($_POST['is_partner']) ? 1 : 0;

if (!$name || !$location) {
    echo json_encode(['success' => false, 'message' => 'Name and Location are required.']);
    exit();
}

try {
    if ($id) {
        // UPDATE
        $stmt = $pdo->prepare("UPDATE institutions SET name = ?, type = ?, location = ?, website = ?, is_partner = ? WHERE id = ?");
        $stmt->execute([$name, $type, $location, $website, $is_partner, $id]);
    } else {
        // CREATE
        $stmt = $pdo->prepare("INSERT INTO institutions (name, type, location, website, is_partner) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $type, $location, $website, $is_partner]);
    }
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
