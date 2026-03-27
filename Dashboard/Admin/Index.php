<?php
session_start();
if (!isset($_SESSION['admin']) && !(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");
$full_name = $_SESSION['full_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 1.5em;
            text-align: center;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar li {
            margin: 10px 0;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: #34495e;
        }
        .sidebar a.active {
            background: #1abc9c;
        }
        /* Main Content */
        .content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
        }
        .top-bar {
            background: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-bar .welcome {
            font-size: 1.2em;
        }
        .top-bar .logout a {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
        }
        .top-bar .logout a:hover {
            background: #c0392b;
        }
        .dashboard-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            .content {
                margin-left: 0;
            }
            .container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Restaurant Admin</h2>
        <ul>
            <li><a href="?page=dashboard" class="<?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="?page=users" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'users') ? 'active' : ''; ?>">Manage Users</a></li>
            <li><a href="?page=tables" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'tables') ? 'active' : ''; ?>">Manage Tables</a></li>
            <li><a href="?page=menu" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'menu') ? 'active' : ''; ?>">Manage Menu</a></li>
            <!-- Add more links as needed -->
        </ul>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="top-bar">
            <div class="welcome">Welcome, <?php echo htmlspecialchars($full_name); ?></div>
            <div class="logout"><a href="../../logout.php">Logout</a></div>
        </div>
        <div class="dashboard-content">
            <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
            switch ($page) {
                case 'users':
                    include 'users.php';
                    break;
                case 'tables':
                    include 'tables.php';
                    break;
                case 'menu':
                    include 'menu.php';
                    break;
                default:
                    echo '<h2>Dashboard</h2>';
                    echo '<p>Welcome to the Admin Panel. Use the sidebar to manage users, tables, and menu items.</p>';
                    // You can add statistics here
                    break;
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>