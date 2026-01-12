<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

$pageTitle = 'Home - FayasHardware';
$conn = getDBConnection();

// Get featured products
$featuredQuery = "SELECT * FROM products WHERE featured = 1 AND stock_quantity > 0 LIMIT 6";
$featuredResult = $conn->query($featuredQuery);

include 'includes/header.php';
?>

<div class="hero-section">
    <div class="hero-background"></div>
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Quality Tools for Professionals & DIYers</h1>
            <p class="hero-tagline">Get the best hardware solutions with 25 years of trusted service</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn btn-shop-now">Shop Now</a>
                <a href="about.php" class="btn btn-services">Our Services</a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <h2 style="text-align: center; margin-bottom: 2rem;">Featured Products</h2>
    
    <div class="products-grid">
        <?php if ($featuredResult->num_rows > 0): ?>
            <?php while ($product = $featuredResult->fetch_assoc()): ?>
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
                        <button class="btn btn-primary add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>', <?php echo $product['price']; ?>)" data-product-id="<?php echo $product['id']; ?>">
                            🛒 Add to Cart
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No featured products available at the moment.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

