<?php
session_start();
require 'database.php';
include('Navigation.php');
// Get all tables
$table_query = mysqli_query($connection, "SELECT * FROM tables_assignment ORDER BY Table_ID ASC");
?>

<h2>Table Dashboard</h2>
<div style="display:flex; flex-wrap:wrap; gap:10px;">
<?php 
while($t = mysqli_fetch_array($table_query)) {
    $color = $t['Status'] == 'Available' ? 'green' : 'red';
    $text  = $t['Status'] == 'Available' ? 'Available' : 'Occupied';
?>
    <form method="post" action="Order.php" style="margin:0;">
        <input type="hidden" name="table_id" value="<?= $t['Table_ID'] ?>">
        <button style="background-color:<?= $color ?>; padding:20px; width:100px; height:100px;">
            Table <?= $t['Table_ID'] ?><br><?= $text ?>
        </button>
    </form>
<?php } ?>
</div>
