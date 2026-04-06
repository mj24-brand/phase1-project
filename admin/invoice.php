<?php  include("auth.php");?>
<?php include("../config/db.php"); ?>
<?php include("../includes/notify.php"); ?>

<?php
if(!isset($_GET['id'])){
    die("Invalid Access!");
}

$id = $_GET['id'];

// ORDER + CUSTOMER
$order = $conn->query("
    SELECT orders.*, customers.id as cid, customers.name, customers.phone, customers.email 
    FROM orders 
    LEFT JOIN customers ON orders.customer_id = customers.id
    WHERE orders.id=$id
")->fetch_assoc();

if(!$order){
    die("Order not found!");
}

// ITEMS
$items = $conn->query("SELECT * FROM order_items WHERE order_id=$id");
?>

<!DOCTYPE html>
<html>
<head>
<title>Invoice</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f5f7fa; }
.invoice-box {
    background:white;
    padding:30px;
    max-width:800px;
    margin:auto;
    margin-top:40px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}
.header { display:flex; justify-content:space-between; }
</style>

</head>
<body>

<div class="invoice-box">

<!-- HEADER -->
<div class="header mb-4">
    <div>
        <h3>Hotel Mangalore International</h3>
        <p>Restaurant & Lodge</p>
    </div>

    <div>
        <h5>Invoice</h5>
        <p>#<?= $order['id'] ?></p>
    </div>
</div>

<hr>

<!-- CUSTOMER -->
<h5>Customer Details</h5>
<p>
Name: <?= $order['name'] ?><br>
Phone: <?= $order['phone'] ?><br>
Email: <?= $order['email'] ?>
</p>

<hr>

<!-- ITEMS -->
<h5>Order Items</h5>

<table class="table table-bordered">
<tr>
    <th>Item</th>
    <th>Qty</th>
    <th>Price</th>
    <th>Total</th>
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
    <td>₹<?= $row['price'] ?></td>
    <td>₹<?= $sub ?></td>
</tr>
<?php } ?>
</table>

<hr>

<h4 class="text-end">Grand Total: ₹<?= $total ?></h4>

<!-- SEND INVOICE -->
<form method="POST" class="text-end mt-3">
    <button name="send_invoice" class="btn btn-success">
        Send Invoice to Customer
    </button>
</form>

<?php
if(isset($_POST['send_invoice'])){

    // PDF LINK
    $pdf_link = "http://localhost/hotel_management/admin/generate_pdf.php?id=".$id;

    $message = "Invoice for Order #".$id." is ready. Download here: ".$pdf_link;

    sendNotification($conn, $message, 'manual', 'customer', $order['cid']);

    echo "<div class='alert alert-success mt-3'>
            Invoice PDF sent to customer!
          </div>";
}
?>

<!-- ACTION BUTTONS -->
<div class="text-center mt-4">

    <button onclick="window.print()" class="btn btn-dark">
        Print Invoice
    </button>

    <a href="generate_pdf.php?id=<?= $id ?>" class="btn btn-danger">
        Download PDF
    </a>

    <a href="send_email.php?id=<?= $id ?>" class="btn btn-primary">
        Send Email
    </a>

    <a target="_blank" 
       href="https://wa.me/?text=Invoice%20Download:%20http://localhost/hotel_management/admin/generate_pdf.php?id=<?= $id ?>" 
       class="btn btn-success">
       WhatsApp
    </a>

</div>

</div>

</body>
</html>