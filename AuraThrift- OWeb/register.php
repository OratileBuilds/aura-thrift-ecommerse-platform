<?php
session_start();
include 'header.php'; // Use the consistent header

// Define error messages
$errors = [
    'empty' => 'All fields are required.',
    'invalid_email' => 'Please enter a valid email address.',
    'password_short' => 'Password must be at least 8 characters long.',
    'password_mismatch' => 'Passwords do not match.',
    'email_exists' => 'An account with this email already exists.',
    'db_error' => 'An unexpected error occurred. Please try again.'
];

$error_message = '';
if (isset($_GET['error']) && array_key_exists($_GET['error'], $errors)) {
    $error_message = $errors[$_GET['error']];
}
?>

<main class="auth-page">
    <div class="auth-container">
        <form class="auth-form" action="register_process.php" method="POST">
            <h2>Create Your Account</h2>
            <p>Join AuraThrift today!</p>
            
            <?php if ($error_message): ?>
                <div class="alert error" style="margin-bottom: 20px;"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
            </div>
            
            <button type="submit" class="btn-primary btn-block">Register</button>
            
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Log In</a></p>
            </div>
        </form>
    </div>
</main>

<?php include 'footer.php'; // Use the consistent footer ?>
