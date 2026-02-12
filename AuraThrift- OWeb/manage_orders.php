<?php
session_start();
include 'config.php';

// --- Security Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header('Location: login.php');
    exit();
}

// --- Fetch All Orders ---
// This query joins multiple tables to get all necessary order details.
$sql = "
    SELECT 
        dd.id AS order_id,
        u.email AS customer_email,
        CONCAT(dd.address, ', ', dd.city, ', ', dd.postal_code) AS delivery_address,
        dd.created_at AS order_date,
        dd.status AS order_status,
        GROUP_CONCAT(CONCAT(p.name, ' (Qty: ', o.quantity, ')') SEPARATOR '<br>') AS products,
        SUM(o.quantity * o.price) AS total_price
    FROM delivery_details dd
    JOIN users u ON dd.user_id = u.id
    JOIN orders o ON dd.id = o.delivery_id
    JOIN products p ON o.product_id = p.id
    GROUP BY dd.id
    ORDER BY dd.created_at DESC;
";

$result = $conn->query($sql);

$page_title = 'Manage Orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - AuraThrift</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>AuraThrift Admin</h2>
            <ul>
                <li><a href="admindashboard.php">Dashboard</a></li>
                <li><a href="admin_approval.php">Product Approvals</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="manage_orders.php" class="active">Manage Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1><?php echo $page_title; ?></h1>

            <?php
            // --- Display Session Messages ---
            if (isset($_SESSION['message'])) {
                $message_type = $_SESSION['message_type'] ?? 'success'; // Default to success
                echo "<div class='alert {$message_type}'>{$_SESSION['message']}</div>";
                // Unset the session message to prevent it from showing on refresh
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>

            <p>Here you can view and track all customer orders.</p>

            <div class="table-container">
                <?php if ($result && $result->num_rows > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Delivery Address</th>
                                <th>Products</th>
                                <th>Total Price</th>
                                <th>Order Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['delivery_address']); ?></td>
                                    <td><?php echo $row['products']; ?></td>
                                    <td>R<?php echo number_format($row['total_price'], 2); ?></td>
                                    <td><?php echo date('F j, Y, g:i a', strtotime($row['order_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(htmlspecialchars($row['order_status'])); ?>">
                                            <?php echo htmlspecialchars($row['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="update_order_status.php" method="POST" class="status-form">
                                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                            <select name="status">
                                                <option value="Pending" <?php echo ($row['order_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Processing" <?php echo ($row['order_status'] == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                                                <option value="Shipped" <?php echo ($row['order_status'] == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="Delivered" <?php echo ($row['order_status'] == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="Cancelled" <?php echo ($row['order_status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" class="btn-update">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No orders have been placed yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
