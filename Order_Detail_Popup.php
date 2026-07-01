<?php
require('database.php');

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$order_id) { die("Invalid order."); }

$q = mysqli_query($connection, "
    SELECT 
        od.Order_Detail_ID,
        od.Order_ID,
        od.Product_ID,
        p.Product_Name,
        od.Quantity,
        od.Note,
        od.Product_Price,
        (od.Quantity * od.Product_Price) AS Line_Total,
        o.Table_ID,
        o.Order_Date,
        o.Order_Open,
        o.Order_Close,
        o.Status AS Order_Status
    FROM order_detail od
    JOIN product p ON p.Product_ID = od.Product_ID
    JOIN order_r o ON o.Order_ID = od.Order_ID
    WHERE od.Order_ID = $order_id
    ORDER BY od.Order_Detail_ID ASC
");

$items = [];
$grand_total = 0;
$order_info = null; // to hold order-level info

while ($row = mysqli_fetch_assoc($q)) {
    if (!$order_info) {
        $order_info = $row; // first row has the order-level info
    }
    $items[] = $row;
    $grand_total += (int)$row['Line_Total'];
}
?>

<h3>Order #<?= $order_id ?> Details <br>(Table <?= htmlspecialchars($order_info['Table_ID']) ?>)
    Open: <?= htmlspecialchars($order_info['Order_Open']) ?> 
    Close: <?= htmlspecialchars($order_info['Order_Close'] ?? '-') ?> 
    Status: <?= htmlspecialchars($order_info['Order_Status']) ?>
</h3>

<table width="100%" border="1" cellpadding="6" cellspacing="0">
<tr>
<th>Product</th>
<th>Qty</th>
<th>Note</th>
<th>Price</th>
<th>Total</th>
</tr>
<?php foreach($items as $it): ?>
<tr>
<td><?= htmlspecialchars($it['Product_Name']) ?></td>
<td><?= (int)$it['Quantity'] ?></td>
<td><?= htmlspecialchars($it['Note']) ?></td>
<td><?= (int)$it['Product_Price'] ?></td>
<td><?= (int)$it['Line_Total'] ?></td>
</tr>
<?php endforeach; ?>
<tr>
<td colspan="4" align="right"><strong>Grand Total:</strong></td>
<td><strong><?= (int)$grand_total ?></strong></td>
</tr>
</table>