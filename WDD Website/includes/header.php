<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'FayasHardware'; ?></title>
    <?php require_once 'config/functions.php'; ?>
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/style.css'); ?>">
</head>
<body>
    <div class="top-bar"></div>
    <header class="main-header">
        <nav class="navbar">
            <div class="container">
                <div class="nav-brand">
                    <a href="index.php" class="logo-link">
                        <span class="logo-icon">🔧</span>
                        <span class="logo-text">
                            <span class="logo-fayas">Fayas</span><span class="logo-hardware">Hardware</span>
                        </span>
                    </a>
                </div>
                <ul class="nav-menu">
                    <?php 
                    $currentPage = basename($_SERVER['PHP_SELF']);
                    ?>
                    <li><a href="index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>"><span class="nav-icon">🏠</span> Home</a></li>
                    <li><a href="products.php" class="nav-link <?php echo $currentPage === 'products.php' ? 'active' : ''; ?>"><span class="nav-icon">📦</span> Products</a></li>
                    <li><a href="services.php" class="nav-link <?php echo $currentPage === 'services.php' ? 'active' : ''; ?>"><span class="nav-icon">🔔</span> Services</a></li>
                    <li><a href="about.php" class="nav-link <?php echo $currentPage === 'about.php' ? 'active' : ''; ?>"><span class="nav-icon">ℹ️</span> About</a></li>
                    <li><a href="contact.php" class="nav-link <?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>"><span class="nav-icon">✉️</span> Contact</a></li>
                    <?php if (isCustomerLoggedIn()): ?>
                        <li><a href="dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                        <li><a href="logout.php" class="nav-link">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link <?php echo $currentPage === 'login.php' ? 'active' : ''; ?>"><span class="nav-icon">→</span> Login</a></li>
                    <?php endif; ?>
                    <li><a href="cart.php" class="nav-link cart-link <?php echo $currentPage === 'cart.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">🛒</span>
                        <span id="cart-count" class="cart-badge">0</span>
                    </a></li>
                </ul>
            </div>
        </nav>
    </header>
    
    <!-- Welcome Message -->
    <?php if (isset($_SESSION['welcome_message']) && $_SESSION['welcome_message']): ?>
        <div class="welcome-message" id="welcomeMessage">
            <div class="welcome-content">
                <div class="welcome-progress-bar"></div>
                <div class="welcome-icon-wrapper">
                    <div class="welcome-icon-bg"></div>
                    <span class="welcome-icon">👋</span>
                </div>
                <div class="welcome-text">
                    <div class="welcome-badge">
                        <span class="badge-icon">✨</span>
                        Welcome Back
                    </div>
                    <h3>Hello, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!</h3>
                    <p>We're thrilled to have you back at <strong>FayasHardware</strong>. Start shopping now!</p>
                </div>
                <button class="welcome-close" onclick="closeWelcomeMessage()" aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="welcome-shine"></div>
            </div>
        </div>
        <?php 
        unset($_SESSION['welcome_message']); // Remove after displaying
        ?>
    <?php endif; ?>
    
    <main>

