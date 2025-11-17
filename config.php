
<?php
// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
session_start();

// Base URL configuration
define('BASE_URL', 'http://localhost/pharmacy-management');

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to login if not authenticated
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}

// Check user role
function requireAdmin() {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: ../pages/dashboard.php');
        exit;
    }
}
?>
