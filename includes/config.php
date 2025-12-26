<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'db_connect.php';

// Error reporting (disable for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Please login to access this page.';
        header('Location: ../login.php');
        exit();
    }
}

// Redirect if already logged in
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: ../dashboard.php');
        exit();
    }
}

// Get current page name
function getCurrentPage() {
    return basename($_SERVER['PHP_SELF']);
}

// Get user initials for avatar
function getUserInitials($username) {
    return strtoupper(substr($username, 0, 2));
}

// Sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Calculate grade from percentage
function calculateGrade($percentage) {
    if ($percentage >= 85) return 'A+';
    if ($percentage >= 75) return 'A';
    if ($percentage >= 65) return 'B+';
    if ($percentage >= 55) return 'B';
    if ($percentage >= 40) return 'C';
    return 'F';
}

// Get status class based on percentage
function getStatusClass($percentage) {
    if ($percentage >= 75) return 'status-excellent';
    if ($percentage >= 60) return 'status-good';
    if ($percentage >= 40) return 'status-average';
    return 'status-poor';
}

// Display flash messages
function displayMessage() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert error">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['info'])) {
        echo '<div class="alert info">' . $_SESSION['info'] . '</div>';
        unset($_SESSION['info']);
    }
}
?>