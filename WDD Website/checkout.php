<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

requireCustomerLogin();

$pageTitle = 'Checkout - FayasHardware';
$customerId = getCustomerId();
$conn = getDBConnection();

// Get cart from cookie - try multiple ways
$cart = [];
if (isset($_COOKIE['cart'])) {
    $cart = json_decode($_COOKIE['cart'], true);
    if (!is_array($cart)) {
        $cart = [];
    }
}

// Also try reading from request if cookie doesn't work
if (empty($cart) && isset($_REQUEST['cart_data'])) {
    $cart = json_decode($_REQUEST['cart_data'], true);
}

if (empty($cart)) {
    $_SESSION['checkout_error'] = 'Your cart is empty. Please add items to cart first.';
    header('Location: cart.php');
    exit();
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate cart items and stock
    $orderItems = [];
    $totalAmount = 0;
    $valid = true;
    
    if (!empty($cart) && is_array($cart)) {
        foreach ($cart as $item) {
            if (!isset($item['productId'])) {
                $valid = false;
                break;
            }
            
            $product = getProductById($item['productId']);
            if (!$product) {
                $valid = false;
                break;
            }
            
            $quantity = min($item['quantity'] ?? 1, $product['stock_quantity']);
            if ($quantity <= 0) {
                $valid = false;
                break;
            }
            
            $orderItems[] = [
                'product_id' => $product['id'],
                'quantity' => $quantity,
                'price' => $product['price']
            ];
            $totalAmount += $product['price'] * $quantity;
        }
    } else {
        $valid = false;
    }
    
    if ($valid && $totalAmount > 0) {
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("id", $customerId, $totalAmount);
        $stmt->execute();
        $orderId = $conn->insert_id;
        $stmt->close();
        
        // Create order items and update stock
        foreach ($orderItems as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();
            $stmt->close();
            
            // Update product stock
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
            $stmt->close();
        }
        
        // Clear cart
        setcookie('cart', '', time() - 3600, '/');
        
        // Also clear from JavaScript
        echo '<script>document.cookie = "cart=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";</script>';
        
        header('Location: order_success.php?id=' . $orderId);
        exit();
    } else {
        $error = 'Unable to process order. Please check your cart and ensure all items are in stock.';
    }
}

// Get cart items for display
$cartItems = [];
$total = 0;

if (!empty($cart) && is_array($cart)) {
    foreach ($cart as $item) {
        if (isset($item['productId'])) {
            $product = getProductById($item['productId']);
            if ($product) {
                $quantity = min($item['quantity'] ?? 1, $product['stock_quantity']);
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
    <h1 style="text-align: center; margin: 2rem 0;">Checkout</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['checkout_error'])): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['checkout_error']); unset($_SESSION['checkout_error']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($cartItems)): ?>
        <div class="alert alert-error">
            Your cart is empty. <a href="products.php">Continue Shopping</a>
        </div>
    <?php else: ?>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Order Summary -->
        <div>
            <div class="table-container">
                <h2 style="margin-bottom: 1.5rem; color: #667eea;">Order Summary</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $cartItem): 
                            $product = $cartItem['product'];
                            $quantity = $cartItem['quantity'];
                            $itemTotal = $product['price'] * $quantity;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo $quantity; ?></td>
                                <td><?php echo formatCurrency($product['price']); ?></td>
                                <td><?php echo formatCurrency($itemTotal); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; font-size: 1.2rem;">
                            <td colspan="3">Total:</td>
                            <td><?php echo formatCurrency($total); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Checkout Form -->
        <div>
            <div class="form-container">
                <h2 style="margin-bottom: 1.5rem; color: #667eea;">Confirm Order</h2>
                <form method="POST" action="checkout.php">
                    <p style="margin-bottom: 1.5rem; line-height: 1.8;">
                        Please review your order and click the button below to confirm your purchase.
                    </p>
                    <button type="submit" class="btn btn-success" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                        Confirm Order
                    </button>
                    <a href="cart.php" class="btn btn-secondary" style="width: 100%; margin-top: 1rem; text-align: center; display: block;">
                        Back to Cart
                    </a>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

