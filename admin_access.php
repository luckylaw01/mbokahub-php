<?php
/**
 * Admin Access Redirection for MbokaHub
 */
require_once 'includes/db_connect.php';
session_start();

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/index.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
