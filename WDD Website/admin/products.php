<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/functions.php';

requireAdminLogin();

$pageTitle = 'Product Management';
$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle image upload
function uploadProductImage($file, $productId = null) {
    $rootDir = dirname(__DIR__);
    $uploadDir = $rootDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            return ['success' => false, 'message' => 'Failed to create uploads directory.'];
        }
    }
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File size exceeds 5MB limit.'];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = 'product_' . ($productId ? $productId . '_' : '') . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Verify file was uploaded
            if (file_exists($targetPath)) {
                return ['success' => true, 'filename' => $fileName];
            } else {
                return ['success' => false, 'message' => 'File upload verification failed.'];
            }
        } else {
            return ['success' => false, 'message' => 'Failed to upload file. Check directory permissions.'];
        }
    }
    
    return ['success' => false, 'message' => 'No file uploaded or upload error: ' . $file['error']];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitizeInput($_POST['name'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock_quantity'] ?? 0);
        $supplierId = intval($_POST['supplier_id'] ?? 0);
        $category = sanitizeInput($_POST['category'] ?? '');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $image = '';
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadProductImage($_FILES['image']);
                if ($uploadResult['success']) {
                    $image = $uploadResult['filename'];
                    // Debug: Log the filename
                    error_log("Image uploaded successfully: " . $image);
                } else {
                    $message = 'Image upload failed: ' . $uploadResult['message'];
                    $messageType = 'error';
                }
            } else {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                ];
                $message = 'Image upload error: ' . ($uploadErrors[$_FILES['image']['error']] ?? 'Unknown error');
                $messageType = 'error';
            }
        }
        
        if (!empty($name) && $price > 0 && empty($message)) {
            // Ensure image is set properly - never save '0', use empty string instead
            if (empty($image) || $image === '0' || $image === 0) {
                $image = ''; // Use empty string, not '0'
            }
            
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock_quantity, supplier_id, category, featured, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            // Parameters: name(s), description(s), price(d), stock(i), supplierId(i), category(s), featured(i), image(s)
            // Correct types: s s d i i s i s
            $stmt->bind_param("ssdiisis", $name, $description, $price, $stock, $supplierId, $category, $featured, $image);
            
            if ($stmt->execute()) {
                $insertedId = $conn->insert_id;
                // Verify the image was saved by checking the database
                $checkStmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
                $checkStmt->bind_param("i", $insertedId);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                $savedProduct = $result->fetch_assoc();
                $checkStmt->close();
                
                $savedImage = $savedProduct['image'] ?? 'NOT SAVED';
                $message = 'Product added successfully! ID: ' . $insertedId . (!empty($image) ? ' | Image filename: ' . $image . ' | Saved to DB: ' . $savedImage : ' | No image');
                $messageType = 'success';
                // Debug: Verify what was saved
                error_log("Product saved with image: " . $image . " | DB has: " . $savedImage);
            } else {
                $message = 'Error adding product: ' . $stmt->error;
                $messageType = 'error';
                error_log("Database error: " . $stmt->error);
            }
            $stmt->close();
        } elseif (empty($message)) {
            $message = 'Please fill in all required fields.';
            $messageType = 'error';
        }
    } elseif ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $name = sanitizeInput($_POST['name'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock_quantity'] ?? 0);
        $supplierId = intval($_POST['supplier_id'] ?? 0);
        $category = sanitizeInput($_POST['category'] ?? '');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $image = '';
        
        // Get current product to keep existing image if no new one uploaded
        $currentProduct = getProductById($id);
        $image = $currentProduct['image'] ?? '';
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadProductImage($_FILES['image'], $id);
            if ($uploadResult['success']) {
                // Delete old image if exists
                if (!empty($image)) {
                    $oldImagePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $image;
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }
                $image = $uploadResult['filename'];
            } else {
                $message = $uploadResult['message'];
                $messageType = 'error';
            }
        }
        
        if ($id > 0 && !empty($name) && $price > 0 && empty($message)) {
            // Ensure image is set (keep existing if no new upload)
            if (empty($image)) {
                $image = $currentProduct['image'] ?? '';
            }
            // Never save '0' as image value
            if ($image === '0' || $image === 0) {
                $image = '';
            }
            
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, supplier_id = ?, category = ?, featured = ?, image = ? WHERE id = ?");
            // Parameters: name(s), description(s), price(d), stock(i), supplierId(i), category(s), featured(i), image(s), id(i)
            // Correct types: s s d i i s i s i (9 parameters total)
            // Parameters: name(s), description(s), price(d), stock(i), supplierId(i), category(s), featured(i), image(s), id(i)
            $stmt->bind_param("ssdiisisi", $name, $description, $price, $stock, $supplierId, $category, $featured, $image, $id);
            
            if ($stmt->execute()) {
                $message = 'Product updated successfully!' . (!empty($image) ? ' Image: ' . $image : '');
                $messageType = 'success';
            } else {
                $message = 'Error updating product: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = 'Product deleted successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error deleting product.';
                $messageType = 'error';
            }
            $stmt->close();
        }
    }
}

