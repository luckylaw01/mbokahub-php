<?php
header('Content-Type: application/json');
require_once 'includes/db_connect.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$fundi_id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            u.first_name, 
            u.last_name, 
            u.role,
            fp.specialization, 
            fp.bio, 
            fp.location, 
            fp.rating,
            c.name_en as category_name,
            c.icon_class
        FROM users u
        JOIN fundi_profiles fp ON u.id = fp.user_id
        JOIN categories c ON fp.category_id = c.id
        WHERE u.id = ?
    ");
    $stmt->execute([$fundi_id]);
    $fundi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fundi) {
        echo json_encode(['error' => 'Fundi not found']);
        exit;
    }

    // Success response
    echo json_encode($fundi);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
