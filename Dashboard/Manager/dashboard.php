<?php
// dashboard.php – shows statistics and quick actions
// Data is fetched from the database
$total_orders = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders"))[0];
$total_revenue = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(total_amount) FROM orders WHERE status = 'paid'"))[0];
$active_orders = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders WHERE status NOT IN ('paid','cancelled')"))[0];
$total_tables = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM tables"))[0];
$occupied_tables = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM tables WHERE status = 'occupied'"))[0];
$total_menu_items = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM menu_items"))[0];
?>
<div class="dash-heading">
    <h2>Good <?= (date('H') < 12) ? 'Morning' : ((date('H') < 17) ? 'Afternoon' : 'Evening') ?>, <?= htmlspecialchars(explode(' ', $full_name)[0]) ?> 👋</h2>
    <p>Here's what's happening at the restaurant today.</p>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card-blue">
        <span class="stat-icon">🧾</span>
        <div class="stat-value"><?= number_format($total_orders) ?></div>
        <div class="stat-label">Total Orders</div>
    </div>
    <div class="stat-card stat-card-green">
        <span class="stat-icon">💰</span>
        <div class="stat-value">$<?= number_format($total_revenue, 2) ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card stat-card-orange">
        <span class="stat-icon">⏳</span>
        <div class="stat-value"><?= number_format($active_orders) ?></div>
        <div class="stat-label">Active Orders</div>
    </div>
    <div class="stat-card stat-card-red">
        <span class="stat-icon">🪑</span>
        <div class="stat-value"><?= number_format($occupied_tables) ?>/<?= number_format($total_tables) ?></div>
        <div class="stat-label">Occupied Tables</div>
    </div>
</div>

<div class="section-title">Quick Actions</div>
<div class="actions-grid">
    <a href="?page=orders" class="action-card">
        <span class="action-icon">🧾</span>
        <span class="action-label">View All Orders</span>
        <span class="action-desc">Check current and past orders.</span>
    </a>
    <a href="?page=menu" class="action-card">
        <span class="action-icon">🍽️</span>
        <span class="action-label">Update Menu</span>
        <span class="action-desc">Change prices, descriptions, availability.</span>
    </a>
    <a href="?page=reports" class="action-card">
        <span class="action-icon">📈</span>
        <span class="action-label">Sales Reports</span>
        <span class="action-desc">Analyze daily/weekly performance.</span>
    </a>
</div>s