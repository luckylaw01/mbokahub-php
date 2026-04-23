<?php
/**
 * AJAX Handler for Dropping/Cancelling a Job Bid
 */
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to modify bids.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
    $fundi_id = $_SESSION['user_id'];

    if (!$job_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid job ID.']);
        exit;
    }

    try {
        // Delete the bid if it belongs to this fundi
        $stmt = $pdo->prepare("DELETE FROM job_bids WHERE job_id = ? AND fundi_id = ? AND status = 'pending'");
        $stmt->execute([$job_id, $fundi_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Bid dropped successfully!']);
        } else {
            // Either no bid found or it's already accepted/rejected
            $checkStmt = $pdo->prepare("SELECT status FROM job_bids WHERE job_id = ? AND fundi_id = ?");
            $checkStmt->execute([$job_id, $fundi_id]);
            $bid = $checkStmt->fetch();

            if ($bid && $bid['status'] !== 'pending') {
                echo json_encode(['success' => false, 'message' => 'Cannot drop bid: Status is ' . $bid['status'] . '.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No pending bid found to drop.']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
