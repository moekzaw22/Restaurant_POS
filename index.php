<?php
require('database.php');
include('Navigation.php');
// Fetch products
$products = [];
$q = mysqli_query($connection, "SELECT * FROM product ORDER BY Food_Type, Product_Name");
while ($row = mysqli_fetch_assoc($q)) {
    $products[] = $row;
}

// Fetch tables
$tables = [];
$tq = mysqli_query($connection, "SELECT * FROM tables_assignment ORDER BY Table_ID");
while ($t = mysqli_fetch_assoc($tq)) {
    $tables[] = $t;
}
?>

<div class="page-heading"><div><p class="eyebrow">Restaurant operations</p><p class="page-subtitle">A live snapshot of your dining room and menu.</p></div><a class="button button-primary" href="Tables.php">Manage tables</a></div>
<?php $occupied_count = 0; foreach ($tables as $table) { if ($table['Status'] === 'Occupied') $occupied_count++; } ?>
<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Dining tables</div><div class="stat-value"><?= count($tables) ?></div><div class="stat-note">Across your restaurant</div></div>
    <div class="stat-card"><div class="stat-label">Currently occupied</div><div class="stat-value"><?= $occupied_count ?></div><div class="stat-note">Active table sessions</div></div>
    <div class="stat-card"><div class="stat-label">Menu items</div><div class="stat-value"><?= count($products) ?></div><div class="stat-note">Products available to serve</div></div>
</div>
<div class="section-heading"><h2>Table overview</h2><a href="Tables.php">See all tables →</a></div>
<div class="table-wrap"><table>
<thead>
    <tr>
        <th>Table ID</th>
        <th>Status</th>
        <th>Order ID</th>
        <th>Action</th>
    </tr></thead><tbody>
    <?php foreach ($tables as $t): ?>
    <tr>
        <td><strong>Table <?= (int)$t['Table_ID'] ?></strong></td>
        <td><span class="status-pill <?= $t['Status']=='Occupied' ? 'status-occupied':'status-available' ?>"><i class="status-dot"></i><?= htmlspecialchars($t['Status']) ?></span></td>
        <td><?= $t['Assign_order'] ?? '-' ?></td>
        <td>
            <a class="button button-small" href="Order.php?table_id=<?= (int)$t['Table_ID'] ?>">Open table</a>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody></table></div>

<div class="section-heading"><h2>Menu preview</h2><a href="Product_list.php">Manage products →</a></div>
<div class="table-wrap"><table>
    <thead><tr>
        <th>Product Name</th>
        <th>Code</th>
        <th>Price</th>
        <th>Food Type</th>
        <th>Action</th>
    </tr></thead><tbody>
    <?php foreach ($products as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['Product_Name']) ?></td>
        <td><?= htmlspecialchars($p['Product_Code']) ?></td>
        <td><?= number_format((float)$p['Item_Price'], 2) ?></td>
        <td><?= htmlspecialchars($p['Food_Type']) ?></td>
        <td><a href="Product_list.php">View in products</a></td>
    </tr>
    <?php endforeach; ?>
</tbody></table></div>
</main></body></html>
