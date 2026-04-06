<?php  include("auth.php");?>
<?php include("../includes/header.php"); ?>
<?php include("../config/db.php"); ?>
<?php include("../includes/notify.php"); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Reservations</h2>
</div>

<div class="card shadow border-0">
<div class="card-body">

<!-- ADD FORM -->
<form method="POST" class="mb-4">
    <div class="row">

        <div class="col-md-3">
            <input type="text" name="name" class="form-control" placeholder="Customer Name" required>
        </div>

        <div class="col-md-2">
            <input type="text" name="phone" class="form-control" placeholder="Phone">
        </div>

        <div class="col-md-2">
            <input type="date" name="date" class="form-control" required>
        </div>

        <div class="col-md-2">
            <input type="time" name="time" class="form-control" required>
        </div>

        <div class="col-md-2">
            <input type="number" name="persons" class="form-control" placeholder="Persons">
        </div>

        <div class="col-md-1">
            <button name="add" class="btn btn-primary w-100">Add</button>
        </div>

    </div>
</form>

<?php
if(isset($_POST['add'])){
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $persons = $_POST['persons'];

    $conn->query("INSERT INTO reservations(customer_name, phone, date, time, persons)
                  VALUES('$name','$phone','$date','$time','$persons')");

    // 🔔 NOTIFICATION
    sendNotification($conn, "New reservation for ".$name." on ".$date." at ".$time);

    echo "<div class='alert alert-success'>Reservation Added!</div>";
}
?>

<!-- LIST -->
<table class="table table-bordered">
<tr>
    <th>#</th>
    <th>Name</th>
    <th>Date</th>
    <th>Time</th>
    <th>Persons</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM reservations ORDER BY id DESC");

while($row = $res->fetch_assoc()){
?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['customer_name'] ?></td>
    <td><?= $row['date'] ?></td>
    <td><?= $row['time'] ?></td>
    <td><?= $row['persons'] ?></td>
</tr>
<?php } ?>

</table>

</div>
</div>

<?php include("../includes/footer.php"); ?>