<?php
session_start();
require_once 'config.php';

// Check if token is provided
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header('Location: password-reset-request.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Please enter both password fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if token is valid and not expired
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $email = $row['email'];
            
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Update user's password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            
            if ($stmt->execute()) {
                // Delete the used token
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                
                $message = "Your password has been successfully updated. You can now log in.";
            } else {
                $error = "Failed to update password. Please try again later.";
            }
        } else {
            $error = "Invalid or expired password reset link.";
        }
        
        $stmt->close();
    }
}

$page_title = 'Reset Password';
include 'header.php';
?>

<div class="auth-page">
    <div class="auth-container">
        <h2>Reset Password</h2>
        
        <?php if ($message): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <button type="submit" class="btn-primary">Update Password</button>
        </form>
        
        <p class="back-link">
            <a href="login.php">Back to Login</a>
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>
