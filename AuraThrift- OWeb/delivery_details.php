<?php
session_start();
include 'config.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $total = $_SESSION['cart_total'] ?? 0;

    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postalCode = $_POST['postal_code'] ?? '';

    if (empty($address) || empty($city) || empty($postalCode)) {
        $_SESSION['error_message'] = "All delivery fields are required.";
        header("Location: delivery_details.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO delivery_details (user_id, address, city, postal_code, contact_number, alternative_number, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
    $alternativeNumber = $_POST['alternative_number'] ?? '';
    $stmt->bind_param("issssss", $userId, $address, $city, $postalCode, $_POST['contact_number'], $alternativeNumber, $_POST['payment_method']);
    $stmt->execute();

    $deliveryId = $conn->insert_id;
    $cartItems = $_SESSION['cart_items'] ?? [];
    
    foreach ($cartItems as $item) {
        $productId = $item['product_id'] ?? $item['id'];
        $stmt = $conn->prepare("INSERT INTO orders (delivery_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idid", $deliveryId, $productId, $item['quantity'], $item['price']);
        $stmt->execute();
    }

    unset($_SESSION['cart_items']);
    unset($_SESSION['cart_total']);

    header("Location: delivery_confirmed.php?total=" . urlencode($total));
    exit;
}

$page_title = 'Delivery Details';
include 'header.php';
?>

<main class="checkout-page-container">
    <div class="checkout-header">
        <h1>Checkout</h1>
    </div>

    <div class="checkout-layout">
        <div class="delivery-form-container">
            <h2>Shipping Information</h2>
            
            <?php
            if (isset($_SESSION['error_message'])) {
                echo "<div class='alert error'>{$_SESSION['error_message']}</div>";
                unset($_SESSION['error_message']);
            }
            ?>

            <form action="delivery_details.php" method="post" class="delivery-form">
                <div class="form-group">
                    <label for="address">Street Address</label>
                    <input type="text" name="address" id="address" placeholder="123 Main St" required>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" name="city" id="city" placeholder="Anytown" required>
                </div>
                <div class="form-group">
                    <label for="postal_code">Postal Code</label>
                    <input type="text" name="postal_code" id="postal_code" placeholder="12345" required>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="tel" name="contact_number" id="contact_number" placeholder="072 123 4567" required>
                    <small>For delivery confirmation and updates</small>
                </div>
                <div class="form-group">
                    <label for="alternative_number">Alternative Number (Optional)</label>
                    <input type="tel" name="alternative_number" id="alternative_number" placeholder="082 987 6543">
                    <small>Additional contact number for delivery</small>
                </div>
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select name="payment_method" id="payment_method" required>
                        <option value="cash_on_delivery">Cash on Delivery</option>
                        <!-- Add more payment methods if needed -->
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Confirm & Place Order</button>
                </div>
            </form>
        </div>

        <div class="checkout-summary">
            <h2>Order Summary</h2>
            <div class="order-items-summary">
                <?php
                $cartItems = $_SESSION['cart_items'] ?? [];
                if (empty($cartItems)) {
                    echo "<p>Your cart is empty.</p>";
                } else {
                    foreach ($cartItems as $item) {
                        echo "<div class='summary-item'>";
                        echo "<span>" . htmlspecialchars($item['name']) . " (×" . $item['quantity'] . ")</span>";
                        echo "<span>R" . number_format($item['price'] * $item['quantity'], 2) . "</span>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <span>R<?php echo number_format($_SESSION['cart_total'] ?? 0, 2); ?></span>
            </div>
            <div class="payment-note">
                <p>Payment is Cash on Delivery. Please have the exact amount ready.</p>
            </div>
        </div>
    </div>
</main>

<?php
include 'footer.php';
?>
