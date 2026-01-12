<?php
// Get the root directory (2 levels up from admin/includes/)
// __DIR__ = admin/includes/
// dirname(__DIR__) = admin/
// dirname(dirname(__DIR__)) = root directory
$rootDir = dirname(dirname(__DIR__));
require_once $rootDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'session.php';
requireAdminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Admin Dashboard'; ?></title>
    <?php 
    $rootDir = dirname(dirname(__DIR__));
    require_once $rootDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'functions.php';
    ?>
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/style.css'); ?>">
</head>
<body>
    <div style="display: flex;">
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <div class="admin-user-info">
                <div class="admin-avatar"><?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?></div>
                <div class="admin-name-wrapper">
                    <div class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                    <div class="admin-role">Administrator</div>
                </div>
            </div>
            <ul>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                    <a href="index.php">📊 Dashboard</a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>">
                    <a href="products.php">📦 Products</a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'suppliers.php' ? 'active' : ''; ?>">
                    <a href="suppliers.php">🏢 Suppliers</a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'customers.php' ? 'active' : ''; ?>">
                    <a href="customers.php">👥 Customers</a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">
                    <a href="orders.php">🛒 Orders</a>
                </li>
                <li class="logout-item">
                    <a href="logout.php">🚪 Logout</a>
                </li>
            </ul>
        </div>
        <div class="admin-content">

