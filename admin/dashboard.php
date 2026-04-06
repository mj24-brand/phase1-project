<?php  include("auth.php");?>
<?php include("../includes/header.php"); ?>
<?php include("../config/db.php"); ?>

<h2 class="fw-bold mb-4">Dashboard</h2>

<!-- ================= DATE FILTER ================= -->
<form method="GET" class="mb-4">
<div class="row">

<div class="col-md-3">
    <label>From</label>
    <input type="date" name="from" class="form-control">
</div>

<div class="col-md-3">
    <label>To</label>
    <input type="date" name="to" class="form-control">
</div>

<div class="col-md-2 d-flex align-items-end">
    <button class="btn btn-dark w-100">Filter</button>
</div>

</div>
</form>

<?php
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

$where = "";

if($from && $to){
    $where = "WHERE DATE(created_at) BETWEEN '$from' AND '$to'";
}

// TODAY SALES
$sales = $conn->query("
    SELECT SUM(total_amount) as total 
    FROM orders 
    $where
")->fetch_assoc()['total'] ?? 0;

// TOTAL ORDERS
$total_orders = $conn->query("
    SELECT COUNT(*) as c FROM orders $where
")->fetch_assoc()['c'];

// PENDING
$pending = $conn->query("
    SELECT COUNT(*) as c FROM orders 
    WHERE status!='Delivered'
")->fetch_assoc()['c'];

// LOW STOCK
$low_stock = $conn->query("
    SELECT COUNT(*) as c FROM inventory WHERE stock < 10
")->fetch_assoc()['c'];
?>

<!-- ================= CARDS ================= -->
<div class="row">

<div class="col-md-3">
<div class="card bg-primary text-white mb-3">
<div class="card-body">
<h5>Sales</h5>
<h3>₹<?= $sales ?></h3>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card bg-success text-white mb-3">
<div class="card-body">
<h5>Total Orders</h5>
<h3><?= $total_orders ?></h3>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card bg-warning text-white mb-3">
<div class="card-body">
<h5>Pending</h5>
<h3><?= $pending ?></h3>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card bg-danger text-white mb-3">
<div class="card-body">
<h5>Low Stock</h5>
<h3><?= $low_stock ?></h3>
</div>
</div>
</div>

</div>

<!-- ================= TOP CUSTOMERS ================= -->
<div class="card shadow mb-4">
<div class="card-body">

<h5>Top Customers</h5>

<table class="table table-bordered">
<tr>
<th>Name</th>
<th>Orders</th>
<th>Spent</th>
</tr>

<?php
$res = $conn->query("
    SELECT customers.name, COUNT(orders.id) as orders, SUM(orders.total_amount) as spent
    FROM orders
    JOIN customers ON orders.customer_id = customers.id
    $where
    GROUP BY customers.id
    ORDER BY spent DESC
    LIMIT 5
");

while($row = $res->fetch_assoc()){
?>
<tr>
<td><?= $row['name'] ?></td>
<td><?= $row['orders'] ?></td>
<td>₹<?= $row['spent'] ?></td>
</tr>
<?php } ?>

</table>

</div>
</div>

<!-- ================= SALES TREND ================= -->
<?php
$dates = [];
$sales_data = [];

$res = $conn->query("
    SELECT DATE(created_at) as d, SUM(total_amount) as total 
    FROM orders 
    $where
    GROUP BY d
");

while($row = $res->fetch_assoc()){
    $dates[] = $row['d'];
    $sales_data[] = $row['total'];
}
?>

<div class="card shadow mb-4">
<div class="card-body">
<h5>Sales Trend</h5>
<canvas id="salesChart"></canvas>
</div>
</div>

<!-- ================= ORDER TREND ================= -->
<?php
$order_dates = [];
$order_counts = [];

$res = $conn->query("
    SELECT DATE(created_at) as d, COUNT(*) as total 
    FROM orders 
    $where
    GROUP BY d
");

while($row = $res->fetch_assoc()){
    $order_dates[] = $row['d'];
    $order_counts[] = $row['total'];
}
?>

<div class="card shadow mb-4">
<div class="card-body">
<h5>Order Trend</h5>
<canvas id="orderChart"></canvas>
</div>
</div>

<!-- ================= CHART JS ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// SALES
new Chart(document.getElementById("salesChart"), {
    type: 'line',
    data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
            label: 'Sales ₹',
            data: <?= json_encode($sales_data) ?>,
            borderWidth: 2
        }]
    }
});

// ORDERS
new Chart(document.getElementById("orderChart"), {
    type: 'bar',
    data: {
        labels: <?= json_encode($order_dates) ?>,
        datasets: [{
            label: 'Orders',
            data: <?= json_encode($order_counts) ?>,
            borderWidth: 1
        }]
    }
});
</script>

<!-- ================= AUTO REFRESH ================= -->
<script>
// Refresh every 30 seconds
setTimeout(() => {
    location.reload();
}, 30000);
</script>

<?php include("../includes/footer.php"); ?>