<?php include("../includes/header.php"); ?>
<?php include("../config/db.php"); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Assign Delivery</h2>
    <a href="delivery.php" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>
</div>

<div class="card shadow border-0">
<div class="card-body">

<form method="POST">

<div class="row">

<!-- ORDER DROPDOWN (FIXED 🔥) -->
<div class="col-md-6 mb-3">
    <label class="form-label">Select Order</label>
    <select name="order_id" class="form-control" required>

        <option value="">-- Select Order --</option>

        <?php
        // LEFT JOIN to avoid missing customer issue
        $res = $conn->query("
            SELECT orders.id, customers.name 
            FROM orders 
            LEFT JOIN customers ON orders.customer_id = customers.id
            ORDER BY orders.id DESC
        ");

        if($res->num_rows > 0){
            while($row = $res->fetch_assoc()){
                $customer = $row['name'] ? $row['name'] : "Unknown Customer";
                echo "<option value='{$row['id']}'>
                        Order #{$row['id']} - {$customer}
                      </option>";
            }
        } else {
            echo "<option>No Orders Found</option>";
        }
        ?>

    </select>
</div>

<!-- DELIVERY PERSON DROPDOWN -->
<div class="col-md-6 mb-3">
    <label class="form-label">Delivery Person</label>
    <select name="person" class="form-control" required>

        <option value="">-- Select Delivery Person --</option>

        <?php
        $res = $conn->query("SELECT * FROM delivery_persons");

        if($res->num_rows > 0){
            while($row = $res->fetch_assoc()){
                echo "<option>{$row['name']}</option>";
            }
        } else {
            echo "<option>No Delivery Persons Found</option>";
        }
        ?>

    </select>
</div>

</div>

<!-- BUTTON -->
<div class="d-flex justify-content-end">
    <button class="btn btn-success" name="save">
        <i class="fa fa-truck"></i> Assign Delivery
    </button>
</div>

</form>

<?php
// SAVE DELIVERY
if(isset($_POST['save'])){
    $order_id = $_POST['order_id'];
    $person = $_POST['person'];

    // Prevent duplicate assignment
    $check = $conn->query("SELECT * FROM deliveries WHERE order_id='$order_id'");
    
    if($check->num_rows > 0){
        echo "<div class='alert alert-warning mt-3'>
                Delivery already assigned for this order!
              </div>";
    } else {

        $conn->query("INSERT INTO deliveries(order_id, delivery_person, status)
                      VALUES('$order_id', '$person', 'Pending')");

        echo "<div class='alert alert-success mt-3'>
                Delivery Assigned Successfully!
              </div>";
    }
}
?>

</div>
</div>

<?php include("../includes/footer.php"); ?>