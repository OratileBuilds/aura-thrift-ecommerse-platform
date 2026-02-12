<?php
session_start();
include 'config.php';

// --- Security Check: Ensure user is an admin ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    // Redirect non-admins to the login page
    header('Location: login.php');
    exit();
}

// --- Handle POST Request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];

    // --- Validate Status: Prevent arbitrary values ---
    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    if (!in_array($newStatus, $allowed_statuses)) {
        // If status is not valid, redirect with an error message
        $_SESSION['message'] = "Invalid status value provided.";
        $_SESSION['message_type'] = 'error';
        header('Location: manage_orders.php');
        exit();
    }

    // --- Update Database ---
    // Prepare and execute the SQL statement to update the order status
    $stmt = $conn->prepare("UPDATE delivery_details SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $newStatus, $orderId);

    if ($stmt->execute()) {
        // On success, set a success message
        $_SESSION['message'] = "Order status updated successfully.";
        $_SESSION['message_type'] = 'success';
    } else {
        // On failure, set an error message
        $_SESSION['message'] = "Error updating order status: " . $conn->error;
        $_SESSION['message_type'] = 'error';
    }

    $stmt->close();
    $conn->close();

} else {
    // If the request is not a valid POST, set an error message
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['message_type'] = 'error';
}

// --- Redirect back to the manage orders page ---
header('Location: manage_orders.php');
exit();
?>
