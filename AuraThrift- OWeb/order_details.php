<?php
session_start();
include 'config.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id === 0) {
    header("Location: my_orders.php");
    exit();
}

// Fetch the main order details and shipping info
$sql = "SELECT * FROM delivery_details WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

// If order not found or doesn't belong to the user, redirect
if (!$order) {
    header("Location: my_orders.php");
    exit();
}

// Fetch the items associated with this order
$sql_items = "SELECT o.quantity, o.price, p.name, p.image 
             FROM orders o 
             JOIN products p ON o.product_id = p.id 
             WHERE o.delivery_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

$page_title = 'Order Details';
include 'header.php';
?>

<main class="order-details-container">
    <div class="page-header">
        <h1>Order #<?php echo htmlspecialchars($order['id']); ?></h1>
        <a href="my_orders.php">&larr; Back to My Orders</a>
    </div>

    <div class="order-details-layout">
        <div class="order-items-detailed">
            <h3>Items in this Order</h3>
            <?php 
            $total = 0;
            foreach ($order_items as $item): 
                $item_total = $item['price'] * $item['quantity'];
                $total += $item_total;
            ?>
                <div class="order-item-detailed-card">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="" class="item-image">
                    <div class="item-info">
                        <p class="item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                        <p class="item-qty-price">Qty: <?php echo $item['quantity']; ?> &nbsp;&nbsp;&nbsp; Price: R<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                    <p class="item-total">R<?php echo number_format($item_total, 2); ?></p>
                </div>
            <?php endforeach; ?>
            <div class="order-grand-total">
                <span>Grand Total</span>
                <span>R<?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <div class="order-summary-detailed">
            <h3>Order Summary</h3>
            <div class="summary-card">
                <p><strong>Order Status:</strong> <span class="status-badge status-<?php echo strtolower(htmlspecialchars($order['status'])); ?>"><?php echo htmlspecialchars($order['status']); ?></span></p>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                <hr>
                <h4>Shipping Address</h4>
                <p><?php echo htmlspecialchars($order['address']); ?><br>
                   <?php echo htmlspecialchars($order['city']); ?><br>
                   <?php echo htmlspecialchars($order['postal_code']); ?></p>
            </div>
        </div>
    </div>
</main>

<?php
include 'footer.php';
?>
