<?php
/**
 * AJAX Handler for Job Bid Submission
 */
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to bid.']);
    exit;
}

// Check if user is a fundi
if ($_SESSION['role'] !== 'fundi') {
    echo json_encode(['success' => false, 'message' => 'Only Fundis can bid on jobs.']);
    exit;
}

// Check for POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
    $proposal_text = filter_input(INPUT_POST, 'proposal_text', FILTER_SANITIZE_SPECIAL_CHARS);
    $fundi_id = $_SESSION['user_id'];

    if (!$job_id || empty($proposal_text)) {
        echo json_encode(['success' => false, 'message' => 'Invalid job or proposal.']);
        exit;
    }

    try {
        // Check if the user has already bid on this job
        $checkStmt = $pdo->prepare("SELECT id FROM job_bids WHERE job_id = ? AND fundi_id = ?");
        $checkStmt->execute([$job_id, $fundi_id]);
        if ($checkStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You have already submitted a bid for this job.']);
            exit;
        }

        // Insert the new bid
        $stmt = $pdo->prepare("INSERT INTO job_bids (job_id, fundi_id, proposal_text) VALUES (?, ?, ?)");
        if ($stmt->execute([$job_id, $fundi_id, $proposal_text])) {
            echo json_encode(['success' => true, 'message' => 'Bid submitted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit bid. Please try again.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
