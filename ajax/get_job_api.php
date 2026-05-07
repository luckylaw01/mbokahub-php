<?php
/**
 * AJAX API for Job Details - MbokaHub
 * Returns JSON for the dashboard modal previews.
 */
session_start();
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/includes/db_connect.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No job ID provided']);
    exit;
}

$jobId = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT j.*, 
               CONCAT(u.first_name, ' ', u.last_name) as client_name, 
               c.name_en as category_name,
               c.icon_class
        FROM jobs j 
        JOIN users u ON j.user_id = u.id 
        JOIN categories c ON j.category_id = c.id 
        WHERE j.id = ?
    ");
    $stmt->execute([$jobId]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($job) {
        $job['budget'] = $job['budget_range'] ?? '0.00';
        $job['created_at_formatted'] = date('M d, Y', strtotime($job['created_at']));
        echo json_encode($job);
    } else {
        echo json_encode(['error' => 'Job not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
exit;
