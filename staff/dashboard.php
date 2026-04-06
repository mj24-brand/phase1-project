<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'staff'){
    header("Location: ../login.php");
    exit;
}

include("header.php");
include("../config/db.php");

$staff_name = $_SESSION['staff_name'];

// STATS
$total = $conn->query("
    SELECT COUNT(*) as c FROM deliveries 
    WHERE delivery_person='$staff_name'
")->fetch_assoc()['c'];

$pending = $conn->query("
    SELECT COUNT(*) as c FROM deliveries 
    WHERE delivery_person='$staff_name' AND status!='Delivered'
")->fetch_assoc()['c'];

$done = $conn->query("
    SELECT COUNT(*) as c FROM deliveries 
    WHERE delivery_person='$staff_name' AND status='Delivered'
")->fetch_assoc()['c'];
?>

<h2 class="mb-4">Dashboard</h2>

<div class="row">

<div class="col-md-4">
<div class="card bg-primary text-white p-3">
<h5>Total Deliveries</h5>
<h3><?= $total ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card bg-warning text-white p-3">
<h5>Pending</h5>
<h3><?= $pending ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card bg-success text-white p-3">
<h5>Completed</h5>
<h3><?= $done ?></h3>
</div>
</div>

</div>

<?php include("footer.php"); ?>