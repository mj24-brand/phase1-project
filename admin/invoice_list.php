<?php include("../includes/header.php"); ?>
<?php include("../config/db.php"); ?>

<h2 class="fw-bold mb-4">Invoices</h2>

<div class="card shadow border-0">
<div class="card-body">

<table class="table table-hover">
<thead class="table-dark">
<tr>
    <th>#</th>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Amount</th>
    <th>Date</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php
$res = $conn->query("
    SELECT orders.*, customers.name 
    FROM orders 
    LEFT JOIN customers ON orders.customer_id = customers.id
    ORDER BY orders.id DESC
");

if($res->num_rows > 0){
    while($row = $res->fetch_assoc()){
?>

<tr>
    <td><?= $row['id'] ?></td>
    <td>#<?= $row['id'] ?></td>
    <td><?= $row['name'] ? $row['name'] : "Unknown" ?></td>
    <td>₹<?= $row['total_amount'] ?></td>
    <td><?= $row['created_at'] ?></td>
    <td>
        <a href="invoice.php?id=<?= $row['id'] ?>" 
           class="btn btn-sm btn-primary">
           View Invoice
        </a>
    </td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No Orders Found</td></tr>";
}
?>

</tbody>
</table>

</div>
</div>

<?php include("../includes/footer.php"); ?>