<?php
require('database.php');
session_start();
include('Navigation.php');
include('Navigation_report.php');
if (isset($_GET['date'])) {
    $date = mysqli_real_escape_string($connection, $_GET['date']);

    // Fetch all orders for that date
    $orders = mysqli_query($connection, "
        SELECT o.Order_ID, o.Table_ID, o.Order_Date, o.Status, p.Payment_total, o.Order_Close
        FROM order_r o
        LEFT JOIN payment p ON p.Order_ID = o.Order_ID
        WHERE DATE(o.Order_Date) = '$date'
        ORDER BY o.Order_Close DESC
    ");
    ?>
        <form method="GET" id="dateForm"> 
             <input type="date" name="date"
           value="<?= $_GET['date'] ?? '' ?>"
           onchange="if(this.value) window.location='Report_By_Date.php?date='+this.value;">
        </form>
    <?php

    echo "<h2>Orders for " . htmlspecialchars($date) . "</h2>";
    echo "<table border='1' cellpadding='6' cellspacing='0'>
        <tr>
            <th>Order ID</th>
            <th>Table</th>
            <th>Order Date</th>
            <th>Close Time</th>
            <th>Status</th>
            <th colspan='2'>Total Payment</th>

        </tr>";

    while ($row = mysqli_fetch_assoc($orders)) {
        echo "<tr>
            <td>{$row['Order_ID']}</td>
            <td>{$row['Table_ID']}</td>
            <td>{$row['Order_Date']}</td>
            <td>{$row['Order_Close']}</td>
            <td>{$row['Status']}</td>
            <td>" . number_format($row['Payment_total'] ?? 0, 2) . "</td>
             <td class='view_detail' onclick='showOrderDetails({$row['Order_ID']})'>View Detail</td>

        </tr>";
    }
    echo "</table>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
    <style>.modal{
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
}.view_detail{
    cursor: pointer;

}.view_detail:hover{
    background:grey;
}

</style>
</head>
<body>
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