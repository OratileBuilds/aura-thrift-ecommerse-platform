<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';
// If user is already logged in, redirect them
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && strtolower(trim($_SESSION['role'])) === 'admin') {
        header('Location: admindashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$error_message = '';

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
        if (!$stmt) {
            $error_message = 'A database error occurred. Please try again later.';
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // --- Login Success ---
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $user['role'];

                    // --- Redirect based on role ---
                    if (isset($user['role']) && strtolower(trim($user['role'])) === 'admin') {
                        header('Location: admindashboard.php');
                    } else {
                        header('Location: index.php');
                    }
                    exit;
                } else {
                    $error_message = 'The password you entered is incorrect.';
                }
            } else {
                $error_message = 'No account found with that email address.';
            }
            $stmt->close();
        }
        $conn->close();
    }
}

// --- Display Page ---
$page_title = 'Login - AuraThrift';
include 'header.php';
?>

<main class="container auth-page">
    <div class="auth-container">
        <form class="auth-form" action="login.php" method="POST">
            <h2>Welcome Back!</h2>
            <p>Log in to continue your thrifting journey.</p>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
            <div class="form-footer">
                <p>Don't have an account? <a href="register.php">Sign up now</a></p>
                <p><a href="password_reset_request.php">Forgot your password?</a></p>
            </div>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
