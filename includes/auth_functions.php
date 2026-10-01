<?php
// Authentication Functions

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . 'login.php');
        exit();
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . 'admin/login.php');
        exit();
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function sendPasswordResetEmail($email, $token) {
    // In production, integrate with actual email service
    $resetLink = SITE_URL . 'reset-password.php?token=' . $token;
    // mail($email, 'Password Reset', "Click here to reset your password: " . $resetLink);
    return true;
}
?>
