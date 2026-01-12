<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/functions.php';

requireAdminLogin();

$pageTitle = 'Supplier Management';
$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        
        if (!empty($name) && !empty($email) && validateEmail($email)) {
            $stmt = $conn->prepare("INSERT INTO suppliers (name, email, phone, address) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $address);
            
            if ($stmt->execute()) {
                $message = 'Supplier added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error adding supplier. Email might already exist.';
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Please fill in all required fields with valid data.';
            $messageType = 'error';
        }
    } elseif ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        
        if ($id > 0 && !empty($name) && !empty($email) && validateEmail($email)) {
            $stmt = $conn->prepare("UPDATE suppliers SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $phone, $address, $id);
            
            if ($stmt->execute()) {
                $message = 'Supplier updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating supplier.';
                $messageType = 'error';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = 'Supplier deleted successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error deleting supplier. Supplier might be assigned to products.';
                $messageType = 'error';
            }
            $stmt->close();
        }
    }
}

// Get supplier to edit
$editSupplier = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editSupplier = $result->fetch_assoc();
    $stmt->close();
}

// Get all suppliers
$suppliers = $conn->query("SELECT s.*, COUNT(p.id) as product_count 
                          FROM suppliers s 
                          LEFT JOIN products p ON s.id = p.supplier_id 
                          GROUP BY s.id 
                          ORDER BY s.name");

include 'includes/header.php';
?>

<div class="admin-page-header">
    <h1>Supplier Management</h1>
    <p class="admin-welcome">Manage suppliers and assign them to products</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Supplier Form -->
<div class="admin-form-section">
    <div class="section-header">
        <h2><?php echo $editSupplier ? '✏️ Edit Supplier' : '➕ Add New Supplier'; ?></h2>
    </div>
    <div class="form-container" style="max-width: 100%; margin: 0;">
    <form method="POST" action="suppliers.php">
        <input type="hidden" name="action" value="<?php echo $editSupplier ? 'update' : 'add'; ?>">
        <?php if ($editSupplier): ?>
            <input type="hidden" name="id" value="<?php echo $editSupplier['id']; ?>">
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Supplier Name *</label>
                <input type="text" name="name" required value="<?php echo $editSupplier ? htmlspecialchars($editSupplier['name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required value="<?php echo $editSupplier ? htmlspecialchars($editSupplier['email']) : ''; ?>">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?php echo $editSupplier ? htmlspecialchars($editSupplier['phone']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" value="<?php echo $editSupplier ? htmlspecialchars($editSupplier['address']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $editSupplier ? '💾 Update Supplier' : '➕ Add Supplier'; ?></button>
            <?php if ($editSupplier): ?>
                <a href="suppliers.php" class="btn btn-secondary">❌ Cancel</a>
            <?php endif; ?>
        </div>
    </form>
    </div>
</div>

<!-- Suppliers List -->
<div class="table-container">
    <h2>All Suppliers</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Products</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($suppliers->num_rows > 0): ?>
                <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $supplier['id']; ?></td>
                        <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                        <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                        <td><?php echo htmlspecialchars($supplier['phone'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($supplier['address'] ?: 'N/A'); ?></td>
                        <td><?php echo $supplier['product_count']; ?></td>
                        <td>
                            <a href="suppliers.php?edit=<?php echo $supplier['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Edit</a>
                            <form method="POST" action="suppliers.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $supplier['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No suppliers found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

