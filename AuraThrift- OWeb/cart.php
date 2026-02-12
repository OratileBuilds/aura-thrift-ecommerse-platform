<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the new login page, and pass the current page as a redirect URL
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $conn->prepare("SELECT c.id AS cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image, p.stock 
                       FROM shopping_cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
?>

<?php 
$page_title = 'Your Shopping Cart';
include 'header.php'; // Corrected header path
?>

<main class="cart-page-container">
    <div class="cart-header">
        <h1>Your Cart</h1>
        <a href="products.php" class="continue-shopping-link">Continue Shopping</a>
    </div>

    <?php if (empty($cart_items)): ?>
        <div class="cart-empty">
            <p>Your cart is currently empty.</p>
            <a href="products.php" class="btn-primary">Discover Products</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items-list">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item-card">
                        <div class="cart-item-image">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="price">R<?php echo number_format($item['price'], 2); ?></p>
                            <form action="cart_handler.php" method="POST" class="quantity-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <label for="quantity-<?php echo $item['product_id']; ?>">Qty:</label>
                                <input type="number" id="quantity-<?php echo $item['product_id']; ?>" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="quantity-input">
                                <button type="submit" class="update-btn">Update</button>
                            </form>
                        </div>
                        <div class="cart-item-total">
                            <p>R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            <form action="cart_handler.php" method="POST" class="remove-form">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <button type="submit" class="remove-btn">Remove</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-line">
                    <span>Subtotal</span>
                    <span>R<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-line">
                    <span>Shipping</span>
                    <span>FREE</span>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span>R<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <form action="checkout.php" method="POST" style="margin: 0;">
                    <input type="hidden" name="cartData" value="<?php echo htmlspecialchars(json_encode($cart_items)); ?>">
                    <button type="submit" class="btn-primary checkout-btn">Proceed to Checkout</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include 'footer.php'; // Corrected footer path ?>