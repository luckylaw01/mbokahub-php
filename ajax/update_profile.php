<?php
/**
 * AJAX Handler for Profile Updates & Avatar Uploads
 */
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    // 1. Handle Avatar Upload
    if (isset($_FILES['avatar'])) {
        $file = $_FILES['avatar'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type.']);
            exit;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large (max 2MB).']);
            exit;
        }

        $upload_dir = '../assets/images/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
        $target = $upload_dir . $filename;
        $db_path = 'assets/images/profiles/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Update DB (fundi_profiles only)
            if ($role === 'fundi') {
                $stmt = $pdo->prepare("UPDATE fundi_profiles SET avatar_url = ? WHERE user_id = ?");
                $stmt->execute([$db_path, $user_id]);
            }
            // If we want hirers to have avatars too, we'd need to add that column to users or a separate table
            
            echo json_encode(['success' => true, 'message' => 'Avatar updated!', 'path' => $db_path]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
        }
        exit;
    }

    // 2. Handle Text Profile Updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS);
        $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_SPECIAL_CHARS);

        // Start transaction
        $pdo->beginTransaction();

        // Update basic user info
        $stmt1 = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
        $stmt1->execute([$first_name, $last_name, $user_id]);
        
        // Update session name
        $_SESSION['name'] = $first_name . ' ' . $last_name;

        // Update fundi specific info
        if ($role === 'fundi') {
            $stmt2 = $pdo->prepare("UPDATE fundi_profiles SET location = ?, bio = ? WHERE user_id = ?");
            $stmt2->execute([$location, $bio, $user_id]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
