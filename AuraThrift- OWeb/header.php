<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// If user is logged in and trying to access login page, redirect to home
if (isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) === 'login.php') {
    header('Location: index.php');
    exit;
}

// If user is not logged in and trying to access protected pages, redirect to login
$protected_pages = ['sell.php', 'my_orders.php', 'cart.php'];
$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['user_id']) && in_array($current_page, $protected_pages)) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo isset($page_title) ? $page_title : 'AuraThrift'; ?></title>
    <link rel="stylesheet" href="style.css?v=1.1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="menu-overlay"></div>
    <header>
        <div class="logo">
            <h1><a href="index.php">AuraThrift</a></h1>
        </div>
        <nav class="main-nav">
            <div class="nav-container">
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Browse</a></li>
                    <li><a href="sell.php">Sell now</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="my_orders.php">My Orders</a></li>
                        <li><a href="cart.php" class="cart-link">
                            <img src="images/OIP.jpeg" alt="Cart" height="20" class="cart-image">
                            <span id="cart-count">0</span>
                        </a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login/Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
        <div class="mobile-menu-toggle" id="mobileMenuToggle">&#9776;</div>
    </header>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const nav = document.querySelector('.main-nav');
            const navLinks = document.querySelector('.nav-links');
            const overlay = document.querySelector('.menu-overlay');

            mobileMenuToggle.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent click from propagating to document
                nav.classList.toggle('active');
                navLinks.classList.toggle('active');
                overlay.classList.toggle('active');
                this.classList.toggle('active');
            });

            // Add smooth scroll to navigation links
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('href');
                    
                    // Close mobile menu when link is clicked
                    nav.classList.remove('active');
                    navLinks.classList.remove('active');
                    overlay.classList.remove('active');
                    mobileMenuToggle.classList.remove('active');

                    // Smooth scroll to target
                    if (href !== '#') {
                        window.location.href = href;
                    }
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!nav.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                    nav.classList.remove('active');
                    navLinks.classList.remove('active');
                    overlay.classList.remove('active');
                    mobileMenuToggle.classList.remove('active');
                }
            });

            // Close menu when clicking on overlay
            overlay.addEventListener('click', function() {
                nav.classList.remove('active');
                navLinks.classList.remove('active');
                this.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
            });
        });
    </script>
