<?php
header('Content-Type: application/json');
require_once 'includes/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hirer') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized or invalid request method']);
    exit;
}

try {
    $data = [
        ':user_id' => $_SESSION['user_id'],
        ':category_id' => (int)$_POST['category_id'],
        ':title' => $_POST['title'],
        ':description' => $_POST['description'],
        ':location' => $_POST['location'],
        ':budget_range' => $_POST['budget_range'],
        ':urgency' => $_POST['urgency'] ?? 'standard'
    ];

    $sql = "INSERT INTO jobs (user_id, category_id, title, description, location, budget_range, urgency) 
            VALUES (:user_id, :category_id, :title, :description, :location, :budget_range, :urgency)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($data);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save job']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
