<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// We will add the product listing form here later.
$page_title = 'Sell Your Item';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - AuraThrift</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <?php
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            echo '<div class="alert success">Success! Your product has been submitted for approval.</div>';
        } else {
            echo '<div class="alert error">Error! There was a problem submitting your product.</div>';
        }
    }
    ?>

    <div class="container">
        <h1>Welcome to Your Seller Dashboard</h1>
        <p>You can list your products for sale using the form below.</p>
        
        <form action="add_product.php" method="post" enctype="multipart/form-data" class="product-form">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price (R)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="size">Size</label>
                <input type="text" id="size" name="size" required>
            </div>
            <div class="form-group">
                <label for="sport">Sport/Category</label>
                <input type="text" id="sport" name="sport" required>
            </div>
            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            <button type="submit" class="btn">List My Item</button>
        </form>

    </div>

    <?php include 'footer.php'; ?>

</body>
</html>
