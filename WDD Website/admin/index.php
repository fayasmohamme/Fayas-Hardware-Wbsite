<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/functions.php';

requireAdminLogin();

$pageTitle = 'Admin Dashboard';
$conn = getDBConnection();

// Get statistics
$stats = [];

// Total products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $result->fetch_assoc()['count'];

// Total customers
$result = $conn->query("SELECT COUNT(*) as count FROM customers");
$stats['customers'] = $result->fetch_assoc()['count'];

// Total suppliers
$result = $conn->query("SELECT COUNT(*) as count FROM suppliers");
$stats['suppliers'] = $result->fetch_assoc()['count'];

// Total orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
$stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Recent orders
$recentOrders = $conn->query("SELECT o.*, c.first_name, c.last_name, c.email 
                              FROM orders o 
                              JOIN customers c ON o.customer_id = c.id 
                              ORDER BY o.order_date DESC 
                              LIMIT 10");

include 'includes/header.php';
?>

<div class="admin-page-header">
    <h1>Dashboard Overview</h1>
    <p class="admin-welcome">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>! 👋</p>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h3>📦 Total Products</h3>
        <div class="number"><?php echo number_format($stats['products']); ?></div>
    </div>
    <div class="dashboard-card">
        <h3>👥 Total Customers</h3>
        <div class="number"><?php echo number_format($stats['customers']); ?></div>
    </div>
    <div class="dashboard-card">
        <h3>🏢 Total Suppliers</h3>
        <div class="number"><?php echo number_format($stats['suppliers']); ?></div>
    </div>
    <div class="dashboard-card">
        <h3>🛒 Total Orders</h3>
        <div class="number"><?php echo number_format($stats['orders']); ?></div>
    </div>
    <div class="dashboard-card">
        <h3>💰 Total Revenue</h3>
        <div class="number"><?php echo formatCurrency($stats['revenue']); ?></div>
    </div>
</div>

<div class="table-container" style="margin-top: 2rem;">
    <h2 style="margin-bottom: 1.5rem;">Recent Orders</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Order Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($recentOrders->num_rows > 0): ?>
                <?php while ($order = $recentOrders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                        <td><?php echo formatCurrency($order['total_amount']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                        <td>
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <div style="padding: 2rem;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
                            <p style="color: var(--gray); font-size: 1.1rem;">No orders found.</p>
                            <p style="color: var(--gray); font-size: 0.9rem; margin-top: 0.5rem;">Orders will appear here once customers start placing orders.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

