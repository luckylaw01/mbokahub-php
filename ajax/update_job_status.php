<?php
/**
 * AJAX Handler for Job Status Updates (Complete / Cancel)
 */
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$job_id = $_POST['job_id'] ?? null;
$action = $_POST['action'] ?? ''; // 'complete' or 'cancel'

if (!$job_id || !in_array($action, ['complete', 'cancel'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters.']);
    exit;
}

try {
    // Verify ownership
    $stmt = $pdo->prepare("SELECT user_id, status FROM jobs WHERE id = ?");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();

    if (!$job || $job['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized or job not found.']);
        exit;
    }

    if ($job['status'] !== 'open' && $job['status'] !== 'in_progress') {
        echo json_encode(['success' => false, 'message' => 'Job status cannot be updated.']);
        exit;
    }

    $new_status = ($action === 'complete') ? 'completed' : 'cancelled';
    
    $stmt = $pdo->prepare("UPDATE jobs SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $job_id]);

    echo json_encode(['success' => true, 'message' => "Job ". ucfirst($new_status) . " successfully!"]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
