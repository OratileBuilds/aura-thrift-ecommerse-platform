<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header('Location: login.php?error=empty');
    exit;
}

$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
if (!$stmt) {
    header('Location: login.php?error=db_error');
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $user['role'];

        if (isset($user['role']) && strtolower(trim($user['role'])) === 'admin') {
            header('Location: admindashboard.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        header('Location: login.php?error=incorrect_password');
        exit;
    }
} else {
    header('Location: login.php?error=email_not_found');
    exit;
}

$stmt->close();
$conn->close();
