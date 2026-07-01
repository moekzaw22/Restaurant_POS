<?php
require('database.php');
session_start();
include('Navigation.php');
include('Navigation_report.php');
$summary_q = mysqli_query($connection, "
    SELECT 
        DATE(o.Order_Date) AS order_day,
        COUNT(o.Order_ID) AS total_orders,
        SUM(p.Payment_total) AS total_revenue
    FROM order_r o
    LEFT JOIN payment p ON p.Order_ID = o.Order_ID
    GROUP BY DATE(o.Order_Date)
    ORDER BY order_day DESC
");
?>

<h2>Daily Reports</h2>

<form method="GET" id="dateForm">
    <input type="date" name="date"
           value="<?= $_GET['date'] ?? '' ?>"
           onchange="if(this.value) window.location='Report_By_Date.php?date='+this.value;">
</form>

<table border="1" cellpadding="6" cellspacing="0">
<tr>
    <th>Date</th>
    <th>Total Orders</th>
    <th>Total Revenue</th>
    <th>Action</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($summary_q)): ?>
<tr>
    <td><?= $row['order_day'] ?></td>
    <td><?= (int)$row['total_orders'] ?></td>
    <td><?= number_format($row['total_revenue'] ?? 0, 2) ?></td>
    <td>
        <a href="Report_By_Date.php?date=<?= $row['order_day'] ?>">
            View Details
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>