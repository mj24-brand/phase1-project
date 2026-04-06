<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'staff'){
    header("Location: ../login.php");
    exit;
}

include("header.php");
include("../config/db.php");
include_once("../includes/notify.php");

$id = $_GET['id'];

$d = $conn->query("SELECT * FROM deliveries WHERE id=$id")->fetch_assoc();
?>

<h2>Update Delivery</h2>

<form method="POST">

<select name="status" class="form-control mb-3">
    <option <?= $d['status']=="Pending"?"selected":"" ?>>Pending</option>
    <option <?= $d['status']=="On the way"?"selected":"" ?>>On the way</option>
    <option <?= $d['status']=="Delivered"?"selected":"" ?>>Delivered</option>
</select>

<button name="update" class="btn btn-success">Update</button>

</form>

<?php
if(isset($_POST['update'])){
    $status = $_POST['status'];

    $conn->query("
        UPDATE deliveries 
        SET status='$status', delivered_at=NOW() 
        WHERE id=$id
    ");

    if($status == "Delivered"){
        sendNotification($conn, "Order #".$d['order_id']." delivered by ".$_SESSION['staff_name'], 'system');
    }

    echo "<div class='alert alert-success mt-3'>Updated Successfully!</div>";
}
?>

<?php include("footer.php"); ?>