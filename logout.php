<?php
require_once 'includes/db_connect.php';
session_start();

// Clear remember_token from DB if user is logging out
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {}
}

// Clear cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

session_unset();
session_destroy();
header("Location: index.php");
exit();
?>
