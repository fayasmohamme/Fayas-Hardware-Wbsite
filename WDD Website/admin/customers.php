<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/functions.php';

requireAdminLogin();

$pageTitle = 'Customer Management';
$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        
        if (!empty($firstName) && !empty($lastName) && !empty($email) && !empty($password) && validateEmail($email)) {
            $hashedPassword = hashPassword($password);
            $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $firstName, $lastName, $email, $hashedPassword, $phone, $address);
            
            if ($stmt->execute()) {
                $message = 'Customer added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error adding customer. Email might already exist.';
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Please fill in all required fields with valid data.';
            $messageType = 'error';
        }
    } elseif ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($id > 0 && !empty($firstName) && !empty($lastName) && !empty($email) && validateEmail($email)) {
            if (!empty($password)) {
                $hashedPassword = hashPassword($password);
                $stmt = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, password = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $firstName, $lastName, $email, $hashedPassword, $phone, $address, $id);
            } else {
                $stmt = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $firstName, $lastName, $email, $phone, $address, $id);
            }
            
            if ($stmt->execute()) {
                $message = 'Customer updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating customer.';
                $messageType = 'error';
            }
            $stmt->close();
        }
    }
}

// Get customer to edit
$editCustomer = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editCustomer = $result->fetch_assoc();
    $stmt->close();
}

// Get all customers with order count
$customers = $conn->query("SELECT c.*, COUNT(o.id) as order_count 
                          FROM customers c 
                          LEFT JOIN orders o ON c.id = o.customer_id 
                          GROUP BY c.id 
                          ORDER BY c.created_at DESC");

include 'includes/header.php';
?>

<div class="admin-page-header">
    <h1>Customer Management</h1>
    <p class="admin-welcome">View and manage customer accounts and information</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Customer Form -->
<div class="admin-form-section">
    <div class="section-header">
        <h2><?php echo $editCustomer ? '✏️ Edit Customer' : '➕ Add New Customer'; ?></h2>
    </div>
    <div class="form-container" style="max-width: 100%; margin: 0;">
    <form method="POST" action="customers.php">
        <input type="hidden" name="action" value="<?php echo $editCustomer ? 'update' : 'add'; ?>">
        <?php if ($editCustomer): ?>
            <input type="hidden" name="id" value="<?php echo $editCustomer['id']; ?>">
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" required value="<?php echo $editCustomer ? htmlspecialchars($editCustomer['first_name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" required value="<?php echo $editCustomer ? htmlspecialchars($editCustomer['last_name']) : ''; ?>">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required value="<?php echo $editCustomer ? htmlspecialchars($editCustomer['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Password <?php echo $editCustomer ? '(leave blank to keep current)' : '*'; ?></label>
                <input type="password" name="password" <?php echo $editCustomer ? '' : 'required'; ?> minlength="6">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?php echo $editCustomer ? htmlspecialchars($editCustomer['phone']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" value="<?php echo $editCustomer ? htmlspecialchars($editCustomer['address']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $editCustomer ? '💾 Update Customer' : '➕ Add Customer'; ?></button>
            <?php if ($editCustomer): ?>
                <a href="customers.php" class="btn btn-secondary">❌ Cancel</a>
            <?php endif; ?>
        </div>
    </form>
    </div>
</div>

<!-- Customers List -->
<div class="table-container">
    <h2>All Customers</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Orders</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($customers->num_rows > 0): ?>
                <?php while ($customer = $customers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $customer['id']; ?></td>
                        <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($customer['address'] ?: 'N/A'); ?></td>
                        <td><?php echo $customer['order_count']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($customer['created_at'])); ?></td>
                        <td>
                            <a href="customers.php?edit=<?php echo $customer['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No customers found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

