<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Basic validation
if (empty($email) || empty($password) || empty($confirm_password)) {
    header('Location: register.php?error=empty');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.php?error=invalid_email');
    exit;
}

if (strlen($password) < 8) {
    header('Location: register.php?error=password_short');
    exit;
}

if ($password !== $confirm_password) {
    header('Location: register.php?error=password_mismatch');
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    header('Location: register.php?error=email_exists');
    exit;
}
$stmt->close();

// Insert new user
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = 'user'; // Default role

$stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $hashed_password, $role);

if ($stmt->execute()) {
    // Redirect to login page with a success message
    header('Location: login.php?success=registered');
    exit;
} else {
    // Generic database error
    header('Location: register.php?error=db_error');
    exit;
}

$stmt->close();
$conn->close();
