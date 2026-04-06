<?php  include("auth.php");?>
<?php include("../includes/header.php"); ?>
<?php include("../config/db.php"); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Delivery Management</h2>

    <!-- Assign Button -->
    <a href="assign_delivery.php" class="btn btn-primary">
        <i class="fa fa-plus"></i> Assign Delivery
    </a>
</div>

<div class="card shadow border-0">
<div class="card-body">

<div class="table-responsive">
<table class="table table-hover align-middle">

<thead class="table-dark">
<tr>
    <th>#</th>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Delivery Person</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php
// 🔥 FIXED QUERY (IMPORTANT)
$res = $conn->query("
    SELECT deliveries.*, customers.name AS customer_name
    FROM deliveries
    LEFT JOIN orders ON deliveries.order_id = orders.id
    LEFT JOIN customers ON orders.customer_id = customers.id
    ORDER BY deliveries.id DESC
");

if($res->num_rows > 0){
    while($row = $res->fetch_assoc()){
?>

<tr>
    <td><?= $row['id'] ?></td>

    <td>
        <span class="badge bg-secondary">
            #<?= $row['order_id'] ?>
        </span>
    </td>

    <td>
        <?= $row['customer_name'] ? $row['customer_name'] : "Unknown" ?>
    </td>

    <td>
        <i class="fa fa-user"></i> <?= $row['delivery_person'] ?>
    </td>

    <td>
        <?php
        if($row['status'] == "Pending"){
            echo '<span class="badge bg-warning">Pending</span>';
        } elseif($row['status'] == "On the way"){
            echo '<span class="badge bg-info">On the way</span>';
        } elseif($row['status'] == "Delivered"){
            echo '<span class="badge bg-success">Delivered</span>';
        } else {
            echo '<span class="badge bg-secondary">Unknown</span>';
        }
        ?>
    </td>

    <td>
        <a href="update_delivery.php?id=<?= $row['id'] ?>" 
           class="btn btn-sm btn-primary">
           <i class="fa fa-edit"></i> Update
        </a>
    </td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='6' class='text-center text-muted'>
            No deliveries assigned yet
          </td></tr>";
}
?>

</tbody>

</table>
</div>

</div>
</div>

<?php include("../includes/footer.php"); ?>