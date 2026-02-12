<?php
session_start();
include 'config.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=my_orders.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's orders from the database
// We join delivery_details and orders to get all info
$sql = "SELECT 
            d.id as order_id, 
            d.created_at as order_date, 
            d.status, 
            SUM(o.quantity * o.price) as total_amount,
            GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
        FROM delivery_details d
        JOIN orders o ON d.id = o.delivery_id
        JOIN products p ON o.product_id = p.id
        WHERE d.user_id = ?
        GROUP BY d.id
        ORDER BY d.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

$page_title = 'My Orders';
include 'header.php';
?>

<main class="my-orders-container">
    <div class="page-header">
        <h1>My Orders</h1>
        <p>View the status and details of your past orders.</p>
    </div>

    <div class="orders-list">
        <?php if (empty($orders)): ?>
            <div class="no-orders-message">
                <p>You haven't placed any orders yet.</p>
                <a href="index.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-info">
                            <span class="order-id">Order #<?php echo htmlspecialchars($order['order_id']); ?></span>
                            <span class="order-date">Placed on <?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
                        </div>
                        <div class="order-status">
                            <span class="status-badge status-<?php echo strtolower(htmlspecialchars($order['status'])); ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                        </div>
                    </div>
                    <div class="order-card-body">
                        <p class="product-summary"><strong>Items:</strong> <?php echo htmlspecialchars($order['product_names']); ?></p>
                    </div>
                    <div class="order-card-footer">
                        <span class="order-total">Total: R<?php echo number_format($order['total_amount'], 2); ?></span>
                        <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php
include 'footer.php';
?>
