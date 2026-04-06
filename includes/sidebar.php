<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
}

/* Sidebar */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 220px;
    height: 100vh;
    background: linear-gradient(180deg, #f1be07, #1c1a1a);
    color: white;
    padding: 20px 15px;
    overflow-y: auto;
    box-shadow: 3px 0 15px rgba(0,0,0,0.2);
}

/* Title */
.sidebar h3 {
    font-size: 25px;
    margin-bottom: 60px;
    letter-spacing: 1px;
}

/* Sidebar links */
.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #ecf0f1;
    padding: 10px 12px;
    margin-top:15px;
    margin-bottom: 8px;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 15px;
}

/* Hover effect */
.sidebar a:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
    color: #fff;
}

/* Active effect (optional future use) */
.sidebar a.active {
    background: #3498db;
}

/* Icons */
.sidebar i {
    font-size: 16px;
}

/* Content area */
.content {
    margin-left: 220px;
    padding: 20px;
    min-height: 100vh;
    background: #f5f7fa;
}
</style>

<div class="sidebar">
    <h3 class="text-center">
       Admin Panel
    </h3>

    <a href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
    <a href="customers.php"><i class="bi bi-people"></i> Customers</a>
    <a href="delivery.php"><i class="bi bi-truck"></i> Delivery</a>
    <a href="reports.php"><i class="bi bi-bar-chart"></i> Reports</a>
    <a href="reservations.php"><i class="bi bi-calendar-check"></i> Reservations</a>
    <a href="notifications.php"><i class="bi bi-bell"></i> Notifications</a>
    <a href="invoice_list.php"><i class="bi bi-receipt"></i> Invoices</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>