<?php
session_start();
include 'config.php';

// --- Security Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header('Location: login.php');
    exit();
}

// --- Handle Form Submissions (Approve/Reject) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $action = $_POST['action'];

    $new_status = ($action == 'approve') ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE products SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $new_status, $product_id);
    $stmt->execute();
    $stmt->close();

    header('Location: admin_approval.php');
    exit;
}

// --- Fetch Pending Products ---
$stmt = $conn->prepare("SELECT p.*, u.email FROM products p JOIN users u ON p.user_id = u.id WHERE p.status = 'pending' ORDER BY p.created_at DESC");
$stmt->execute();
$pending_products_result = $stmt->get_result();
$stmt->close();

$page_title = 'Product Approvals';
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
                <li><a href="admindashboard.php">Dashboard</a></li>
                <li><a href="admin_approval.php" class="active">Product Approvals</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="manage_orders.php">Manage Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1><?php echo $page_title; ?></h1>
            <p>Review the items below submitted by users. Approve them to make them live on the site.</p>

            <div class="table-container">
                <?php if ($pending_products_result->num_rows > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Submitted By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $pending_products_result->fetch_assoc()): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" width="80"></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>R<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['email']); ?></td>
                                    <td>
                                        <form action="admin_approval.php" method="post" style="display: inline-block;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
                                        </form>
                                        <form action="admin_approval.php" method="post" style="display: inline-block;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>There are no products currently awaiting approval.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
