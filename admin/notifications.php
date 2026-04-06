<?php  include("auth.php");?>
<?php include("../includes/header.php"); ?>
<?php include("../config/db.php"); ?>
<?php include("../includes/notify.php"); ?>

<h2 class="fw-bold mb-4">Send Notifications</h2>

<div class="card shadow mb-4">
<div class="card-body">

<form method="POST">

<div class="row">

<!-- TYPE -->
<div class="col-md-3">
    <label>Send To</label>
    <select name="type" id="type" class="form-control" required>
        <option value="">Select</option>
        <option value="customer">Customer</option>
        <option value="staff">Staff</option>
    </select>
</div>

<!-- CUSTOMER -->
<div class="col-md-3" id="customerBox" style="display:none;">
    <label>Select Customer</label>
    <select name="customer_id" class="form-control">
        <?php
        $res = $conn->query("SELECT * FROM customers");
        while($row = $res->fetch_assoc()){
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
        ?>
    </select>
</div>

<!-- STAFF -->
<div class="col-md-3" id="staffBox" style="display:none;">
    <label>Select Staff</label>
    <select name="staff_id" class="form-control">
        <option value="all">All Staff</option>
        <?php
        $res = $conn->query("SELECT * FROM staff");
        while($row = $res->fetch_assoc()){
            echo "<option value='{$row['id']}'>{$row['name']} ({$row['role']})</option>";
        }
        ?>
    </select>
</div>

<!-- MESSAGE -->
<div class="col-md-3">
    <label>Message</label>
    <input type="text" name="message" class="form-control" required>
</div>

<!-- BUTTON -->
<div class="col-md-3 d-flex align-items-end">
    <button name="send" class="btn btn-primary w-100">Send</button>
</div>

</div>

</form>

<?php
if(isset($_POST['send'])){

    $type = $_POST['type'];
    $msg = $_POST['message'];

    if($type == "customer"){
        $cid = $_POST['customer_id'];

        sendNotification($conn, $msg, 'manual', 'customer', $cid);

    } elseif($type == "staff"){

        $sid = $_POST['staff_id'];

        if($sid == "all"){
            $staff = $conn->query("SELECT * FROM staff");

            while($s = $staff->fetch_assoc()){
                sendNotification($conn, $msg, 'manual', 'staff', $s['id']);
            }
        } else {
            sendNotification($conn, $msg, 'manual', 'staff', $sid);
        }
    }

    echo "<div class='alert alert-success mt-3'>Notification Sent!</div>";
}
?>

</div>
</div>

<!-- DOWNLOAD BUTTONS -->
<div class="mt-3">

<a href="download_customer_notifications.php" class="btn btn-success">
Download Customer Messages
</a>

<a href="download_staff_notifications.php" class="btn btn-primary">
Download Staff Messages
</a>

</div>

<!-- SCRIPT -->
<script>
document.getElementById("type").addEventListener("change", function(){

    document.getElementById("customerBox").style.display = "none";
    document.getElementById("staffBox").style.display = "none";

    if(this.value === "customer"){
        document.getElementById("customerBox").style.display = "block";
    }

    if(this.value === "staff"){
        document.getElementById("staffBox").style.display = "block";
    }
});
</script>

<?php include("../includes/footer.php"); ?>