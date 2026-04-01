<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
}

/* Sidebar */
.sidebar {
    position: fixed;          /* 🔥 makes it fixed */
    top: 0;
    left: 0;
    width: 220px;
    height: 100vh;            /* full screen height */
    background: #2c3e50;
    color: white;
    padding: 20px;
    overflow-y: auto;         /* scroll inside sidebar if needed */
}

/* Sidebar links */
.sidebar a {
    display: block;
    color: white;
    padding: 10px;
    text-decoration: none;
    border-radius: 5px;
}

.sidebar a:hover {
    background: #34495e;
}

/* Content area */
.content {
    margin-left: 220px;       /* same as sidebar width */
    padding: 20px;
    min-height: 100vh;        /* avoid white gap */
    background: #f5f7fa;
}
</style>

<div class="sidebar">
    <h3 class="text-center">Admin Panel</h3>

    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="customers.php">👤 Customers</a>
    <a href="delivery.php">🚚 Delivery</a>
    <a href="reports.php">📊 Reports</a>
    <a href="reservations.php">📅 Reservations</a>
    <a href="notifications.php">🔔 Notifications</a>
    <a href="invoice_list.php">🧾 Invoices</a>
    
</div>