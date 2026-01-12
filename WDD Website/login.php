<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

$pageTitle = 'Login - FayasHardware';
$error = '';

// Redirect if already logged in
if (isCustomerLoggedIn()) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $customer = getCustomerByEmail($email);
        
        if ($customer && verifyPassword($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
            $_SESSION['welcome_message'] = true; // Set welcome message flag
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h1 style="text-align: center; margin-bottom: 1.5rem; color: #667eea;">Customer Login</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
    </form>
    
    <p style="text-align: center; margin-top: 1.5rem;">
        Don't have an account? <a href="register.php" style="color: #667eea;">Register here</a>
    </p>
    
    <p style="text-align: center; margin-top: 1rem;">
        <a href="admin/login.php" style="color: #667eea;">Admin Login</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>

