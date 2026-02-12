<?php
session_start();
require_once 'config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Store token in database
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $token, $expiry);
            
            if ($stmt->execute()) {
                // Send reset email
                $reset_url = "http://localhost/AuraThrift- OWeb/reset-password.php?token=" . $token;
                
                $subject = "Password Reset Request - AuraThrift";
                $body = "Hello,\n\nYou have requested to reset your password. Please click the link below to reset your password:\n\n" . $reset_url . "\n\nIf you did not request this password reset, please ignore this email.\n\nThis link will expire in 1 hour.";
                
                if (send_email($email, $subject, $body)) {
                    $message = "We've sent you an email with instructions to reset your password.";
                } else {
                    $error = "Failed to send reset email. Please try again later.";
                }
            } else {
                $error = "Failed to process your request. Please try again later.";
            }
        } else {
            $error = "No account found with this email address.";
        }
        
        $stmt->close();
    }
}

$page_title = 'Forgot Password';
include 'header.php';
?>

<div class="auth-page">
    <div class="auth-container">
        <h2>Forgot Password</h2>
        
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
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <button type="submit" class="btn-primary">Send Reset Link</button>
        </form>
        
        <p class="back-link">
            <a href="login.php">Back to Login</a>
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>
