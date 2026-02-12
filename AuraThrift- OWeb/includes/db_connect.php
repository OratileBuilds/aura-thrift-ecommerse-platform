<?php
$host = 'localhost';
$db = 'aurathift_db';
$user = 'root';
$pass = ''; // default for XAMPP users with no password

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
