<?php
include "../config/db.php";
$guest_name = $_POST['guest_name'];

$tax = isset($_POST['tax']) ? (float)$_POST['tax'] : 0;
$discount = isset($_POST['discount']) && $_POST['discount'] !== '' ? (float)$_POST['discount'] : 0;
$payment_mode = $_POST['payment_mode'];
$subtotal = 0;

foreach ($_POST['qty'] as $key => $qty) {
    $qty = (float)$qty;
    $price = (float)$_POST['price'][$key];

    $subtotal += ($qty * $price);
}

$taxAmount = ($subtotal * $tax) / 100;
$total = $subtotal + $taxAmount - $discount;

// Invoice number
$res = mysqli_query($conn, "SELECT invoice_number FROM invoices ORDER BY id DESC LIMIT 1");
$row = mysqli_fetch_assoc($res);
$invoice_number = $row ? $row['invoice_number'] + 1 : 1001;

// this is to Save invoice
mysqli_query($conn, "INSERT INTO invoices 
(invoice_number, guest_name, subtotal, tax, discount, total, payment_mode)
VALUES 
('$invoice_number','$guest_name','$subtotal','$taxAmount','$discount','$total','$payment_mode')");

// Save items 
foreach ($_POST['item'] as $key => $item) {
    $qty = $_POST['qty'][$key];
    $price = $_POST['price'][$key];

    mysqli_query($conn, "INSERT INTO invoice_items 
    (invoice_number, item_name, qty, price)
    VALUES 
    ('$invoice_number','$item','$qty','$price')");
}

header("Location: receipt.php?id=".$invoice_number);
exit;
?>