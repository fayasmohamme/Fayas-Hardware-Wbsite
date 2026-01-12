<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

$pageTitle = 'Register - FayasHardware';
$error = '';
$success = '';

// Redirect if already logged in
if (isCustomerLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if email already exists
        $existingCustomer = getCustomerByEmail($email);
        if ($existingCustomer) {
            $error = 'Email already registered. Please login instead.';
        } else {
            $conn = getDBConnection();
            $hashedPassword = hashPassword($password);
            $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $firstName, $lastName, $email, $hashedPassword, $phone, $address);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h1 style="text-align: center; margin-bottom: 1.5rem; color: #667eea;">Customer Registration</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="first_name">First Name *</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name *</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required minlength="6">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password *</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
        </div>
        
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone">
        </div>
        
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
    </form>
    
    <p style="text-align: center; margin-top: 1.5rem;">
        Already have an account? <a href="login.php" style="color: #667eea;">Login here</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>

