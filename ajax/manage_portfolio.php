<?php
/**
 * AJAX Handler for Portfolio, Experience, and Certifications
 */
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_portfolio':
            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $completion_date = filter_input(INPUT_POST, 'completion_date', FILTER_SANITIZE_SPECIAL_CHARS);
            
            if (!isset($_FILES['image'])) {
                echo json_encode(['success' => false, 'message' => 'Project image required.']);
                exit;
            }

            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $upload_dir = '../assets/images/portfolio/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $filename = 'project_' . $user_id . '_' . time() . '.' . $ext;
            $target = $upload_dir . $filename;
            $db_path = 'assets/images/portfolio/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $target)) {
                $stmt = $pdo->prepare("INSERT INTO portfolio_items (user_id, title, description, image_url, completion_date) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $title, $description, $db_path, $completion_date]);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Upload failed.']);
            }
            break;

        case 'add_experience':
            $role = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_SPECIAL_CHARS);
            $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_SPECIAL_CHARS);
            $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);

            $stmt = $pdo->prepare("INSERT INTO experiences (user_id, role, company, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $role, $company, $start_date, $end_date, $description]);
            echo json_encode(['success' => true]);
            break;

        case 'add_cert':
            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $institution = filter_input(INPUT_POST, 'institution', FILTER_SANITIZE_SPECIAL_CHARS);
            $issue_date = filter_input(INPUT_POST, 'issue_date', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

            $stmt = $pdo->prepare("INSERT INTO certifications (user_id, title, institution, issue_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $institution, $issue_date]);
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
