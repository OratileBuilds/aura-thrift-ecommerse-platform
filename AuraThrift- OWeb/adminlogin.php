<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email=? AND role='admin' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION["admin_logged_in"] = true;
            $_SESSION["admin_email"] = $user["email"];
            header("Location: admindashboard.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No admin account found.";
    }
}
?>
