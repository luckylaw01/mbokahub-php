<?php
/**
 * Post Review - MbokaHub AJAX Handler
 * Processes the review submission and updates aggregate ratings.
 */
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = $_POST['job_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $comment = $_POST['comment'] ?? '';
    $reviewer_id = $_SESSION['user_id'];

    if (!$job_id || !$rating) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Verify the job belongs to this user (the hirer) and is completed
        $stmt = $pdo->prepare("SELECT assigned_fundi_id, user_id FROM jobs WHERE id = ?");
        $stmt->execute([$job_id]);
        $job = $stmt->fetch();

        if (!$job || $job['user_id'] != $reviewer_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid job or unauthorized.']);
            $pdo->rollBack();
            exit;
        }

        $reviewee_id = $job['assigned_fundi_id'];

        if (!$reviewee_id) {
            echo json_encode(['success' => false, 'message' => 'No fundi assigned to this job.']);
            $pdo->rollBack();
            exit;
        }

        // 2. Insert the review
        $stmt = $pdo->prepare("
            INSERT INTO reviews (job_id, reviewer_id, reviewee_id, rating, comment)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)
        ");
        $stmt->execute([$job_id, $reviewer_id, $reviewee_id, $rating, $comment]);

        // 3. Update the aggregate rating in fundi_profiles
        // We calculate the fresh average and count
        $stmt = $pdo->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
            FROM reviews 
            WHERE reviewee_id = ?
        ");
        $stmt->execute([$reviewee_id]);
        $stats = $stmt->fetch();

        $updateStmt = $pdo->prepare("
            UPDATE fundi_profiles 
            SET rating = ?, review_count = ? 
            WHERE user_id = ?
        ");
        $updateStmt->execute([$stats['avg_rating'], $stats['review_count'], $reviewee_id]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully.']);

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
