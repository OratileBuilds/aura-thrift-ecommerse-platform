<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/auth_check.php';

// Get user ID from POST data
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT 
        sc.id as cart_id,
        sc.product_id,
        sc.quantity,
        p.name,
        p.price,
        p.image
    FROM shopping_cart sc
    JOIN products p ON sc.product_id = p.id
    WHERE sc.user_id = ?
    ORDER BY sc.added_at DESC");
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cartItems = [];
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = [
            'id' => $row['cart_id'],
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => $row['quantity'],
            'image' => $row['image']
        ];
    }
    
    echo json_encode(['success' => true, 'cartItems' => $cartItems]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading cart: ' . $e->getMessage()]);
}
?>
