<?php
session_start();
include 'config.php';

// --- Security Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header('Location: login.php');
    exit();
}

// --- Handle POST Actions (Change Role, Delete User) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id_to_modify = $_POST['user_id'];

    // Prevent admin from modifying their own account
    if ($user_id_to_modify == $_SESSION['user_id']) {
        $error_message = 'Error: You cannot modify your own account.';
    } else {
        if (isset($_POST['action']) && $_POST['action'] == 'change_role') {
            $new_role = $_POST['new_role'];
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param('si', $new_role, $user_id_to_modify);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['action']) && $_POST['action'] == 'delete_user') {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $user_id_to_modify);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: manage_users.php'); // Refresh page
        exit();
    }
}

// --- Fetch All Users ---
$users_result = $conn->query("SELECT id, email, role, created_at FROM users ORDER BY created_at DESC");

$page_title = 'Manage Users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - AuraThrift</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>AuraThrift Admin</h2>
            <ul>
                <li><a href="admindashboard.php">Dashboard</a></li>
                <li><a href="admin_approval.php">Product Approvals</a></li>
                <li><a href="manage_users.php" class="active">Manage Users</a></li>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="manage_orders.php">Manage Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1><?php echo $page_title; ?></h1>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Email</th>
                            <th>Current Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form action="manage_users.php" method="post" style="display: inline-block;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="new_role">
                                                <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <button type="submit" name="action" value="change_role" class="btn-update">Update Role</button>
                                        </form>
                                        <form action="manage_users.php" method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="action" value="delete_user" class="btn-delete">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span>(Your Account)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
