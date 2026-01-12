<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/functions.php';

requireAdminLogin();

$pageTitle = 'Order Management';
$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id'] ?? 0);
    $status = sanitizeInput($_POST['status'] ?? '');
    
    if ($orderId > 0 && in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $orderId);
        
        if ($stmt->execute()) {
            $message = 'Order status updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating order status.';
            $messageType = 'error';
        }
        $stmt->close();
    }
}

// Get filter
$statusFilter = $_GET['status'] ?? 'all';

// Build query
$query = "SELECT o.*, c.first_name, c.last_name, c.email 
          FROM orders o 
          JOIN customers c ON o.customer_id = c.id";
          
if ($statusFilter !== 'all') {
    $query .= " WHERE o.status = ?";
}

$query .= " ORDER BY o.order_date DESC";

$stmt = $conn->prepare($query);
if ($statusFilter !== 'all') {
    $stmt->bind_param("s", $statusFilter);
}
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

include 'includes/header.php';
?>

<div class="admin-page-header">
    <h1>Order Management</h1>
    <p class="admin-welcome">Track and manage customer orders</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Filter -->
<div class="admin-filter">
    <form method="GET" action="orders.php">
        <div class="form-group">
            <label>Filter by Status</label>
            <select name="status" onchange="this.form.submit()">
                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Orders</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
    </form>
</div>

<!-- Orders List -->
<div class="table-container">
    <h2>All Orders</h2>
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
            <?php if ($orders->num_rows > 0): ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                        <td><?php echo formatCurrency($order['total_amount']); ?></td>
                        <td>
                            <form method="POST" action="orders.php" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="status-select status-<?php echo $order['status']; ?>">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                        <td>
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

