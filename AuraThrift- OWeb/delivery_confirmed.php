<?php
session_start();
include 'config.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$total = $_GET['total'] ?? '0.00';

$page_title = 'Order Confirmation';
include 'header.php';
?>

<main class="confirmation-page-container">
    <div class="confirmation-box">
        <div class="confirmation-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <h1>Thank You For Your Order!</h1>
        <div class="order-summary">
            <div class="order-details">
                <p><strong>Order Number:</strong> <?php echo $conn->insert_id; ?></p>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y'); ?></strong></p>
                <p><strong>Payment Method:</strong> Cash on Delivery</p>
                <p><strong>Delivery Status:</strong> Processing</p>
            </div>
            <div class="total-amount">
                <h3>Total Amount:</h3>
                <span>R<?php echo number_format((float)$total, 2); ?></span>
            </div>
            <div class="delivery-info">
                <h3>Delivery Information</h3>
                <p>A delivery driver will contact you via phone to confirm your delivery details.</p>
                <p>Please ensure you have the exact amount ready for cash on delivery.</p>
                <p>Our delivery team will contact you within 24-48 hours to confirm your delivery date and time.</p>
            </div>
        </div>
        <div class="next-steps">
            <h3>What Happens Next?</h3>
            <ul>
                <li>Our team will review your order</li>
                <li>A delivery driver will contact you to confirm details</li>
                <li>You'll receive a confirmation call before delivery</li>
                <li>Delivery will be made within 2-3 business days</li>
            </ul>
        </div>
        <div class="confirmation-actions">
            <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            <a href="my_orders.php" class="btn btn-primary">Track Your Order</a>
        </div>
    </div>
</main>

<?php
include 'footer.php';
?>
