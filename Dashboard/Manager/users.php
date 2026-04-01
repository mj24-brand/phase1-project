<?php
// users.php
$alertMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit_user'])) {
        $id = (int) $_POST['id'];
        $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        if ($id == $_SESSION['user_id']) {
            $alertMessage = "You cannot change your own role.";
        } else {
            $query = "UPDATE users SET full_name='$full_name', role='$role' WHERE id=$id";
            if (mysqli_query($conn, $query)) {
                $alertMessage = "User updated.";
            } else {
                $alertMessage = "Error: " . mysqli_error($conn);
            }
        }
    } elseif (isset($_POST['reset_password'])) {
        $id = (int) $_POST['id'];
        $defaultPassword = "12345678";
        $hashed = md5($defaultPassword);
        $query = "UPDATE users SET password='$hashed' WHERE id=$id";
        if (mysqli_query($conn, $query)) {
            $alertMessage = "Password reset to '12345678'.";
        } else {
            $alertMessage = "Error resetting password.";
        }
    }
}

$users = [];
$result = mysqli_query($conn, "SELECT id, username, full_name, role, created_at FROM users ORDER BY id");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>
<div class="subpage-header">
    <h2>Staff Management</h2>
    <p>View staff accounts, edit roles, and reset passwords.</p>
</div>

<?php if (!empty($alertMessage)): ?>
    <div class="alert alert-info"><?= htmlspecialchars($alertMessage) ?></div>
<?php endif; ?>

<table border="1" cellpadding="8">
    <thead>
        <tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Created At</th><th>Actions</th> </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required></td>
                <td>
                    <select name="role" <?= ($user['id'] == $_SESSION['user_id']) ? 'disabled' : '' ?>>
                        <option value="admin"   <?= $user['role'] == 'admin'   ? 'selected' : '' ?>>Admin</option>
                        <option value="manager" <?= $user['role'] == 'manager' ? 'selected' : '' ?>>Manager</option>
                        <option value="cashier" <?= $user['role'] == 'cashier' ? 'selected' : '' ?>>Cashier</option>
                        <option value="waiter"  <?= $user['role'] == 'waiter'  ? 'selected' : '' ?>>Waiter</option>
                        <option value="kitchen" <?= $user['role'] == 'kitchen' ? 'selected' : '' ?>>Kitchen Staff</option>
                    </select>
                </td>
                <td><?= $user['created_at'] ?></td>
                <td>
                    <button type="submit" name="edit_user">Update</button>
                    <button type="submit" name="reset_password" onclick="return confirm('Reset password for <?= htmlspecialchars($user['username']) ?>?')">Reset Password</button>
                </td>
            </tr>
        </form>
    <?php endforeach; ?>
    </tbody>
</table>