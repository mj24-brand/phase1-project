<?php include("../config/db.php"); ?>

<?php
$id = $_GET['id'];

$order = $conn->query("
    SELECT orders.*, customers.name 
    FROM orders 
    LEFT JOIN customers ON orders.customer_id = customers.id
    WHERE orders.id=$id
")->fetch_assoc();

$items = $conn->query("SELECT * FROM order_items WHERE order_id=$id");
?>

<!DOCTYPE html>
<html>
<head>
<title>Invoice PDF</title>
</head>

<body>

<h2>Hotel Mangalore International</h2>
<hr>

<p><strong>Order ID:</strong> <?= $id ?></p>
<p><strong>Customer:</strong> <?= $order['name'] ?></p>

<table border="1" width="100%">
<tr>
    <th>Item</th>
    <th>Qty</th>
    <th>Price</th>
</tr>

<?php
$total = 0;

while($row = $items->fetch_assoc()){
    $sub = $row['price'] * $row['quantity'];
    $total += $sub;
?>
<tr>
    <td><?= $row['item_name'] ?></td>
    <td><?= $row['quantity'] ?></td>
    <td><?= $sub ?></td>
</tr>
<?php } ?>

</table>

<h3>Total: ₹<?= $total ?></h3>

</body>
</html>