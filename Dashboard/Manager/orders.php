<?php
// orders.php
$alertMessage = '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Update order status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order_status'])) {
    $order_id = (int) $_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['order_status']);
    $update = mysqli_query($conn, "UPDATE orders SET status = '$new_status' WHERE id = $order_id");
    if ($update) {
        $alertMessage = "Order #$order_id status updated to '$new_status'.";
    } else {
        $alertMessage = "Error updating order: " . mysqli_error($conn);
    }
}

// Build query with optional status filter
$where = '';
if ($filter_status && in_array($filter_status, ['pending', 'confirmed', 'preparing', 'ready', 'served', 'paid', 'cancelled'])) {
    $where = "WHERE status = '$filter_status'";
}
$orders_query = "SELECT o.*, u.full_name as waiter_name, t.table_number 
                 FROM orders o 
                 LEFT JOIN users u ON o.waiter_id = u.id
                 LEFT JOIN tables t ON o.table_id = t.id
                 $where
                 ORDER BY o.order_time DESC";
$result = mysqli_query($conn, $orders_query);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
?>
<div class="subpage-header">
    <h2>Order Management</h2>
    <p>View and update the status of all orders.</p>
</div>

<?php if (!empty($alertMessage)): ?>
    <div class="alert alert-info"><?= htmlspecialchars($alertMessage) ?></div>
<?php endif; ?>

<div class="filter-bar">
    <form method="GET" action="?page=orders">
        <label>Filter by status:</label>
        <select name="status" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= $filter_status == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="preparing" <?= $filter_status == 'preparing' ? 'selected' : '' ?>>Preparing</option>
            <option value="ready" <?= $filter_status == 'ready' ? 'selected' : '' ?>>Ready</option>
            <option value="served" <?= $filter_status == 'served' ? 'selected' : '' ?>>Served</option>
            <option value="paid" <?= $filter_status == 'paid' ? 'selected' : '' ?>>Paid</option>
            <option value="cancelled" <?= $filter_status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
    </form>
</div>

<table border="1" cellpadding="8">
    <thead>
        <tr><th>ID</th><th>Type</th><th>Waiter</th><th>Table/Customer</th><th>Total</th><th>Status</th><th>Actions</th> </thead>
    <tbody>
    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= $order['id'] ?></td>
            <td><?= ucfirst($order['order_type']) ?></td>
            <td><?= htmlspecialchars($order['waiter_name']) ?></td>
            <td>
                <?php if ($order['order_type'] == 'dine_in'): ?>
                    Table <?= $order['table_number'] ?>
                <?php else: ?>
                    <?= htmlspecialchars($order['customer_name']) ?>
                <?php endif; ?>
            </td>
            <td>$<?= number_format($order['total_amount'], 2) ?></td>
            <td><?= ucfirst($order['status']) ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <select name="order_status">
                        <option value="pending"   <?= $order['status'] == 'pending'   ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="preparing" <?= $order['status'] == 'preparing' ? 'selected' : '' ?>>Preparing</option>
                        <option value="ready"     <?= $order['status'] == 'ready'     ? 'selected' : '' ?>>Ready</option>
                        <option value="served"    <?= $order['status'] == 'served'    ? 'selected' : '' ?>>Served</option>
                        <option value="paid"      <?= $order['status'] == 'paid'      ? 'selected' : '' ?>>Paid</option>
                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_order_status">Update</button>
                </form>
                <a href="order_details.php?order_id=<?= $order['id'] ?>">View Items</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>