<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

$pageTitle = 'Products - FayasHardware';
$conn = getDBConnection();

// Get category filter
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT * FROM products WHERE stock_quantity > 0";
$params = [];
$types = "";

if ($category !== 'all') {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($search)) {
    $query .= " AND name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get all categories for filter
$categoriesQuery = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL";
$categoriesResult = $conn->query($categoriesQuery);

include 'includes/header.php';
?>

<div class="container">
    <h1 style="text-align: center; margin: 2rem 0;">All Products</h1>
    
    <!-- Search and Filter -->
    <div style="background: white; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <form method="GET" action="products.php" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label>Search Products</label>
                <input type="text" name="search" placeholder="Enter product name..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label>Category</label>
                <select name="category">
                    <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                    <?php while ($cat = $categoriesResult->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="products.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>
    
    <!-- Products Grid -->
    <div class="products-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($product = $result->fetch_assoc()): ?>
                <div class="product-card" data-category="<?php echo htmlspecialchars($product['category']); ?>">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?php echo getAssetUrl('uploads/' . htmlspecialchars($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="width: 100%; height: 100%; display: none; align-items: center; justify-content: center; font-size: 4rem; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                                📦
                            </div>
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 4rem; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                                📦
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-price"><?php echo formatCurrency($product['price']); ?></div>
                        <div class="product-stock">Stock: <?php echo $product['stock_quantity']; ?></div>
                        <?php if (!empty($product['description'])): ?>
                            <p style="font-size: 0.9rem; color: #666; margin: 0.5rem 0;"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                        <?php endif; ?>
                        <button class="btn btn-primary add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>', <?php echo $product['price']; ?>)" data-product-id="<?php echo $product['id']; ?>">
                            🛒 Add to Cart
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center; padding: 2rem;">No products found.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>

