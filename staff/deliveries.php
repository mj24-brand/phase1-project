<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'staff'){
    header("Location: ../login.php");
    exit;
}

include("header.php");
include("../config/db.php");

$staff_name = $_SESSION['staff_name'];
?>

<h2>My Deliveries</h2>

<table class="table table-bordered">
<tr>
    <th>Order ID</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php
$res = $conn->query("
    SELECT * FROM deliveries 
    WHERE delivery_person='$staff_name'
");

while($row = $res->fetch_assoc()){
?>

<tr>
<td><?= $row['order_id'] ?></td>
<td><?= $row['status'] ?></td>
<td>
<a href="update_delivery.php?id=<?= $row['id'] ?>" 
   class="btn btn-sm btn-primary">
   Update
</a>
</td>
</tr>

<?php } ?>

</table>

<?php include("footer.php"); ?>