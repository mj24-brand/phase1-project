<?php
session_start();
if (!(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");
$full_name = $_SESSION['full_name'] ?? 'Admin';

$total_users = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0];
$total_tables = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM tables"))[0];
$total_menu = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM menu_items"))[0];
$total_orders = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders"))[0];
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$page_titles = [
    'dashboard'     => 'Dashboard',
    'users'         => 'User Management',
    'tables'        => 'Table Management',
    'menu'          => 'Menu Management',
    'activity_logs' => 'Activity Logs',
    'floor_layout'  => 'Floor Layout',
];
$title = $page_titles[$page] ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="../../assets/styles/admin.css">
</head>
<body>
<div class="container">

    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>🍽 RestoAdmin</h2>
        </div>

        <p class="sidebar-label">Navigation</p>
        <ul>
            <li>
                <a href="?page=dashboard"
                   class="<?= (!isset($_GET['page']) || $page === 'dashboard') ? 'active' : '' ?>">
                    📊 Dashboard
                </a>
            </li>
            <li>
                <a href="?page=users"
                   class="<?= $page === 'users' ? 'active' : '' ?>">
                    👥 User Management
                </a>
            </li>
            <li>
                <a href="?page=tables"
                   class="<?= $page === 'tables' ? 'active' : '' ?>">
                    🪑 Table Management
                </a>
            </li>
            <li>
                <a href="?page=menu"
                   class="<?= $page === 'menu' ? 'active' : '' ?>">
                    🗒 Menu Management
                </a>
            </li>
            <!-- New links -->
            <li>
                <a href="?page=activity_logs"
                   class="<?= $page === 'activity_logs' ? 'active' : '' ?>">
                    📋 Activity Logs
                </a>
            </li>
            <li>
                <a href="?page=floor_layout"
                   class="<?= $page === 'floor_layout' ? 'active' : '' ?>">
                    🗺️ Floor Layout
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="../../logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="content">

        <!-- Top Bar -->
        <div class="top-bar">
            <div class="topbar-title"><?= htmlspecialchars($title) ?></div>
            <div class="topbar-right">
                <div class="admin-chip">
                    <div class="admin-avatar"><?= strtoupper(substr($full_name, 0, 1)) ?></div>
                    <span class="admin-name"><?= htmlspecialchars($full_name) ?></span>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="dashboard-content">
            <?php switch ($page):

                case 'dashboard':
                default: ?>

                <p class="dash-heading">
                    Good <?= (date('H') < 12) ? 'Morning' : ((date('H') < 17) ? 'Afternoon' : 'Evening') ?>,
                    <?= htmlspecialchars(explode(' ', $full_name)[0]) ?> 👋
                </p>
                <p class="dash-sub">Here's what's happening at your restaurant today.</p>

                <!-- Stat Cards -->
                <div class="stats-grid">
                    <div class="stat-card stat-card-blue">
                        <span class="stat-icon">👥</span>
                        <div class="stat-value"><?= number_format($total_users) ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-card stat-card-green">
                        <span class="stat-icon">🪑</span>
                        <div class="stat-value"><?= number_format($total_tables) ?></div>
                        <div class="stat-label">Total Tables</div>
                    </div>
                    <div class="stat-card stat-card-orange">
                        <span class="stat-icon">🗒</span>
                        <div class="stat-value"><?= number_format($total_menu) ?></div>
                        <div class="stat-label">Menu Items</div>
                    </div>
                    <div class="stat-card stat-card-red">
                        <span class="stat-icon">🧾</span>
                        <div class="stat-value"><?= number_format($total_orders) ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <p class="section-title">Quick Actions</p>
                <div class="actions-grid">
                    <a href="?page=users" class="action-card">
                        <span class="action-icon">👥</span>
                        <span class="action-label">Manage Users</span>
                        <span class="action-desc">Add, edit or remove staff and customer accounts.</span>
                    </a>
                    <a href="?page=tables" class="action-card">
                        <span class="action-icon">🪑</span>
                        <span class="action-label">Manage Tables</span>
                        <span class="action-desc">Configure table layout, capacity and availability.</span>
                    </a>
                    <a href="?page=menu" class="action-card">
                        <span class="action-icon">🗒</span>
                        <span class="action-label">Manage Menu</span>
                        <span class="action-desc">Update dishes, prices, categories and images.</span>
                    </a>
                    <a href="?page=activity_logs" class="action-card">
                        <span class="action-icon">📋</span>
                        <span class="action-label">Activity Logs</span>
                        <span class="action-desc">View user actions and system events.</span>
                    </a>
                    <a href="?page=floor_layout" class="action-card">
                        <span class="action-icon">🗺️</span>
                        <span class="action-label">Floor Layout</span>
                        <span class="action-desc">Visual table management and status updates.</span>
                    </a>
                </div>

                <?php break;

                case 'users':
                    if (file_exists('users.php')) {
                        include 'users.php';
                    } else { ?>
                        <p class="subpage-header">User Management</p>
                        <p class="subpage-sub">Create, update and deactivate user accounts.</p>
                        <div class="placeholder-box">
                            <span class="ph-icon">👥</span>
                            <p>users.php not found — create it and it will load here automatically.</p>
                        </div>
                    <?php } break;

                case 'tables':
                    if (file_exists('tables.php')) {
                        include 'tables.php';
                    } else { ?>
                        <p class="subpage-header">Table Management</p>
                        <p class="subpage-sub">Manage dining tables, capacity and availability.</p>
                        <div class="placeholder-box">
                            <span class="ph-icon">🪑</span>
                            <p>tables.php not found — create it and it will load here automatically.</p>
                        </div>
                    <?php } break;

                case 'menu':
                    if (file_exists('menu.php')) {
                        include 'menu.php';
                    } else { ?>
                        <p class="subpage-header">Menu Management</p>
                        <p class="subpage-sub">Add or update dishes, prices and categories.</p>
                        <div class="placeholder-box">
                            <span class="ph-icon">🗒</span>
                            <p>menu.php not found — create it and it will load here automatically.</p>
                        </div>
                    <?php } break;

                case 'activity_logs':
                    if (file_exists('activity_logs.php')) {
                        include 'activity_logs.php';
                    } else { ?>
                        <p class="subpage-header">Activity Logs</p>
                        <p class="subpage-sub">View system activity logs.</p>
                        <div class="placeholder-box">
                            <span class="ph-icon">📋</span>
                            <p>activity_logs.php not found — create it and it will load here automatically.</p>
                        </div>
                    <?php } break;

                case 'floor_layout':
                    if (file_exists('floor_layout.php')) {
                        include 'floor_layout.php';
                    } else { ?>
                        <p class="subpage-header">Floor Layout</p>
                        <p class="subpage-sub">Visual representation of tables and their status.</p>
                        <div class="placeholder-box">
                            <span class="ph-icon">🗺️</span>
                            <p>floor_layout.php not found — create it and it will load here automatically.</p>
                        </div>
                    <?php } break;

            endswitch; ?>
        </div>

    </div>

</div>
</body>
</html>