<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

$full_name = $_SESSION['full_name'] ?? 'Manager';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$page_titles = [
    'dashboard' => 'Manager Dashboard',
    'orders'    => 'Order Management',
    'tables'    => 'Table Management',
    'menu'      => 'Menu Management',
    'users'     => 'Staff Management',
    'reports'   => 'Reports'
];
$title = $page_titles[$page] ?? 'Manager';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="../../assets/styles/admin.css"> <!-- reuse admin styles -->
</head>
<body>
<div class="container">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>🍽 Manager Panel</h2>
        </div>
        <p class="sidebar-label">Navigation</p>
        <ul>
            <li><a href="?page=dashboard" class="<?= $page === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a></li>
            <li><a href="?page=orders"    class="<?= $page === 'orders'    ? 'active' : '' ?>">🧾 Order Management</a></li>
            <li><a href="?page=tables"    class="<?= $page === 'tables'    ? 'active' : '' ?>">🪑 Table Management</a></li>
            <li><a href="?page=menu"      class="<?= $page === 'menu'      ? 'active' : '' ?>">🍽️ Menu Management</a></li>
            <li><a href="?page=users"     class="<?= $page === 'users'     ? 'active' : '' ?>">👥 Staff Management</a></li>
            <li><a href="?page=reports"   class="<?= $page === 'reports'   ? 'active' : '' ?>">📈 Reports</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="../../logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="top-bar">
            <div class="topbar-title"><?= htmlspecialchars($title) ?></div>
            <div class="topbar-right">
                <div class="admin-chip">
                    <div class="admin-avatar"><?= strtoupper(substr($full_name, 0, 1)) ?></div>
                    <span class="admin-name"><?= htmlspecialchars($full_name) ?></span>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <?php
            $page_file = $page . '.php';
            if (file_exists($page_file)) {
                include $page_file;
            } else {
                echo '<p>Page not found.</p>';
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>