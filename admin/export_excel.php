<?php
include("../config/db.php");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=report.xls");

echo "Date\tOrders\tRevenue\n";

$res = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue 
    FROM orders 
    GROUP BY date
");

while($row = $res->fetch_assoc()){
    echo "{$row['date']}\t{$row['orders']}\t{$row['revenue']}\n";
}
?>