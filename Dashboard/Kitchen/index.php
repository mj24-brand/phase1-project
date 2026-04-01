<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kitchen') {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");
$full_name = $_SESSION['full_name'] ?? 'Kitchen Staff';

$alertMessage = '';

// Handle status update using prepared statement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_item'])) {
    $item_id = (int) $_POST['item_id'];
    $new_status = $_POST['status']; // will be bound as string

    $update_query = "UPDATE order_items SET kitchen_status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $new_status, $item_id);
        if (mysqli_stmt_execute($stmt)) {
            $alertMessage = "Item status updated.";
        } else {
            $alertMessage = "Error updating status: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $alertMessage = "Database error: " . mysqli_error($conn);
    }
}

// Fetch orders with status not paid or cancelled
$orders = [];

// Fixed: use correct table alias "tables" and escape output later
$order_query = "SELECT orders.*, tables.table_number, users.full_name as waiter_name 
                FROM orders 
                LEFT JOIN tables ON orders.table_id = tables.id
                JOIN users ON orders.waiter_id = users.id
                WHERE orders.status NOT IN ('paid','cancelled')
                ORDER BY orders.order_time ASC";

$result_orders = mysqli_query($conn, $order_query);
if ($result_orders) {
    while ($order = mysqli_fetch_assoc($result_orders)) {
        $order_id = $order['id'];

        // Use prepared statement for items to prevent SQL injection
        // Include notes column
        $items = [];
        $item_query = "SELECT order_items.*, menu_items.name 
                       FROM order_items 
                       JOIN menu_items ON order_items.menu_item_id = menu_items.id 
                       WHERE order_items.order_id = ?";
        $stmt = mysqli_prepare($conn, $item_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            mysqli_stmt_execute($stmt);
            $result_items = mysqli_stmt_get_result($stmt);
            while ($item = mysqli_fetch_assoc($result_items)) {
                $items[] = $item;
            }
            mysqli_stmt_close($stmt);
        }
        $order['items'] = $items;
        $orders[] = $order;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System</title>
    <link rel="stylesheet" href="../../assets/styles/kitchen.css">
    <!-- Add Font Awesome if your CSS doesn't already include it -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Additional styles for notes column and time display */
        .item-notes {
            max-width: 200px;
            font-size: 0.9em;
            color: #555;
            font-style: italic;
        }
        .order-age {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-pending { background: #ffc107; color: #856404; }
        .status-cooking { background: #17a2b8; color: white; }
        .status-ready { background: #28a745; color: white; }
        .status-served { background: #6c757d; color: white; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-utensils"></i> Kitchen Display System</h1>
        <div class="user-info">
            <i class="fas fa-user-cog"></i> <?php echo htmlspecialchars($full_name); ?>
            <a href="../../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <?php if (!empty($alertMessage)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($alertMessage); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No active orders. Kitchen is clear!
        </div>
    <?php else: ?>
        <div class="orders-grid">
            <?php foreach ($orders as $order): ?>
                <?php
                // Calculate order age in minutes
                $order_time = strtotime($order['order_time']);
                $now = time();
                $diff_minutes = floor(($now - $order_time) / 60);
                $age_display = $diff_minutes . ' min ago';
                if ($diff_minutes < 1) $age_display = 'Just now';
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <h3><i class="fas fa-receipt"></i> Order #<?php echo htmlspecialchars($order['id']); ?></h3>
                        <div class="order-meta">
                            <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($order['order_time'])); ?></span>
                            <span class="order-age"><i class="fas fa-hourglass-half"></i> <?php echo $age_display; ?></span>
                            <span><i class="fas fa-user"></i> Waiter: <?php echo htmlspecialchars($order['waiter_name']); ?></span>
                        </div>
                    </div>
                    <div class="order-body">
                        <div class="order-info">
                            <strong><i class="fas fa-tag"></i> Type:</strong> <?php echo ucfirst(htmlspecialchars($order['order_type'])); ?>
                            <?php if ($order['order_type'] == 'dine_in' && !empty($order['table_number'])): ?>
                                | <strong><i class="fas fa-chair"></i> Table:</strong> <?php echo htmlspecialchars($order['table_number']); ?>
                            <?php elseif (!empty($order['customer_name'])): ?>
                                | <strong><i class="fas fa-user"></i> Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($order['notes'])): ?>
                            <div class="order-notes">
                                <i class="fas fa-comment-dots"></i> <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                            </div>
                        <?php endif; ?>

                        <table class="item-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </thead>
                            <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo (int) $item['quantity']; ?></td>
                                    <td class="item-notes"><?php echo nl2br(htmlspecialchars($item['notes'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo htmlspecialchars($item['kitchen_status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($item['kitchen_status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="item_id" value="<?php echo (int) $item['id']; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="pending" <?php if($item['kitchen_status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                                <option value="cooking" <?php if($item['kitchen_status'] == 'cooking') echo 'selected'; ?>>Cooking</option>
                                                <option value="ready" <?php if($item['kitchen_status'] == 'ready') echo 'selected'; ?>>Ready</option>
                                                <option value="served" <?php if($item['kitchen_status'] == 'served') echo 'selected'; ?>>Served</option>
                                            </select>
                                            <input type="hidden" name="update_item" value="1">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>