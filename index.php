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

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>POS Dashboard</title>
    <style>
        table { border-collapse: collapse; margin-bottom: 20px; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #eee; }
        .occupied { background-color: #fdd; }
        .available { background-color: #dfd; }
    </style>
</head>
<body>
<h1>Dashboard</h1>

<h2>Tables</h2>
<table>
    <tr>
        <th>Table ID</th>
        <th>Status</th>
        <th>Order ID</th>
        <th>Action</th>
    </tr>
    <?php foreach ($tables as $t): ?>
    <tr class="<?= $t['Status']=='Occupied' ? 'occupied':'available' ?>">
        <td><?= $t['Table_ID'] ?></td>
        <td><?= $t['Status'] ?></td>
        <td><?= $t['Assign_order'] ?? '-' ?></td>
        <td>
            <a href="order.php?table_id=<?= $t['Table_ID'] ?>">Open Order</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Products</h2>
<table>
    <tr>
        <th>Product Name</th>
        <th>Code</th>
        <th>Price</th>
        <th>Food Type</th>
        <th>Action</th>
    </tr>
    <?php foreach ($products as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['Product_Name']) ?></td>
        <td><?= htmlspecialchars($p['Product_Code']) ?></td>
        <td><?= (int)$p['Item_Price'] ?></td>
        <td><?= htmlspecialchars($p['Food_Type']) ?></td>
        <td>
            <a href="product_add.php?edit=<?= $p['Product_ID'] ?>">Edit</a>
            |
            <a href="product_list.php?delete=<?= $p['Product_ID'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<p><a href="product_add.php">Add New Product</a></p>

</body>
</html>
