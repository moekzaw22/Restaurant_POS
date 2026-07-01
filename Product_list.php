<?php
require('database.php');
session_start();
include('Navigation.php');

// --- Handle Delete ---
if (isset($_GET['delete'])) {
    $pid = (int)$_GET['delete'];
    mysqli_query($connection, "DELETE FROM product WHERE Product_ID=$pid");
    header("Location: product_list.php");
    exit;
}

// --- Handle Add/Edit ---
if (isset($_POST['save'])) {
    $pid = (int)($_POST['Product_ID'] ?? 0);
    $code = mysqli_real_escape_string($connection, $_POST['Product_Code'] ?? '');
    $name = mysqli_real_escape_string($connection, $_POST['Product_Name'] ?? '');
    $price = (float)($_POST['Item_Price'] ?? 0);
    $Food_Type = mysqli_real_escape_string($connection, $_POST['Food_Type'] ?? '');

    if ($pid) {
        // Update
        $sql = "UPDATE product SET Product_Code='$code', Product_Name='$name', Item_Price=$price, Food_Type='$Food_Type' WHERE Product_ID=$pid";
    } else {
        // Insert
        $sql = "INSERT INTO product (Product_Code, Product_Name, Item_Price, Food_Type) VALUES ('$code','$name',$price,'$Food_Type')";
    }
    mysqli_query($connection, $sql);
    header("Location: product_list.php");
    exit;
}

// --- Search and Filter ---
$search = mysqli_real_escape_string($connection, $_GET['search'] ?? '');
$selected = mysqli_real_escape_string($connection, $_GET['selectedtype'] ?? '');

$where = "1"; // default always true
if (!empty($search)) $where .= " AND Product_Name LIKE '%$search%'";
if (!empty($selected)) $where .= " AND Food_Type='$selected'";

// --- Fetch Products ---
$sql = "SELECT * FROM product WHERE $where ORDER BY Product_Name ASC";
$res_product = mysqli_query($connection, $sql);

// --- Product Types ---
$types = ['Drink','Snack','Can','Meal','Fastfood'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Product List</title>
<style>
body { font-family: Arial;}
button { padding:6px 12px; margin:3px; cursor:pointer; }
table { border-collapse: collapse; width:100%; margin-top:15px; }
table, th, td { border:1px solid #aaa; }
th, td { padding:8px; text-align:left; }
#productModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); }
#productModal .modal-content { background:white; width:400px; margin:100px auto; padding:20px; border-radius:8px; position:relative; }
#productModal span.close { position:absolute; top:10px; right:15px; cursor:pointer; font-weight:bold; font-size:18px; }
#productModal input{padding:10px;}
#productModal label{width:100px;display: flex;}
</style>
</head>
<body>

<h2>Product List</h2>

<!-- Search & Filter -->
<form method="GET">
    <select name="selectedtype" onchange="this.form.submit()">
        <option value="">All Types</option>
        <?php foreach($types as $type): ?>
        <option value="<?= $type ?>" <?= ($selected==$type)?'selected':'' ?>><?= $type ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="search" placeholder="Search product..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
    <button type="button" onclick="window.location.href='product_list.php'">Reset</button>
</form>

<!-- Add Product Button -->
<button type="button" onclick="openModal()">Add Product</button>

<!-- Product Table -->
<table>
    <tr>
        <th>ID</th>
        <th>Code</th>
        <th>Name</th>
        <th>Price</th>
        <th>Type</th>
        <th>Actions</th>
    </tr>
    <?php while($p=mysqli_fetch_assoc($res_product)): ?>
    <tr>
        <td><?= $p['Product_ID'] ?></td>
        <td><?= htmlspecialchars($p['Product_Code']) ?></td>
        <td><?= htmlspecialchars($p['Product_Name']) ?></td>
        <td><?= number_format($p['Item_Price'],2) ?></td>
        <td><?= $p['Food_Type'] ?></td>
        <td>
            <a href="#" class="edit-btn" 
               data-id="<?= $p['Product_ID'] ?>"
               data-code="<?= htmlspecialchars($p['Product_Code']) ?>"
               data-name="<?= htmlspecialchars($p['Product_Name']) ?>"
               data-price="<?= (integer)$p['Item_Price'] ?>"
               data-type="<?= $p['Food_Type'] ?>">Edit</a> |
            <a href="?delete=<?= $p['Product_ID'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Modal -->
<div id="productModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle">Add Product</h3>
        <form method="POST" id="productForm">
            <input type="hidden" name="Product_ID" id="Product_ID">
            <label>Code:</label><input type="text" name="Product_Code" id="Product_Code" required><br>
            <label>Name:</label><input type="text" name="Product_Name" id="Product_Name" required><br>
            <label>Price:</label><input type="number" name="Item_Price" id="Item_Price" step="0.01" required><br>
            <label>Type:</label>
            <select name="Food_Type" id="Food_Type" required>
                <?php foreach($types as $type): ?>
                <option value="<?= $type ?>"><?= $type ?></option>
                <?php endforeach; ?>
            </select><br><br>
            <button type="submit" name="save" id="submitBtn">Save</button>
            <button type="button" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
// Open modal for Add
function openModal() {
    document.getElementById('modalTitle').innerText = 'Add Product';
    document.getElementById('Product_ID').value = '';
    document.getElementById('Product_Code').value = '';
    document.getElementById('Product_Name').value = '';
    document.getElementById('Item_Price').value = '';
    document.getElementById('Food_Type').value = 'Drink';
    document.getElementById('productModal').style.display = 'block';
}

// Close modal
function closeModal() {
    document.getElementById('productModal').style.display = 'none';
}

// Edit button handler
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function(e){
        e.preventDefault();
        document.getElementById('modalTitle').innerText = 'Edit Product';
        document.getElementById('Product_ID').value = this.dataset.id;
        document.getElementById('Product_Code').value = this.dataset.code;
        document.getElementById('Product_Name').value = this.dataset.name;
        document.getElementById('Item_Price').value = this.dataset.price;
        document.getElementById('Food_Type').value = this.dataset.type;
        document.getElementById('productModal').style.display = 'block';
    });
});

// Click outside modal to close
window.onclick = function(event) {
    const modal = document.getElementById('productModal');
    if(event.target == modal) closeModal();
}
</script>

</body>
</html>