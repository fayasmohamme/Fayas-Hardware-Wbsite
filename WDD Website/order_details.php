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

// Get order items
$itemsQuery = "SELECT oi.*, p.name as product_name, p.image 
               FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = ?";
$stmt = $conn->prepare($itemsQuery);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

$pageTitle = 'Order Details - FayasHardware';
include 'includes/header.php';
?>

<div class="container">
    <h1 style="text-align: center; margin: 2rem 0;">Order Details #<?php echo $order['id']; ?></h1>
    
    <div class="table-container">
        <h2 style="margin-bottom: 1.5rem; color: #667eea;">Order Information</h2>
        <table>
            <tr>
                <th>Order ID</th>
                <td>#<?php echo $order['id']; ?></td>
            </tr>
            <tr>
                <th>Order Date</th>
                <td><?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <span style="padding: 0.25rem 0.75rem; border-radius: 5px; background: <?php 
                        echo $order['status'] === 'completed' ? '#28a745' : ($order['status'] === 'pending' ? '#ffc107' : '#dc3545'); 
                    ?>; color: white;">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td style="font-size: 1.5rem; font-weight: bold; color: #667eea;">
                    <?php echo formatCurrency($order['total_amount']); ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="table-container">
        <h2 style="margin-bottom: 1.5rem; color: #667eea;">Ordered Products</h2>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                    <?php echo $item['image'] ? '<img src="' . getAssetUrl('uploads/' . htmlspecialchars($item['image'])) . '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 5px;">' : '📦'; ?>
                                </div>
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </div>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo formatCurrency($item['price']); ?></td>
                        <td><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div style="text-align: center; margin: 2rem 0;">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

