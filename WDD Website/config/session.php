<?php
// Session configuration - Must be set BEFORE session_start()
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    session_start();
}

// Check if user is logged in (customer)
function isCustomerLoggedIn() {
    return isset($_SESSION['customer_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Get current customer ID
function getCustomerId() {
    return $_SESSION['customer_id'] ?? null;
}

// Get current admin ID
function getAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

// Require customer login
function requireCustomerLogin() {
    if (!isCustomerLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}
?>

