<?php
/**
 * AJAX - Update User Role (Admin Only)
 */
require_once '../../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$id = $_POST['user_id'] ?? null;
$new_role = $_POST['role'] ?? null;
$first_name = $_POST['first_name'] ?? null;
$last_name = $_POST['last_name'] ?? null;
$user_name = $_POST['user_name'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

$allowed_roles = ['hirer', 'fundi', 'admin', 'contractor'];

if (!$id || !$new_role || !in_array($new_role, $allowed_roles) || !$first_name || !$last_name || !$user_name || !$email) {
    echo json_encode(['success' => false, 'error' => 'Invalid data provided. All fields except password are required.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Get old role to see if we need to clean up profile tables
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $old_role = $stmt->fetchColumn();

    if (!$old_role) {
        throw new Exception("User not found");
    }

    // 2. Prepare update query
    if ($password) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET role = ?, first_name = ?, last_name = ?, user_name = ?, email = ?, password_hash = ? WHERE id = ?");
        $update->execute([$new_role, $first_name, $last_name, $user_name, $email, $password_hash, $id]);
    } else {
        $update = $pdo->prepare("UPDATE users SET role = ?, first_name = ?, last_name = ?, user_name = ?, email = ? WHERE id = ?");
        $update->execute([$new_role, $first_name, $last_name, $user_name, $email, $id]);
    }

    // 3. Profile table management
    // If moving to fundi, ensure profile exists
    if ($new_role === 'fundi') {
        $pdo->prepare("INSERT IGNORE INTO fundi_profiles (user_id) VALUES (?)")->execute([$id]);
    } 
    // If moving to contractor, ensure profile exists
    elseif ($new_role === 'contractor') {
        $pdo->prepare("INSERT IGNORE INTO contractor_profiles (user_id) VALUES (?)")->execute([$id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'System error: ' . $e->getMessage()]);
}
