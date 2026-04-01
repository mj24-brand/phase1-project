<?php include("../includes/header.php"); ?>
<?php include("../config/db.php"); ?>
<?php include_once("../includes/notify.php"); ?>

<?php
$id = $_GET['id'];

$d = $conn->query("SELECT * FROM deliveries WHERE id=$id")->fetch_assoc();
?>

<h2 class="fw-bold mb-4">Update Delivery</h2>

<div class="card shadow border-0">
<div class="card-body">

<form method="POST">

<label>Status</label>
<select name="status" class="form-control mb-3">
    <option <?= $d['status']=="Pending"?"selected":"" ?>>Pending</option>
    <option <?= $d['status']=="On the way"?"selected":"" ?>>On the way</option>
    <option <?= $d['status']=="Delivered"?"selected":"" ?>>Delivered</option>
</select>

<button class="btn btn-primary" name="update">Update</button>

</form>

<?php
if(isset($_POST['update'])){
    $status = $_POST['status'];

    $conn->query("UPDATE deliveries SET status='$status' WHERE id=$id");

    if($status == "Delivered"){
        sendNotification($conn, "Order #".$d['order_id']." delivered successfully", 'system');
    }

    echo "<div class='alert alert-success mt-3'>Updated Successfully!</div>";
}
?>

</div>
</div>

<?php include("../includes/footer.php"); ?>