<?php
/**
 * AJAX Live Search Backend - MbokaHub
 * Returns query matches for jobs and artisans.
 */
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$type = trim($_GET['type'] ?? 'all'); // 'all', 'jobs', 'artisans'

if ($query === '') {
    echo json_encode(['success' => true, 'jobs' => [], 'artisans' => []]);
    exit;
}

$search_term = "%$query%";
$response = [
    'success' => true,
    'jobs' => [],
    'artisans' => []
];

try {
    // 1. Fetch matching Jobs
    if ($type === 'all' || $type === 'jobs') {
        $stmt = $pdo->prepare("
            SELECT j.id, j.title, j.description, j.budget_range, j.location, j.urgency, c.name_en as cat_name, c.icon_class
            FROM jobs j
            LEFT JOIN categories c ON j.category_id = c.id
            WHERE j.status = 'open' 
              AND (j.title LIKE ? OR j.description LIKE ? OR j.location LIKE ? OR c.name_en LIKE ?)
            ORDER BY j.created_at DESC
            LIMIT 15
        ");
        $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
        $response['jobs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Fetch matching Artisans
    if ($type === 'all' || $type === 'artisans') {
        $stmt = $pdo->prepare("
            SELECT u.id, u.first_name, u.last_name, u.user_name, fp.bio, fp.location, fp.skills, fp.avatar_url, fp.rating, fp.review_count, c.name_en as specialty
            FROM users u
            JOIN fundi_profiles fp ON u.id = fp.user_id
            LEFT JOIN categories c ON fp.category_id = c.id
            WHERE u.role = 'fundi'
              AND (u.first_name LIKE ? OR u.last_name LIKE ? OR fp.skills LIKE ? OR fp.bio LIKE ? OR fp.location LIKE ? OR c.name_en LIKE ?)
            ORDER BY fp.rating DESC, fp.review_count DESC
            LIMIT 15
        ");
        $stmt->execute([$search_term, $search_term, $search_term, $search_term, $search_term, $search_term]);
        $response['artisans'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database search error: ' . $e->getMessage()]);
}
