<?php
/**
 * AJAX Handler for Saving Jobs
 */
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to save jobs.']);
    exit;
}

// Check if user is a fundi
if ($_SESSION['role'] !== 'fundi') {
    echo json_encode(['success' => false, 'message' => 'Only Fundis can save jobs.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if (!$job_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid job ID.']);
        exit;
    }

    try {
        // Since we don't have a saved_jobs table yet, we'll suggest creating it or 
        // handle it gracefully. For now, we'll try to insert and catch if table doesn't exist.
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (?, ?)");
        if ($stmt->execute([$user_id, $job_id])) {
            echo json_encode(['success' => true, 'message' => 'Job saved for later!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save job.']);
        }
    } catch (PDOException $e) {
        // If table doesn't exist, we provide a clearer error
        if ($e->getCode() == '42S02') {
            echo json_encode(['success' => false, 'message' => 'Saved jobs feature is coming soon (database table missing).']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
