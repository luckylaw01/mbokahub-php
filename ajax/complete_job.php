<?php
/**
 * AJAX Handler for Job Completion & Rating
 */
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hirer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$hirer_id = $_SESSION['user_id'];
$job_id = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
$comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$job_id || !$rating || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters. Please provide a rating between 1 and 5.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verify job ownership and status
    $stmt = $pdo->prepare("SELECT assigned_fundi_id, status FROM jobs WHERE id = ? AND user_id = ?");
    $stmt->execute([$job_id, $hirer_id]);
    $job = $stmt->fetch();

    if (!$job) {
        throw new Exception("Job not found or unauthorized.");
    }

    if ($job['status'] === 'completed') {
        throw new Exception("Job is already marked as completed.");
    }

    $fundi_id = $job['assigned_fundi_id'];
    if (!$fundi_id) {
        throw new Exception("No fundi assigned to this job yet.");
    }

    // 2. Mark job as completed
    $updateJob = $pdo->prepare("UPDATE jobs SET status = 'completed' WHERE id = ?");
    $updateJob->execute([$job_id]);

    // 3. Insert review
    $insertReview = $pdo->prepare("INSERT INTO reviews (job_id, reviewer_id, reviewee_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
    $insertReview->execute([$job_id, $hirer_id, $fundi_id, $rating, $comment]);

    // 4. Update Fundi's average rating and review count
    // We'll calculate the new average from the reviews table to ensure accuracy
    $avgStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as rev_count FROM reviews WHERE reviewee_id = ?");
    $avgStmt->execute([$fundi_id]);
    $stats = $avgStmt->fetch();

    $updateProfile = $pdo->prepare("UPDATE fundi_profiles SET rating = ?, review_count = ? WHERE user_id = ?");
    $updateProfile->execute([$stats['avg_rating'], $stats['rev_count'], $fundi_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Job completed and review submitted!']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}