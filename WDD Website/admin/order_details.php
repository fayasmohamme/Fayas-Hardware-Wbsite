<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/functions.php';

requireAdminLogin();

$orderId = $_GET['id'] ?? 0;
$conn = getDBConnection();

// Get order details
$stmt = $conn->prepare("SELECT o.*, c.first_name, c.last_name, c.email, c.phone, c.address 
                       FROM orders o 
                       JOIN customers c ON o.customer_id = c.id 
                       WHERE o.id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: orders.php');
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

$pageTitle = 'Order Details #' . $orderId;
include 'includes/header.php';
?>

<div class="admin-page-header">
    <h1>Order Details #<?php echo $order['id']; ?></h1>
    <p class="admin-welcome">View complete order information and customer details</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin: 2rem 0;">
    <!-- Order Information -->
    <div class="table-container">
        <h2>Order Information</h2>
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
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td class="order-total-amount">
                    <?php echo formatCurrency($order['total_amount']); ?>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Customer Information -->
    <div class="table-container">
        <h2>Customer Information</h2>
        <table>
            <tr>
                <th>Name</th>
                <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($order['email']); ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo htmlspecialchars($order['phone'] ?: 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo htmlspecialchars($order['address'] ?: 'N/A'); ?></td>
            </tr>
        </table>
    </div>
</div>

<!-- Order Items -->
<div class="table-container">
    <h2>Ordered Products</h2>
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
        <tfoot>
            <tr style="font-weight: bold; font-size: 1.2rem;">
                <td colspan="3">Total:</td>
                <td><?php echo formatCurrency($order['total_amount']); ?></td>
            </tr>
        </tfoot>
    </table>
</div>

    <div class="admin-actions">
        <a href="orders.php" class="btn btn-secondary">← Back to Orders</a>
    </div>

<?php
$conn->close();
include 'includes/footer.php';
?>

