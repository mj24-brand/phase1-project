<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'staff'){
    header("Location: ../login.php");
    exit;
}

include("header.php");
include("../config/db.php");

$staff_id = $_SESSION['staff_id'];
?>

<h2>Notifications</h2>

<?php
$res = $conn->query("
    SELECT * FROM notifications 
    WHERE receiver_type='staff' AND receiver_id=$staff_id
    ORDER BY id DESC
");

while($row = $res->fetch_assoc()){
?>

<div class="alert alert-info">
    <?= $row['message'] ?><br>
    <small><?= $row['created_at'] ?></small>
</div>

<?php } ?>

<?php include("footer.php"); ?>