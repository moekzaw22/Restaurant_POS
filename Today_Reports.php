<?php
require('database.php');
session_start();
include('Navigation.php');
include('Navigation_report.php');
$date =date("Y-m-d");
// Fetch all orders
$orders = [];
$q = mysqli_query($connection, "
    SELECT o.Order_ID, o.Table_ID, o.Order_Date, o.Status, p.Payment_total, o.Order_Close, o.Order_Open
    FROM order_r o
    LEFT JOIN payment p ON p.Order_ID = o.Order_ID
    WHERE o.Order_Date ='$date' ORDER BY o.Order_Close DESC
");
$item_count = mysqli_query($connection,"SELECT sum(od.Quantity) AS total_qty FROM Order_Detail od LEFT JOIN order_r o ON o.Order_ID= od.Order_ID WHERE o.Order_Date='$date'");
$item_count_q = mysqli_fetch_assoc($item_count);

$b = mysqli_query($connection, "SELECT sum(p.Payment_total) AS total_money FROM payment p LEFT JOIN order_r o ON p.Order_ID =o.Order_ID WHERE o.Order_Date ='$date'");
$total_payment = mysqli_fetch_assoc($b);
//payment total query

while ($row = mysqli_fetch_assoc($q)) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Orders Report</title>
<style>
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
tr:hover { background-color: #f5f5f5; cursor: pointer; }
.modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
}

.modal-content{
    background:#fff;
    width:800px;
    margin:80px auto;
    padding:20px;
}
.close { float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
</style>
<script>
function showOrderDetails(orderId){
    fetch('order_detail_popup.php?order_id=' + orderId)
    .then(response => response.text())
    .then(html => {
        document.getElementById('modal-body').innerHTML = html;
        document.getElementById('modal').style.display = 'block';
    });
}

function closeModal(){
    document.getElementById('modal').style.display = 'none';
}
window.onclick = function(event){
    var modal = document.getElementById("modal");

    // If click on background (not modal-content)
    if(event.target == modal){
        closeModal();
    }
}
</script>
</head>
<body>
<div>
  <button onclick="window.location.href='Reports.php'">
    All Reports
</button>
</div>
<h2>Item Sold Today <?php echo (int)$item_count_q['total_qty'] ?> Total Amount <?php echo number_format($total_payment['total_money'] )?></h2>
<h2>Orders Report</h2>
<table>
<tr>
<th>Order ID</th>
<th>Table</th>
<th>Open</th>
<th>Close</th>
<th>Status</th>
<th>Total</th>
</tr>
<?php foreach($orders as $o): ?>
<tr onclick="showOrderDetails(<?= $o['Order_ID'] ?>)">
<td><?= $o['Order_ID'] ?></td>
<td><?= $o['Table_ID'] ?></td>
<td><?= $o['Order_Open'] ?></td>
<td><?= $o['Order_Close'] ?></td>
<td><?= htmlspecialchars($o['Status']) ?></td>
<td><?= (int)$o['Payment_total'] ?></td>
</tr>
<?php endforeach; ?>
</table>

<!-- Modal -->
<div id="modal" class="modal">
<div class="modal-content">
<span class="close" onclick="closeModal()">Close</span>
<div id="modal-body">
<!-- order details will be loaded here -->
</div>
</div>
</div>

</body>
</html>