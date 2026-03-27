<?php
if (!isset($_SESSION['admin']) && !(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

$alertMessage = "";

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ---------- ADD USER ----------
    if (isset($_POST['add_user'])) {
        $username = mysqli_real_escape_string($conn, trim($_POST['username']));
        $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $password = $_POST['password'];

        // Validate
        if (empty($username) || empty($full_name) || empty($role)) {
            $alertMessage = "Please fill all required fields.";
        } else {
            // Check if username already exists
            $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
            if (mysqli_num_rows($check) > 0) {
                $alertMessage = "Username already exists.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO users (username, password, full_name, role) VALUES ('$username', '$hashedPassword', '$full_name', '$role')";
                if (mysqli_query($conn, $query)) {
                    if (function_exists('logActivity')) {
                        logActivity($_SESSION['user_id'], "Added user", "Username: $username, Role: $role");
                    }
                    $alertMessage = "User added successfully.";
                } else {
                    $alertMessage = "Database error: " . mysqli_error($conn);
                }
            }
        }
    }

    // ---------- EDIT USER ----------
    elseif (isset($_POST['edit_user'])) {
        $id = (int) $_POST['id'];
        $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
        $role = mysqli_real_escape_string($conn, $_POST['role']);

        if (empty($full_name) || empty($role)) {
            $alertMessage = "Full name and role are required.";
        } else {
            $query = "UPDATE users SET full_name = '$full_name', role = '$role' WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                if (function_exists('logActivity')) {
                    logActivity($_SESSION['user_id'], "Edited user", "User ID: $id");
                }
                $alertMessage = "User updated successfully.";
            } else {
                $alertMessage = "Database error: " . mysqli_error($conn);
            }
        }
    }

    // ---------- RESET PASSWORD ----------
    elseif (isset($_POST['reset_password'])) {
        $id = (int) $_POST['id'];
        $defaultPassword = "12345678";
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

        $query = "UPDATE users SET password = '$hashedPassword' WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            if (function_exists('logActivity')) {
                logActivity($_SESSION['user_id'], "Reset password", "User ID: $id");
            }
            $alertMessage = "Password reset to default: '12345678'.";
        } else {
            $alertMessage = "Database error: " . mysqli_error($conn);
        }
    }

    // ---------- DELETE USER ----------
    elseif (isset($_POST['delete_user'])) {
        $id = (int) $_POST['id'];
        // Prevent self-deletion
        if ($id == $_SESSION['user_id']) {
            $alertMessage = "You cannot delete your own account.";
        } else {
            $query = "DELETE FROM users WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                if (function_exists('logActivity')) {
                    logActivity($_SESSION['user_id'], "Deleted user", "User ID: $id");
                }
                $alertMessage = "User deleted successfully.";
            } else {
                $alertMessage = "Database error: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch all users (excluding passwords for display)
$users = [];
$result = mysqli_query($conn, "SELECT id, username, full_name, role, created_at FROM users ORDER BY id");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
</head>
<body>
    <?php if (!empty($alertMessage)): ?>
        <script>
            alert('<?php echo addslashes($alertMessage); ?>');
        </script>
    <?php endif; ?>

    <h2>User Management</h2>

    <!-- Add User Form -->
    <h3>Add New User</h3>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="text" name="full_name" placeholder="Full Name" required>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="cashier">Cashier</option>
            <option value="waiter">Waiter</option>
            <option value="kitchen">Kitchen Staff</option>
        </select>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="add_user">Add User</button>
    </form>

    <!-- List Users with inline edit -->
    <h3>Existing Users</h3>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required></td>
                    <td>
                        <select name="role" required>
                            <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                            <option value="manager" <?php if($user['role'] == 'manager') echo 'selected'; ?>>Manager</option>
                            <option value="cashier" <?php if($user['role'] == 'cashier') echo 'selected'; ?>>Cashier</option>
                            <option value="waiter" <?php if($user['role'] == 'waiter') echo 'selected'; ?>>Waiter</option>
                            <option value="kitchen" <?php if($user['role'] == 'kitchen') echo 'selected'; ?>>Kitchen Staff</option>
                        </select>
                    </td>
                    <td><?php echo $user['created_at']; ?></td>
                    <td>
                        <button type="submit" name="edit_user">Update</button>
                        <button type="submit" name="reset_password" onclick="return confirm('Reset password for <?php echo addslashes($user['username']); ?>? The new password will be \'password123\'.');">Reset Password</button>
                        <button type="submit" name="delete_user" onclick="return confirm('Delete user <?php echo addslashes($user['username']); ?>?');">Delete</button>
                    </td>
                </tr>
            </form>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>