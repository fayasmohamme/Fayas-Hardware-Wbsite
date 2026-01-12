<?php
require_once 'database.php';

// Sanitize input to prevent XSS
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Get customer by email
function getCustomerByEmail($email) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $customer;
}

// Get admin by username
function getAdminByUsername($username) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $admin;
}

// Format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Get product by ID
function getProductById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT p.*, s.name as supplier_name FROM products p LEFT JOIN suppliers s ON p.supplier_id = s.id WHERE p.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $product;
}

// Get asset URL (for CSS, JS, images)
function getAssetUrl($path) {
    // Get the base URL
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the script directory - this already has proper URL encoding from the server
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    
    // For admin pages, go up one level
    if (strpos($scriptDir, '/admin') !== false) {
        $scriptDir = dirname($scriptDir);
    }
    
    // Clean up the path
    $scriptDir = rtrim($scriptDir, '/');
    if ($scriptDir === '') {
        $scriptDir = '/';
    }
    
    // Handle uploads path differently
    if (strpos($path, 'uploads/') !== false || strpos($path, '../uploads/') !== false) {
        $path = str_replace('../uploads/', 'uploads/', $path);
        // Encode only the filename part (the last segment after the last /)
        $pathParts = explode('/', $path);
        $filename = array_pop($pathParts);
        $encodedFilename = rawurlencode($filename);
        $encodedPath = (!empty($pathParts) ? implode('/', $pathParts) . '/' : '') . $encodedFilename;
        return $protocol . '://' . $host . $scriptDir . '/' . ltrim($encodedPath, '/');
    }
    
    // For assets, encode the filename
    $pathParts = explode('/', $path);
    $filename = array_pop($pathParts);
    $encodedFilename = rawurlencode($filename);
    $encodedPath = (!empty($pathParts) ? implode('/', $pathParts) . '/' : '') . $encodedFilename;
    return $protocol . '://' . $host . $scriptDir . '/assets/' . ltrim($encodedPath, '/');
}
?>

