<?php
require('database.php');

$table_id = (int)$_POST['table_id'];
$order_id = (int)$_POST['order_id'];

if (!$order_id) {
    die("Invalid order.");
}

// Recalculate total
$sumQuery = mysqli_query($connection,
    "SELECT SUM(Quantity * Product_Price) AS total
     FROM order_detail
     WHERE Order_ID = $order_id");

$row = mysqli_fetch_assoc($sumQuery);
$final_total = (int)($row['total'] ?? 0);

$date = date("Y-m-d H:i:s");
$Order_Close = date("H:i:s");
// Update payment
mysqli_query($connection,
    "UPDATE payment
     SET Payment_total = $final_total,
         Method = 'Cash',
         Payment_Date = '$date'
     WHERE Order_ID = $order_id");

// Close order
mysqli_query($connection,
    "UPDATE order_r
     SET Status = 'Paid',Order_Close = '$Order_Close'
     WHERE Order_ID = $order_id");

// Free table
mysqli_query($connection,
    "UPDATE tables_assignment
     SET Status='Available', Assign_order=NULL
     WHERE Table_ID=$table_id");

// Redirect back to order page
header("Location: order.php?table_id=$table_id");
exit;
?>