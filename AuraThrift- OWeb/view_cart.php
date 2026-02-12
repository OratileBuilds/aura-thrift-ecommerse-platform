<?php
session_start();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Your cart is empty.";
} else {
    foreach ($_SESSION['cart'] as $id => $item) {
        echo "<p>{$item['name']} - R{$item['price']} <a href='remove_from_cart.php?id=$id'>Remove</a></p>";
    }
}
?>
