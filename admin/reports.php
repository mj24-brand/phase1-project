<?php include("../includes/header.php"); ?>
<?php include("../config/db.php"); ?>
<?php include("../includes/notify.php"); ?>

<h2 class="fw-bold mb-4">Reports & Analytics</h2>

<!-- ================= LOW STOCK ALERT ================= -->
<?php
$resLow = $conn->query("SELECT * FROM inventory WHERE stock < 10");

while($row = $resLow->fetch_assoc()){
    $msg = "Low stock alert: ".$row['item_name'];

    $check = $conn->query("SELECT * FROM notifications WHERE message='$msg'");
    
    if($check->num_rows == 0){
        sendNotification($conn, $msg);
    }
}
?>

<!-- ================= FILTER ================= -->
<form method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-4">
            <label>From Date</label>
            <input type="date" name="from" class="form-control">
        </div>

        <div class="col-md-4">
            <label>To Date</label>
            <input type="date" name="to" class="form-control">
        </div>

        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-dark w-100">Generate Report</button>
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
?>

<!-- EXPORT BUTTON -->
<a href="export_excel.php" class="btn btn-success mb-3">
    Export to Excel
</a>

<?php
// ===== CHART DATA =====
$dates = [];
$sales = [];

$resChart = $conn->query("
    SELECT DATE(created_at) as d, SUM(total_amount) as total 
    FROM orders 
    $where
    GROUP BY d
");

while($r = $resChart->fetch_assoc()){
    $dates[] = $r['d'];
    $sales[] = $r['total'];
}
?>

<!-- ================= SALES CHART ================= -->
<div class="card shadow mb-4">
<div class="card-body">
    <h5>Sales Chart</h5>
    <canvas id="salesChart"></canvas>
</div>
</div>

<!-- ================= DAILY SALES ================= -->
<div class="card shadow mb-4">
<div class="card-body">
<h5>Daily Sales</h5>

<table class="table table-bordered">
<tr>
    <th>Date</th>
    <th>Total Orders</th>
    <th>Revenue</th>
</tr>

<?php
$res = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue 
    FROM orders 
    $where
    GROUP BY date
");

while($row = $res->fetch_assoc()){
?>
<tr>
    <td><?= $row['date'] ?></td>
    <td><?= $row['orders'] ?></td>
    <td>₹<?= $row['revenue'] ?></td>
</tr>
<?php } ?>
</table>
</div>
</div>

<!-- ================= MONTHLY SALES ================= -->
<div class="card shadow mb-4">
<div class="card-body">
<h5>Monthly Sales</h5>

<table class="table table-bordered">
<tr>
    <th>Month</th>
    <th>Total Orders</th>
    <th>Revenue</th>
</tr>

<?php
$res = $conn->query("
    SELECT DATE_FORMAT(created_at,'%Y-%m') as month, 
           COUNT(*) as orders, 
           SUM(total_amount) as revenue 
    FROM orders 
    GROUP BY month
");

while($row = $res->fetch_assoc()){
?>
<tr>
    <td><?= $row['month'] ?></td>
    <td><?= $row['orders'] ?></td>
    <td>₹<?= $row['revenue'] ?></td>
</tr>
<?php } ?>
</table>
</div>
</div>

<!-- ================= ITEM SALES ================= -->
<div class="card shadow mb-4">
<div class="card-body">
<h5>Item Sales</h5>

<table class="table table-bordered">
<tr>
    <th>Item</th>
    <th>Quantity Sold</th>
    <th>Total Revenue</th>
</tr>

<?php
$res = $conn->query("
    SELECT item_name, 
           SUM(quantity) as qty, 
           SUM(price * quantity) as revenue 
    FROM order_items 
    GROUP BY item_name
");

while($row = $res->fetch_assoc()){
?>
<tr>
    <td><?= $row['item_name'] ?></td>
    <td><?= $row['qty'] ?></td>
    <td>₹<?= $row['revenue'] ?></td>
</tr>
<?php } ?>
</table>
</div>
</div>

<!-- ================= PROFIT REPORT ================= -->
<div class="card shadow mb-4">
<div class="card-body">
<h5>Profit Report</h5>

<?php
$revenue = $conn->query("SELECT SUM(total_amount) as r FROM orders")->fetch_assoc()['r'];
$cost = $revenue * 0.6; // assumed cost
$profit = $revenue - $cost;
?>

<p><strong>Total Revenue:</strong> ₹<?= $revenue ?></p>
<p><strong>Estimated Cost:</strong> ₹<?= $cost ?></p>
<p><strong>Profit:</strong> ₹<?= $profit ?></p>

</div>
</div>

<!-- ================= INVENTORY REPORT ================= -->
<div class="card shadow mb-4">
<div class="card-body">
<h5>Inventory Report</h5>

<table class="table table-bordered">
<tr>
    <th>Item</th>
    <th>Stock</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM inventory");

while($row = $res->fetch_assoc()){
?>
<tr>
    <td><?= $row['item_name'] ?></td>
    <td><?= $row['stock'] ?></td>
</tr>
<?php } ?>
</table>

</div>
</div>

<!-- ================= CHART SCRIPT ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById("salesChart"), {
    type: 'bar',
    data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
            label: 'Sales ₹',
            data: <?= json_encode($sales) ?>,
            borderWidth: 1
        }]
    }
});
</script>

<?php include("../includes/footer.php"); ?>