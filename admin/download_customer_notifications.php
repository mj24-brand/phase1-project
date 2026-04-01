<?php
include("../config/db.php");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=customer_notifications.xls");

echo "Customer Name\tMessage\tDate\n";

$res = $conn->query("
    SELECT notifications.*, customers.name 
    FROM notifications
    JOIN customers ON notifications.receiver_id = customers.id
    WHERE receiver_type='customer'
");

while($row = $res->fetch_assoc()){
    echo "{$row['name']}\t{$row['message']}\t{$row['created_at']}\n";
}
?>