// Get product to edit
$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editProduct = getProductById($editId);
}

// Get all products
$products = $conn->query("SELECT p.*, s.name as supplier_name FROM products p LEFT JOIN suppliers s ON p.supplier_id = s.id ORDER BY p.created_at DESC");

// Get suppliers for dropdown
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY name");

include 'includes/header.php';
?>

<div class="admin-page-header">
    <h1>Product Management</h1>
    <p class="admin-welcome">Manage your product catalog, inventory, and suppliers</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Product Form -->
<div class="admin-form-section">
    <div class="section-header">
        <h2><?php echo $editProduct ? '✏️ Edit Product' : '➕ Add New Product'; ?></h2>
    </div>
    <div class="form-container" style="max-width: 100%; margin: 0;">
    <form method="POST" action="products.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $editProduct ? 'update' : 'add'; ?>">
        <?php if ($editProduct): ?>
            <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label>Product Image</label>
            <?php if ($editProduct && !empty($editProduct['image'])): ?>
                <div style="margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <img src="<?php echo getAssetUrl('uploads/' . htmlspecialchars($editProduct['image'])); ?>" alt="Current Image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd; display: block; margin-bottom: 0.5rem;" onerror="this.style.display='none';">
                    <p style="font-size: 0.9rem; color: #666;">Current image - Upload new image to replace</p>
                </div>
            <?php endif; ?>
            <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="padding: 0.5rem; border: 2px solid #ddd; border-radius: 8px; width: 100%;">
            <p style="font-size: 0.85rem; color: #666; margin-top: 0.5rem;">Max size: 5MB. Allowed formats: JPEG, PNG, GIF, WebP</p>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" value="<?php echo $editProduct ? htmlspecialchars($editProduct['category']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"><?php echo $editProduct ? htmlspecialchars($editProduct['description']) : ''; ?></textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Price *</label>
                <input type="number" name="price" step="0.01" required value="<?php echo $editProduct ? $editProduct['price'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Stock Quantity *</label>
                <input type="number" name="stock_quantity" required value="<?php echo $editProduct ? $editProduct['stock_quantity'] : 0; ?>">
            </div>
            
            <div class="form-group">
                <label>Supplier</label>
                <select name="supplier_id">
                    <option value="0">No Supplier</option>
                    <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                        <option value="<?php echo $supplier['id']; ?>" <?php echo ($editProduct && $editProduct['supplier_id'] == $supplier['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($supplier['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group checkbox-group">
            <label class="checkbox-label">
                <input type="checkbox" name="featured" <?php echo ($editProduct && $editProduct['featured']) ? 'checked' : ''; ?>>
                <span>⭐ Featured Product</span>
            </label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $editProduct ? '💾 Update Product' : '➕ Add Product'; ?></button>
            <?php if ($editProduct): ?>
                <a href="products.php" class="btn btn-secondary">❌ Cancel</a>
            <?php endif; ?>
        </div>
    </form>
    </div>
</div>

<!-- Products List -->
<div class="table-container">
    <h2>All Products</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Supplier</th>
                <th>Featured</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo getAssetUrl('uploads/' . htmlspecialchars($product['image'])); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 2px solid #ddd;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 60px; height: 60px; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 6px; font-size: 1.5rem;">📦</div>
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 6px; font-size: 1.5rem;">📦</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>
                            <span class="category-badge"><?php echo htmlspecialchars($product['category'] ?: 'N/A'); ?></span>
                        </td>
                        <td><strong><?php echo formatCurrency($product['price']); ?></strong></td>
                        <td>
                            <span class="stock-badge <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                <?php echo $product['stock_quantity']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($product['supplier_name'] ?: 'N/A'); ?></td>
                        <td>
                            <?php if ($product['featured']): ?>
                                <span class="featured-badge">⭐ Yes</span>
                            <?php else: ?>
                                <span class="not-featured">No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">✏️ Edit</a>
                                <form method="POST" action="products.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️ Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="empty-state">
                        <div style="padding: 2rem;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
                            <p style="color: var(--gray); font-size: 1.1rem;">No products found.</p>
                            <p style="color: var(--gray); font-size: 0.9rem; margin-top: 0.5rem;">Add your first product using the form above.</p>
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

