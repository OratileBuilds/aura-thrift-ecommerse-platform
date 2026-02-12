<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/auth_check.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: login.php");
    exit;
}

// Get user's orders
$sql = "SELECT id, user_id, order_date, delivery_name, delivery_address, status
        FROM orders
        WHERE user_id = ?
        ORDER BY order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Tracking - AuraThrift</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .user-portal {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .user-portal h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .order-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .order-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .order-card h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .order-info {
            margin-bottom: 15px;
        }
        .order-info p {
            margin: 5px 0;
            color: #555;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
        }
        .status-pending {
            background: #ff9800;
        }
        .status-processing {
            background: #2196f3;
        }
        .status-delivered {
            background: #4caf50;
        }
        .status-cancelled {
            background: #f44336;
        }
        .order-items {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .order-items ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .order-items li {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .order-items li:last-child {
            border-bottom: none;
        }
        .total-amount {
            margin-top: 10px;
            font-size: 1.2em;
            color: #333;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="user-portal">
        <h1>My Orders</h1>
        
        <?php if ($result->num_rows === 0): ?>
            <p style="text-align: center; color: #555; margin-top: 30px;">
                You have no orders yet. Start shopping now!
            </p>
        <?php endif; ?>
        
        <div class="order-list">
            <?php while ($order = $result->fetch_assoc()): ?>
                <div class="order-card">
                    <h3>Order #<?= $order['id'] ?></h3>
                    <div class="order-info">
                        <p><strong>Status:</strong> <span class="status-badge status-<?= strtolower($order['status']) ?>"><?= ucfirst($order['status']) ?></span></p>
                        <p><strong>Ordered on:</strong> <?= date('F j, Y', strtotime($order['order_date'])) ?></p>
                        <p><strong>Delivery Name:</strong> <?= htmlspecialchars($order['delivery_name']) ?></p>
                        <p><strong>Delivery Address:</strong> <?= htmlspecialchars($order['delivery_address']) ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
