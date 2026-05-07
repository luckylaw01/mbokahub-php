<?php
/**
 * Backend logic for user registration
 */
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $user_name = $_POST['user_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'hirer';
    $category_id = $_POST['category_id'] ?? null;

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($user_name) || empty($email) || empty($password)) {
        die("Please fill all required fields.");
    }

    // Password confirmation check
    if ($password !== $confirm_password) {
        die("Error: Passwords do not match.");
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        // 1. Insert into users table
        $sql = "INSERT INTO users (user_name, first_name, last_name, email, password_hash, role) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_name, $first_name, $last_name, $email, $password_hash, $role]);
        $user_id = $pdo->lastInsertId();

        // 2. Profile initialization based on role
        if ($role === 'fundi') {
            $sql_profile = "INSERT INTO fundi_profiles (user_id, category_id) VALUES (?, ?)";
            $stmt_profile = $pdo->prepare($sql_profile);
            $stmt_profile->execute([$user_id, $category_id ?: null]);
        } elseif ($role === 'contractor') {
            $sql_profile = "INSERT INTO contractor_profiles (user_id) VALUES (?)";
            $stmt_profile = $pdo->prepare($sql_profile);
            $stmt_profile->execute([$user_id]);
        }

        $pdo->commit();

        // Redirect to login or auto-login
        header("Location: login.php?registered=success");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            die("Error: Username or Email already exists.");
        }
        die("Registration failed: " . $e->getMessage());
    }
}
?>
