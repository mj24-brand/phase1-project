<?php include("../config/db.php"); ?>

<?php
if(!isset($_GET['id'])){
    die("Invalid Access!");
}

$id = $_GET['id'];

// FETCH CUSTOMER EMAIL
$order = $conn->query("
    SELECT orders.*, customers.email, customers.name 
    FROM orders 
    LEFT JOIN customers ON orders.customer_id = customers.id
    WHERE orders.id=$id
")->fetch_assoc();

if(!$order){
    die("Order not found!");
}

$email = $order['email'];

// PDF LINK
$pdf_link = "http://localhost/hotel_management/admin/generate_pdf.php?id=".$id;

$subject = "Invoice for Order #".$id;

$message = "
Hello ".$order['name'] .",

Your invoice for Order #$id is ready.

Download here:
$pdf_link

Thank you for choosing us!
";

$headers = "From: hotel@example.com";

if(mail($email, $subject, $message, $headers)){
    echo "<h3>Email sent successfully!</h3>";
} else {
    echo "<h3>Email sending failed (configure SMTP)</h3>";
}
?>