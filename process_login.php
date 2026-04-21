<?php
/**
 * Login Logic
 */
require_once 'includes/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    if (empty($email) || empty($password)) {
        header("Location: login.php?error=invalid");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];

            // Handle Remember Me
            if ($remember_me) {
                // Generate a secure random token
                $token = bin2hex(random_bytes(32));
                
                // Save token in DB
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $user['id']]);

                // Store in cookie for 30 days
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            }

            header("Location: index.php");
            exit();
        } else {
            // Login failed
            header("Location: login.php?error=invalid");
            exit();
        }

    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}
?>
