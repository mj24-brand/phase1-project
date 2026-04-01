<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

// Validate and sanitize inputs
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // ensure page >= 1

$limit = 50;
$offset = ($page - 1) * $limit;

// Build WHERE clause safely
$where = "";
$params = [];
$types = "";

if ($search !== '') {
    $where = "WHERE (l.action LIKE ? OR l.details LIKE ? OR u.username LIKE ? OR l.ip_address LIKE ?)";
    $search_term = "%$search%";
    $params = [$search_term, $search_term, $search_term, $search_term];
    $types = "ssss";
}

// Prepare count query
$count_sql = "SELECT COUNT(*) FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id $where";
$count_stmt = mysqli_prepare($conn, $count_sql);
if ($count_stmt) {
    if ($where) {
        mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    }
    mysqli_stmt_execute($count_stmt);
    mysqli_stmt_bind_result($count_stmt, $total_rows);
    mysqli_stmt_fetch($count_stmt);
    mysqli_stmt_close($count_stmt);
} else {
    $total_rows = 0;
}
$total_pages = ceil($total_rows / $limit);

// Fetch logs with pagination
$logs = [];
$sql = "SELECT l.*, u.username 
        FROM activity_logs l 
        LEFT JOIN users u ON l.user_id = u.id 
        $where 
        ORDER BY l.created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    // Bind parameters: search terms + limit + offset
    $bind_types = $types . "ii";
    $bind_params = array_merge($params, [$limit, $offset]);
    if ($where) {
        mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_params);
    } else {
        mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs</title>
    <link rel="stylesheet" href="../../assets/styles/admin.css">
    <style>
        .pagination { margin-top: 20px; }
        .pagination a {
            display: inline-block;
            padding: 5px 10px;
            margin: 0 2px;
            border: 1px solid #ddd;
            text-decoration: none;
        }
        .pagination a.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .search-box { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar – copy from your admin index.php -->
    <div class="sidebar">...</div>
    <div class="content">
        <div class="top-bar">...</div>
        <div class="dashboard-content">
            <h2>Activity Logs</h2>

            <form method="GET" action="activity_logs.php" class="search-box">
                <input type="text" name="search" placeholder="Search by action, details, username, IP"
                       value="<?= htmlspecialchars($search) ?>" style="width:300px;">
                <button type="submit">Search</button>
                <?php if ($search): ?>
                    <a href="activity_logs.php">Clear</a>
                <?php endif; ?>
            </form>

            <?php if (empty($logs)): ?>
                <p>No logs found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Date & Time</th>
                        </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td><?= htmlspecialchars($log['username'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                                <td><?= htmlspecialchars($log['details']) ?></td>
                                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                <td><?= $log['created_at'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
                           class="<?= ($i == $page) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>