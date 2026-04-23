<?php
/**
 * AJAX Handler for Hirers to accept a bid
 */
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hirer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$hirer_id = $_SESSION['user_id'];
$bid_id = filter_input(INPUT_POST, 'bid_id', FILTER_VALIDATE_INT);
$job_id = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);

if (!$bid_id || !$job_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verify this job belongs to the hirer
    $jobStmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND user_id = ?");
    $jobStmt->execute([$job_id, $hirer_id]);
    if (!$jobStmt->fetch()) {
        throw new Exception("Ownership verification failed.");
    }

    // 2. Get the fundi_id and job details for the gig creation
    $bidDetailsStmt = $pdo->prepare("
        SELECT b.fundi_id, j.title, j.budget_range, j.description 
        FROM job_bids b 
        JOIN jobs j ON b.job_id = j.id 
        WHERE b.id = ?
    ");
    $bidDetailsStmt->execute([$bid_id]);
    $details = $bidDetailsStmt->fetch();

    if (!$details) {
        throw new Exception("Bid details not found.");
    }

    $fundi_id = $details['fundi_id'];
    $title = $details['title'];
    $price = (float)$details['budget_range']; // Using job budget as initial gig price
    $desc = $details['description'];

    // 3. Update job status and assigned fundi
    $updateJobStmt = $pdo->prepare("UPDATE jobs SET status = 'in_progress', assigned_fundi_id = ? WHERE id = ?");
    $updateJobStmt->execute([$fundi_id, $job_id]);

    // 4. Update the accepted bid and reject others
    $acceptBidStmt = $pdo->prepare("UPDATE job_bids SET status = 'accepted' WHERE id = ?");
    $acceptBidStmt->execute([$bid_id]);

    $rejectOthersStmt = $pdo->prepare("UPDATE job_bids SET status = 'rejected' WHERE job_id = ? AND id != ?");
    $rejectOthersStmt->execute([$job_id, $bid_id]);

    // 5. AUTOMATIC SYSTEM: Add to Fundi's Gigs
    // This creates a "Past Success" record in their Gigs section automatically
    $addGigStmt = $pdo->prepare("
        INSERT INTO gigs (user_id, title, price_amount, description, is_active) 
        VALUES (?, ?, ?, ?, 0)
    ");
    // Setting is_active to 0 because this is a 'completed' event record, not necessarily a 'for hire' listing
    $addGigStmt->execute([
        $fundi_id, 
        "Completed: " . $title, 
        $price, 
        "System Verified: Successfully hired for this project on MbokaHub."
    ]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Bid accepted! Gig added to Fundi portfolio.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
