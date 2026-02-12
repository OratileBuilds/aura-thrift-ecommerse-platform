<?php
include 'config.php';

// Fetch all products for New Arrivals
$stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT 8");
$stmt->execute();
$result = $stmt->get_result();
$new_arrivals = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$page_title = 'Welcome to AuraThrift';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">

</head>
<body>
    <?php include 'header.php'; ?>

    <section class="hero">
        <div class="hero-slider">
            <img src="images/son2.jpg" class="hero-image" id="heroImage" alt="Slideshow Image">
        </div>
        <div class="hero-text">
            <h2>Pre-Owned Performance, Unbeatable Prices!</h2>
            <p>Buy, Sell and Connect with sports Enthusiasts in your community</p>
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Search for Sports gear...">
                <button id="search-button">Search</button>
            </div>
        </div>
    </section>

    <section id="new-arrivals" class="browse-page">
        <h2>New Arrivals</h2>
        <div class="products-grid">
            <?php if (empty($new_arrivals)): ?>
                <p>No new arrivals at the moment. Check back soon!</p>
            <?php else: ?>
                <?php foreach ($new_arrivals as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="price">R<?php echo number_format($product['price'], 2); ?></p>
                        <button type="button" class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                            Add to Cart
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="js/home.js"></script>
</body>
</html>