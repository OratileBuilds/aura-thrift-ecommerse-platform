<?php
session_start();
include 'includes/config.php';

// Get form data
$fullName = $_POST['full_name'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    die("User not logged in");
}

// Step 1: Insert into orders table
$orderQuery = "INSERT INTO orders (user_id, delivery_name, delivery_phone, delivery_address)
               VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("isss", $userId, $fullName, $phone, $address);
$stmt->execute();
$orderId = $stmt->insert_id;
$stmt->close();

// Step 2: Fetch cart items and calculate total
$cartQuery = "SELECT sc.product_id, sc.quantity, p.price 
              FROM shopping_cart sc 
              JOIN products p ON sc.product_id = p.id 
              WHERE sc.user_id = ?";
$stmt = $conn->prepare($cartQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$totalAmount = 0;
while ($row = $result->fetch_assoc()) {
    $totalAmount += $row['price'] * $row['quantity'];
}
$stmt->close();

// Optional: You could copy cart items to order_items table here

// Step 3: Redirect with total
header("Location: delivery_confirmed.php?total=" . number_format($totalAmount, 2));
exit;
?>

