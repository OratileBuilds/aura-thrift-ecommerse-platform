<?php
session_start();
include 'config.php';
require_once 'includes/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'USER_NOT_LOGGED_IN', 'message' => 'You must be logged in to add items to your cart.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$product_id = $data['product_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Missing product ID']);
    exit;
}

// Check if product is already in the cart
$stmt = $conn->prepare("SELECT id, quantity FROM shopping_cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity
    $row = $result->fetch_assoc();
    $newQty = $row['quantity'] + 1;
    $updateStmt = $conn->prepare("UPDATE shopping_cart SET quantity = ?, added_at = NOW() WHERE id = ?");
    $updateStmt->bind_param("ii", $newQty, $row['id']);
    $updateStmt->execute();
} else {
    // Insert new item
    $insertStmt = $conn->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity, added_at) VALUES (?, ?, 1, NOW())");
    $insertStmt->bind_param("ii", $user_id, $product_id);
    $insertStmt->execute();
}

// Count updated items
$countStmt = $conn->prepare("SELECT SUM(quantity) as total FROM shopping_cart WHERE user_id = ?");
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();

$response = [
    'success' => true,
    'message' => 'Product added to cart',
    'cart_count' => $countResult['total'] ?? 0,
    'debug' => [
        'product_id' => $product_id,
        'user_id' => $user_id,
        'quantity' => $newQty ?? 1,
        'timestamp' => date('Y-m-d H:i:s')
    ]
];

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
echo json_encode($response);
?>
