<?php
include("../config/db.php");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=staff_notifications.xls");

echo "Staff Name\tRole\tMessage\tDate\n";

$res = $conn->query("
    SELECT notifications.*, staff.name, staff.role
    FROM notifications
    JOIN staff ON notifications.receiver_id = staff.id
    WHERE receiver_type='staff'
");

while($row = $res->fetch_assoc()){
    echo "{$row['name']}\t{$row['role']}\t{$row['message']}\t{$row['created_at']}\n";
}
?>