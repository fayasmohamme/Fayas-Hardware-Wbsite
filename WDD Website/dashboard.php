<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

requireCustomerLogin();

$pageTitle = 'Customer Dashboard - FayasHardware';
$customerId = getCustomerId();
$conn = getDBConnection();

// Get customer info
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get order history
$ordersQuery = "SELECT o.*, 
                (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
                FROM orders o 
                WHERE o.customer_id = ? 
                ORDER BY o.order_date DESC";
$stmt = $conn->prepare($ordersQuery);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

include 'includes/header.php';
?>

<div class="container">
    <h1 style="text-align: center; margin: 2rem 0;">Customer Dashboard</h1>
    
    <!-- Profile Section -->
    <div class="table-container">
        <h2 style="margin-bottom: 1.5rem; color: #667eea;">Personal Profile</h2>
        <table>
            <tr>
                <th>Name</th>
                <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($customer['email']); ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo htmlspecialchars($customer['phone'] ?: 'Not provided'); ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo htmlspecialchars($customer['address'] ?: 'Not provided'); ?></td>
            </tr>
            <tr>
                <th>Member Since</th>
                <td><?php echo date('F j, Y', strtotime($customer['created_at'])); ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Order History -->
    <div class="table-container">
        <h2 style="margin-bottom: 1.5rem; color: #667eea;">Order History</h2>
        
        <?php if ($orders->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></td>
                            <td><?php echo $order['item_count']; ?> item(s)</td>
                            <td><?php echo formatCurrency($order['total_amount']); ?></td>
                            <td>
                                <span style="padding: 0.25rem 0.75rem; border-radius: 5px; background: <?php 
                                    echo $order['status'] === 'completed' ? '#28a745' : ($order['status'] === 'pending' ? '#ffc107' : '#dc3545'); 
                                ?>; color: white;">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found. <a href="products.php">Start shopping!</a></p>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

