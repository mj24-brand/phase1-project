<?php
// admin/floor_layout.php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_table'])) {
    $id = (int)$_POST['id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE tables SET status = '$status' WHERE id = $id");
    // Optionally log activity
}

$tables = [];
$result = mysqli_query($conn, "SELECT * FROM tables ORDER BY table_number");
while ($row = mysqli_fetch_assoc($result)) {
    $tables[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Floor Layout</title>
    <link rel="stylesheet" href="../../assets/styles/admin.css">
    <style>
        .floor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .table-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            background: #f9f9f9;
            transition: 0.2s;
        }
        .table-card.available { border-left: 5px solid #28a745; background: #e8f5e9; }
        .table-card.occupied { border-left: 5px solid #dc3545; background: #ffe8e8; }
        .table-card.reserved { border-left: 5px solid #ffc107; background: #fff3cd; }
        .table-card.cleaning { border-left: 5px solid #17a2b8; background: #e2f3f5; }
        .table-number {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .table-status {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .table-capacity {
            font-size: 12px;
            color: #666;
        }
        .status-select {
            margin-top: 10px;
            width: 100%;
            padding: 5px;
        }
        button {
            margin-top: 5px;
            background: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <div class="sidebar">...</div>
    <div class="content">
        <div class="top-bar">...</div>
        <div class="dashboard-content">
            <h2>Floor Layout</h2>
            <div class="floor-grid">
                <?php foreach ($tables as $t): ?>
                    <div class="table-card <?= $t['status'] ?>">
                        <div class="table-number">Table <?= $t['table_number'] ?></div>
                        <div class="table-status">Status: <?= ucfirst($t['status']) ?></div>
                        <div class="table-capacity">Capacity: <?= $t['capacity'] ?> people</div>
                        <div class="table-section">Section: <?= $t['section'] ?></div>
                        <form method="POST" action="floor_layout.php">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <select name="status" class="status-select">
                                <option value="available" <?= $t['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                                <option value="occupied" <?= $t['status'] == 'occupied' ? 'selected' : '' ?>>Occupied</option>
                                <option value="reserved" <?= $t['status'] == 'reserved' ? 'selected' : '' ?>>Reserved</option>
                                <option value="cleaning" <?= $t['status'] == 'cleaning' ? 'selected' : '' ?>>Cleaning</option>
                            </select>
                            <button type="submit" name="update_table">Update</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            <p><a href="tables.php">Back to Table Management (list view)</a></p>
        </div>
    </div>
</div>
</body>
</html>