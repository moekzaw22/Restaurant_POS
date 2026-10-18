<?php
session_start();
require 'database.php';
if (isset($_POST['add_table'])) {
    if (mysqli_query($connection, "INSERT INTO tables_assignment (Status, Assign_order) VALUES ('Available', NULL)")) {
        header('Location: Tables.php?added=1');
        exit;
    }
    $table_error = 'Could not add a table. Please try again.';
}
// Get all tables
$table_query = mysqli_query($connection, "SELECT * FROM tables_assignment ORDER BY Table_ID ASC");
include('Navigation.php');
?>

<div class="page-heading"><div><p class="eyebrow">Floor plan</p><h1>Tables</h1><p class="page-subtitle">Choose a table to view its current order or start service.</p></div><form method="POST"><button class="primary" type="submit" name="add_table" value="1">＋ Add table</button></form></div>
<?php if (isset($_GET['added'])): ?><p class="notice notice-success">Table added and ready for service.</p><?php endif; ?>
<?php if (isset($table_error)): ?><p class="notice notice-error"><?= htmlspecialchars($table_error) ?></p><?php endif; ?>
<div class="table-grid">
<?php 
while($t = mysqli_fetch_array($table_query)) {
    $available = $t['Status'] == 'Available';
    $text = $available ? 'Available' : 'Occupied';
?>
    <a class="table-card" href="Order.php?table_id=<?= (int)$t['Table_ID'] ?>">
        <div class="table-card-top"><span class="table-number">Table <?= (int)$t['Table_ID'] ?></span><span class="status-pill <?= $available ? 'status-available' : 'status-occupied' ?>"><i class="status-dot"></i><?= $text ?></span></div>
        <div class="table-meta"><?= $available ? 'Ready for guests' : 'View active order' ?><?= !empty($t['Assign_order']) ? ' · Order #'.(int)$t['Assign_order'] : '' ?></div>
    </a>
<?php } ?>
</div>
</main></body></html>
