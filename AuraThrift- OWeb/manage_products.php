<?php
session_start();
include 'config.php';

// --- Security Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header('Location: login.php');
    exit();
}

// --- Handle POST Actions (Delete Product) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_product') {
    $product_id_to_delete = $_POST['product_id'];

    // First, get the image path to delete the file
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param('i', $product_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($product = $result->fetch_assoc()) {
        if (file_exists($product['image'])) {
            unlink($product['image']); // Delete the image file
        }
    }
    $stmt->close();

    // Then, delete the product record from the database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $product_id_to_delete);
    $stmt->execute();
    $stmt->close();

    header('Location: manage_products.php'); // Refresh page
    exit();
}

// --- Fetch All Products ---
$products_result = $conn->query("SELECT p.*, u.email FROM products p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");

$page_title = 'Manage Products';
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
                <li><a href="manage_products.php" class="active">Manage Products</a></li>
                <li><a href="manage_orders.php">Manage Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1><?php echo $page_title; ?></h1>
            <div class="page-actions">
                <p>Here you can view, edit, and delete all products in the database.</p>
                <a href="sell.php" class="btn-add-new">+ Add New Product</a>
            </div>

            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Seller</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $products_result->fetch_assoc()): ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="" width="60"></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>R<?php echo number_format($product['price'], 2); ?></td>
                                <td><span class="status-badge status-<?php echo strtolower(htmlspecialchars($product['status'])); ?>"><?php echo htmlspecialchars($product['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($product['email'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-edit">Edit</a>
                                    <form action="manage_products.php" method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to permanently delete this product?');">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" name="action" value="delete_product" class="btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
