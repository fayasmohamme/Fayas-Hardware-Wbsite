<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

requireCustomerLogin();

$orderId = $_GET['id'] ?? 0;
$customerId = getCustomerId();
$conn = getDBConnection();

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $orderId, $customerId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: dashboard.php');
    exit();
}

$pageTitle = 'Order Success - FayasHardware';
include 'includes/header.php';
?>

<div class="container">
    <div style="text-align: center; padding: 3rem; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 2rem 0;">
        <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
        <h1 style="color: #28a745; margin-bottom: 1rem;">Order Placed Successfully!</h1>
        <p style="font-size: 1.2rem; margin-bottom: 2rem;">
            Thank you for your purchase. Your order has been received and is being processed.
        </p>
        
        <div style="background: #f8f9fa; padding: 2rem; border-radius: 8px; margin: 2rem 0; text-align: left; display: inline-block;">
            <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
            <p><strong>Total Amount:</strong> <?php echo formatCurrency($order['total_amount']); ?></p>
            <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
        </div>
        
        <div style="margin-top: 2rem;">
            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="margin-right: 1rem;">View Order Details</a>
            <a href="dashboard.php" class="btn btn-secondary" style="margin-right: 1rem;">Go to Dashboard</a>
            <a href="products.php" class="btn btn-success">Continue Shopping</a>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

