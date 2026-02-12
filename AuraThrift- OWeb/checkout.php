<?php
session_start();
include 'config.php';

// User must be logged in to checkout
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to proceed to checkout.";
    header('Location: login.php?redirect=cart.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartData'])) {
    $cartData = json_decode($_POST['cartData'], true);
    $userId = $_SESSION['user_id'];

    if (empty($cartData)) {
        header('Location: cart.php');
        exit();
    }

    // Calculate total
    $total = 0;
    foreach ($cartData as $item) {
        if (isset($item['price'], $item['quantity'])) {
            $total += $item['price'] * $item['quantity'];
        }
    }

    // Save cart data to session
    $_SESSION['cart_total'] = $total;
    $_SESSION['cart_items'] = $cartData;

    // Redirect to delivery details page
    header("Location: delivery_details.php");
    exit;
} else {
    // If accessed directly or without data, redirect to cart
    header('Location: cart.php');
    exit;
}
?>
