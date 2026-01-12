<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

$pageTitle = 'Contact Us - FayasHardware';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $msg = sanitizeInput($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($msg)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } elseif (!validateEmail($email)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $msg);
        
        if ($stmt->execute()) {
            $message = 'Thank you for your message! We will get back to you soon.';
            $messageType = 'success';
            // Clear form
            $name = $email = $msg = '';
        } else {
            $message = 'Error sending message. Please try again.';
            $messageType = 'error';
        }
        
        $stmt->close();
        $conn->close();
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h1 style="text-align: center; margin: 2rem 0;">Contact Us</h1>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin: 2rem 0;">
        <!-- Contact Form -->
        <div class="form-container">
            <h2 style="margin-bottom: 1.5rem; color: #667eea;">Send us a Message</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="contact.php">
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" required><?php echo isset($msg) ? htmlspecialchars($msg) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
            </form>
        </div>
        
        <!-- Contact Information -->
        <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h2 style="margin-bottom: 1.5rem; color: #667eea;">Store Contact Details</h2>
            
            <div style="margin-bottom: 2rem;">
                <h3 style="color: #667eea; margin-bottom: 0.5rem;">Email</h3>
                <p style="font-size: 1.1rem;">info@ecommerce.com</p>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h3 style="color: #667eea; margin-bottom: 0.5rem;">Phone</h3>
                <p style="font-size: 1.1rem;">+1 (555) 123-4567</p>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h3 style="color: #667eea; margin-bottom: 0.5rem;">Address</h3>
                <p style="font-size: 1.1rem;">
                    123 Main Street<br>
                    City, State 12345<br>
                    United States
                </p>
            </div>
            
            <div>
                <h3 style="color: #667eea; margin-bottom: 0.5rem;">Business Hours</h3>
                <p style="font-size: 1.1rem;">
                    Monday - Friday: 9:00 AM - 6:00 PM<br>
                    Saturday: 10:00 AM - 4:00 PM<br>
                    Sunday: Closed
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

