<?php
session_start();
require_once 'config/db.php';   // your database connection

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];

// Fetch total number of tables (example)
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tables");
$row = mysqli_fetch_assoc($result);
$totalTables = $row['total'];

// Optionally, fetch other stats (e.g., active orders)
$activeOrders = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM orders WHERE status NOT IN ('paid','cancelled')");
if ($res) {
    $activeOrders = mysqli_fetch_assoc($res)['cnt'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($full_name); ?> (<?php echo ucfirst($role); ?>)</h1>
    <p>Total Tables: <?php echo $totalTables; ?></p>
    <p>Active Orders: <?php echo $activeOrders; ?></p>

    <hr>
    <h3>Quick Actions</h3>
    <ul>
        <?php if ($role == 'admin'): ?>
            <li><a href="admin/users.php">Manage Users</a></li>
            <li><a href="admin/tables.php">Manage Tables</a></li>
            <li><a href="admin/menu.php">Manage Menu</a></li>
            <li><a href="admin/logs.php">View Logs</a></li>
        <?php elseif ($role == 'manager'): ?>
            <li><a href="manager/reports.php">Sales Reports</a></li>
            <li><a href="manager/staff.php">Staff Schedule</a></li>
        <?php elseif ($role == 'cashier'): ?>
            <li><a href="cashier/index.php">Process Payments</a></li>
        <?php elseif ($role == 'waiter'): ?>
            <li><a href="waiter/index.php">Take Orders</a></li>
            <li><a href="waiter/active_orders.php">Active Orders</a></li>
        <?php elseif ($role == 'kitchen'): ?>
            <li><a href="kitchen/index.php">Kitchen Display</a></li>
        <?php endif; ?>
    </ul>

    <a href="../logout.php">Logout</a>
</body>
</html>