<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aurathift_db";

// First, connect without specifying the database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    $error = "Connection failed: " . $conn->connect_error;
    header("Content-Type: text/html");
    echo "<div class='error-message'>";
    echo "<h2>Database Connection Error</h2>";
    echo "<p>Error: " . htmlspecialchars($error) . "</p>";
    echo "<p>Please check if MySQL is running and the database exists.</p>";
    echo "</div>";
    exit;
}

// Try to select the database
if (!$conn->select_db($dbname)) {
    $error = "Could not select database: " . $conn->error;
    header("Content-Type: text/html");
    echo "<div class='error-message'>";
    echo "<h2>Database Selection Error</h2>";
    echo "<p>Error: " . htmlspecialchars($error) . "</p>";
    echo "<p>Please check if the database exists and create it if necessary.</p>";
    echo "</div>";
    exit;
}
?>
