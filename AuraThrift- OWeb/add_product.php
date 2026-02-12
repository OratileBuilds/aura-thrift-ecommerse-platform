<?php
// Check if the GD image processing library is enabled
if (!extension_loaded('gd')) {
    die('Error: The GD image library is not enabled in your PHP configuration. Please edit your php.ini file, remove the semicolon from the line that says ";extension=gd", and restart your Apache server.');
}

session_start();
include 'config.php';

// --- Image Resizing Function ---
function resize_image($file_path, $max_width, $max_height) {
    list($width, $height, $type) = getimagesize($file_path);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($file_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($file_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($file_path);
            break;
        default:
            return false; // Unsupported type
    }

    // Calculate new dimensions while preserving aspect ratio
    $ratio = $width / $height;
    if ($max_width / $max_height > $ratio) {
        $new_width = $max_height * $ratio;
        $new_height = $max_height;
    } else {
        $new_height = $max_width / $ratio;
        $new_width = $max_width;
    }

    $new_image = imagecreatetruecolor($new_width, $new_height);

    // Handle transparency for PNG files
    if ($type == IMAGETYPE_PNG) {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
        imagealphablending($new_image, true);
    }

    imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // Overwrite the original file
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($new_image, $file_path, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($new_image, $file_path, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($new_image, $file_path);
            break;
    }

    imagedestroy($source_image);
    imagedestroy($new_image);

    return true;
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $sport = $_POST['sport'];

    // --- Image Upload Handling ---
    $target_dir = 'images/'; // Your folder for storing product images
    $image_name = basename($_FILES['image']['name']);
    $image_file_type = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    
    // Create a unique name for the image to prevent overwriting files
    $unique_image_name = uniqid('', true) . '.' . $image_file_type;
    $target_file = $target_dir . $unique_image_name;
    $upload_ok = 1;

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES['image']['tmp_name']);
    if ($check === false) {
        die('Error: File is not an image.');
    }

    // Check file size (e.g., 5MB limit)
    if ($_FILES['image']['size'] > 5000000) {
        die('Error: Sorry, your file is too large.');
    }

    // Allow certain file formats
    if ($image_file_type != 'jpg' && $image_file_type != 'png' && $image_file_type != 'jpeg' && $image_file_type != 'gif') {
        die('Error: Sorry, only JPG, JPEG, PNG & GIF files are allowed.');
    }

    // Try to upload file
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        // --- Resize the uploaded image ---
        resize_image($target_file, 500, 500); // Resize to fit within 500x500
    } else {
        die('Error: Sorry, there was an error uploading your file.');
    }

    // --- Database Insertion ---
    // IMPORTANT: Assumes your 'products' table has 'user_id' and 'status' columns.
    $status = 'pending'; // Set default status for admin approval

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, size, sport, image, user_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssdsssds', $name, $description, $price, $size, $sport, $target_file, $user_id, $status);

    if ($stmt->execute()) {
        header('Location: sell.php?status=success');
        exit();
    } else {
        header('Location: sell.php?status=error');
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    // If not a POST request, redirect to the form
    header('Location: sell.php');
    exit;
}
?>
