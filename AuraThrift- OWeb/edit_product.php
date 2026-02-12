<?php
session_start();
include 'config.php';

// --- Security Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header('Location: login.php');
    exit();
}

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    header('Location: manage_products.php');
    exit();
}

// --- Handle Form Submission (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $sport = $_POST['sport'];
    $status = $_POST['status'];

    // Handle image upload if a new one is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // (Optional) Add resizing logic here if needed, similar to add_product.php
        $target_dir = 'images/';
        $image_name = uniqid('', true) . '.' . strtolower(pathinfo(basename($_FILES['image']['name']), PATHINFO_EXTENSION));
        $target_file = $target_dir . $image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);

        // Delete old image
        $old_image = $_POST['current_image'];
        if (file_exists($old_image)) {
            unlink($old_image);
        }

        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, size = ?, sport = ?, status = ?, image = ? WHERE id = ?");
        $stmt->bind_param('ssdssssi', $name, $description, $price, $size, $sport, $status, $target_file, $product_id);
    } else {
        // Update without changing the image
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, size = ?, sport = ?, status = ? WHERE id = ?");
        $stmt->bind_param('ssdsssi', $name, $description, $price, $size, $sport, $status, $product_id);
    }

    $stmt->execute();
    $stmt->close();

    header('Location: manage_products.php?status=updated');
    exit();
}

// --- Fetch Product Details (GET) ---
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die('Product not found.');
}

$page_title = 'Edit Product';
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
                <li><a href="admin_approval.php">Product Approvals</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_products.php" class="active">Manage Products</a></li>
                <li><a href="manage_orders.php">Manage Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1><?php echo $page_title; ?>: <?php echo htmlspecialchars($product['name']); ?></h1>

            <div class="form-container">
                <form action="edit_product.php?id=<?php echo $product['id']; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (R)</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="size">Size</label>
                        <input type="text" id="size" name="size" value="<?php echo htmlspecialchars($product['size']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="sport">Sport</label>
                        <input type="text" id="sport" name="sport" value="<?php echo htmlspecialchars($product['sport']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="pending" <?php echo ($product['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo ($product['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="sold" <?php echo ($product['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <p>Current Image:</p>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Current Image" width="150">
                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">
                        <p>Upload new image (optional):</p>
                        <input type="file" id="image" name="image">
                    </div>
                    <button type="submit" class="btn-submit">Save Changes</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
