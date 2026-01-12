<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

requireCustomerLogin();

$pageTitle = 'Shopping Cart - FayasHardware';
$conn = getDBConnection();

// Handle remove from cart
if (isset($_GET['remove'])) {
    $productId = intval($_GET['remove']);
    $cart = json_decode($_COOKIE['cart'] ?? '[]', true);
    $cart = array_filter($cart, function($item) use ($productId) {
        return $item['productId'] != $productId;
    });
    setcookie('cart', json_encode(array_values($cart)), time() + (86400 * 30), '/');
    header('Location: cart.php');
    exit();
}

// Get cart from cookie - try multiple ways
$cart = [];
if (isset($_COOKIE['cart'])) {
    $cartData = $_COOKIE['cart'];
    $cart = json_decode($cartData, true);
    if (!is_array($cart)) {
        $cart = [];
    }
}

$cartItems = [];
$total = 0;

if (!empty($cart)) {
    foreach ($cart as $item) {
        if (isset($item['productId'])) {
            $product = getProductById($item['productId']);
            if ($product) {
                $quantity = min($item['quantity'] ?? 1, $product['stock_quantity']); // Don't exceed stock
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $total += $product['price'] * $quantity;
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h1 style="text-align: center; margin: 2rem 0;">Shopping Cart</h1>
    
    <?php if (empty($cartItems)): ?>
        <div style="text-align: center; padding: 3rem; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <p style="font-size: 1.2rem; margin-bottom: 1rem;">Your cart is empty.</p>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div>
            <?php foreach ($cartItems as $cartItem): 
                $product = $cartItem['product'];
                $quantity = $cartItem['quantity'];
                $itemTotal = $product['price'] * $quantity;
            ?>
                <div class="cart-item">
                    <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                        <div style="width: 100px; height: 100px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                            <?php echo $product['image'] ? '<img src="' . getAssetUrl('uploads/' . htmlspecialchars($product['image'])) . '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 5px;">' : '📦'; ?>
                        </div>
                        <div>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p style="color: #666;"><?php echo formatCurrency($product['price']); ?> each</p>
                            <p style="color: #666;">Quantity: <?php echo $quantity; ?></p>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <p style="font-size: 1.2rem; font-weight: bold; margin-bottom: 1rem;">
                            <?php echo formatCurrency($itemTotal); ?>
                        </p>
                        <a href="cart.php?remove=<?php echo $product['id']; ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;">Remove</a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="cart-total">
                <h3>Total: <?php echo formatCurrency($total); ?></h3>
                <a href="checkout.php" class="btn btn-success" style="margin-top: 1rem; padding: 1rem 2rem; font-size: 1.1rem;">Proceed to Checkout</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

