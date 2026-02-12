<?php
session_start();
include 'config.php';

// --- Security Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header('Location: login.php');
    exit();
}

// --- Handle 'Mark as Sold' Action ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'mark_sold') {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("UPDATE products SET status = 'sold' WHERE id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admindashboard.php'); // Refresh to see changes
    exit();
}

// --- Fetch Dashboard Stats ---
$pending_count = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'pending'")->fetch_assoc()['count'];
$approved_count = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'approved'")->fetch_assoc()['count'];
$sold_count = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'sold'")->fetch_assoc()['count'];

// --- Fetch Approved Products to Manage ---
$approved_products_result = $conn->query("SELECT * FROM products WHERE status = 'approved' ORDER BY created_at DESC");

$page_title = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - AuraThrift</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>AuraThrift Admin</h2>
            <ul>
                <li><a href="admindashboard.php" class="active">Dashboard</a></li>
                <li><a href="admin_approval.php">Product Approvals</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="manage_orders.php">Manage Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?> 👋</h1>
            
            <div class="cards">
                <div class="card">
                    <h3>Pending Approval</h3>
                    <p><?php echo $pending_count; ?></p>
                    <a href="admin_approval.php">View Items</a>
                </div>
                <div class="card">
                    <h3>Products for Sale</h3>
                    <p><?php echo $approved_count; ?></p>
                </div>
                <div class="card">
                    <h3>Products Sold</h3>
                    <p><?php echo $sold_count; ?></p>
                </div>
            </div>

            <div class="table-container">
                <h2>Manage Available Products</h2>
                <?php if ($approved_products_result->num_rows > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $approved_products_result->fetch_assoc()): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" width="80"></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>R<?php echo number_format($product['price'], 2); ?></td>
                                    <td>
                                        <form action="admindashboard.php" method="post">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="action" value="mark_sold" class="btn-sold">Mark as Sold</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>There are no products currently for sale.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
