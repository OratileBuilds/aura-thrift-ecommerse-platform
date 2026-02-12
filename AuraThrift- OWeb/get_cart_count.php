<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth_check.php';

// Get user ID from POST data
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'count' => 0, 'message' => 'User not logged in']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT SUM(quantity) as total_items FROM shopping_cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $count = $row['total_items'] ?? 0;
    echo json_encode(['success' => true, 'count' => $count]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'count' => 0, 'message' => 'Error getting cart count']);
}
?>
