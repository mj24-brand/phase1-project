<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'waiter') {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

$waiter_id = $_SESSION['user_id'];

$orders = array();
$res = mysqli_query($conn, "SELECT o.*, t.table_number FROM orders o LEFT JOIN tables t ON o.table_id = t.id WHERE o.waiter_id = $waiter_id AND o.status NOT IN ('paid','cancelled') ORDER BY o.order_time DESC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Orders - Waiter</title>
    <link rel="stylesheet" href="../../assets/styles/waiter.css">
</head>
<body>
<div class="container">

    <div class="top-bar">
        <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <div class="logout"><a href="../../logout.php">Logout</a></div>
    </div>

    <div class="nav">
        <a href="take_order.php">Take New Order</a>
        <a href="active_order.php" class="active">Active Orders</a>
        <a href="view_menu.php">View Menu</a>
    </div>

    <div class="content">

        <h2>Active Orders</h2>

        <?php if (empty($orders)) { ?>
            <p>No active orders.</p>
        <?php } else { ?>
            <table border="1" cellpadding="8">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Type</th>
                        <th>Table / Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order) { ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo $order['order_type']; ?></td>
                        <td>
                            <?php if ($order['order_type'] == 'dine_in') { ?>
                                Table <?php echo $order['table_number']; ?>
                            <?php } else { ?>
                                <?php echo htmlspecialchars($order['customer_name']); ?>
                            <?php } ?>
                        </td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo $order['status']; ?></td>
                        <td>
                            <a href="order_details.php?order_id=<?php echo $order['id']; ?>">View / Modify</a>

                            <?php if ($order['order_type'] == 'dine_in' && $order['status'] != 'served') { ?>
                                &nbsp;
                                <form method="POST" action="order_details.php" style="display:inline;">
                                    <input type="hidden" name="action"   value="serve">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" onclick="return confirm('Mark this order as served?');">Mark Served</button>
                                </form>
                            <?php } ?>

                            &nbsp;
                            <form method="POST" action="order_details.php" style="display:inline;">
                                <input type="hidden" name="action"   value="cancel">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" onclick="return confirm('Cancel this order?');">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>

    </div><!-- /.content -->
</div><!-- /.container -->
</body>
</html>