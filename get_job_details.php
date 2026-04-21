<?php
header('Content-Type: application/json');
require_once 'includes/db_connect.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$job_id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            j.*,
            c.name_en as category_name,
            c.icon_class,
            u.first_name as hirer_name
        FROM jobs j
        JOIN categories c ON j.category_id = c.id
        JOIN users u ON j.user_id = u.id
        WHERE j.id = ?
    ");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        echo json_encode(['error' => 'Job not found']);
        exit;
    }

    echo json_encode($job);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
