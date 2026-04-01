<?php
// reports.php
// Today's sales
$today = date('Y-m-d');
$today_sales = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(total_amount) FROM orders WHERE DATE(order_time) = '$today' AND status = 'paid'"))[0];

// This week's sales
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_sales = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(total_amount) FROM orders WHERE order_time >= '$week_start' AND status = 'paid'"))[0];

// Top 5 items
$top_items = [];
$top_query = "SELECT mi.name, SUM(oi.quantity) as total_qty 
              FROM order_items oi
              JOIN menu_items mi ON oi.menu_item_id = mi.id
              JOIN orders o ON oi.order_id = o.id
              WHERE o.status = 'paid'
              GROUP BY oi.menu_item_id
              ORDER BY total_qty DESC
              LIMIT 5";
$result = mysqli_query($conn, $top_query);
while ($row = mysqli_fetch_assoc($result)) {
    $top_items[] = $row;
}
?>
<div class="subpage-header">
    <h2>Sales Reports</h2>
    <p>Overview of restaurant performance.</p>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card-blue">
        <span class="stat-icon">📅</span>
        <div class="stat-value">$<?= number_format($today_sales, 2) ?></div>
        <div class="stat-label">Today's Sales</div>
    </div>
    <div class="stat-card stat-card-green">
        <span class="stat-icon">📆</span>
        <div class="stat-value">$<?= number_format($week_sales, 2) ?></div>
        <div class="stat-label">This Week's Sales</div>
    </div>
</div>

<h3>Top 5 Selling Items</h3>
<table border="1" cellpadding="8">
    <thead><tr><th>Item</th><th>Quantity Sold</th></tr></thead>
    <tbody>
    <?php foreach ($top_items as $item): ?>
        <tr><td><?= htmlspecialchars($item['name']) ?></td><td><?= $item['total_qty'] ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>