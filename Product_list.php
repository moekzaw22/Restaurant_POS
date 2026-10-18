<?php
require('database.php');
session_start();

// --- Handle Delete ---
if (isset($_GET['delete'])) {
    $pid = (int)$_GET['delete'];
    mysqli_query($connection, "DELETE FROM product WHERE Product_ID=$pid");
    header("Location: Product_list.php");
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
    header("Location: Product_list.php");
    exit;
}

// --- Search and Filter ---
$search = mysqli_real_escape_string($connection, $_GET['search'] ?? '');
$selected = mysqli_real_escape_string($connection, $_GET['selectedtype'] ?? '');

$where = "1"; // default always true
if (!empty($search)) $where .= " AND Product_Name LIKE '%$search%'";
if (!empty($selected)) $where .= " AND Food_Type='$selected'";

// --- Fetch Products ---
$per_page = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$count_result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM product WHERE $where");
$product_count = (int)(mysqli_fetch_assoc($count_result)['total'] ?? 0);
$total_pages = max(1, (int)ceil($product_count / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;
$sql = "SELECT * FROM product WHERE $where ORDER BY Product_Name ASC LIMIT $per_page OFFSET $offset";
$res_product = mysqli_query($connection, $sql);
$pagination_filters = ['search' => $_GET['search'] ?? '', 'selectedtype' => $_GET['selectedtype'] ?? ''];

// --- Product Types ---
$types = ['Drink','Snack','Can','Meal','Fastfood','Extra'];
include('Navigation.php');
?>
<div class="page-heading"><div><p class="eyebrow">Catalog</p><h1>Products</h1><p class="page-subtitle">Keep your menu items, prices, and categories up to date.</p></div><button type="button" class="primary" onclick="openModal()">＋ Add product</button></div>

<!-- Search & Filter -->
<form method="GET" class="filters">
    <select name="selectedtype" onchange="this.form.submit()">
        <option value="">All Types</option>
        <?php foreach($types as $type): ?>
        <option value="<?= $type ?>" <?= ($selected==$type)?'selected':'' ?>><?= $type ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="search" placeholder="Search product..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
<button type="button" onclick="window.location.href='Product_list.php'">Reset</button>
</form>

<!-- Add Product Button -->
<!-- Product Table -->
<div class="table-wrap"><table>
    <thead><tr>
        <th>ID</th>
        <th>Code</th>
        <th>Name</th>
        <th>Price</th>
        <th>Type</th>
        <th>Actions</th>
    </tr></thead><tbody>
    <?php if ($product_count === 0): ?><tr><td colspan="6" class="empty-state">No products match these filters.</td></tr><?php endif; ?>
    <?php while($p=mysqli_fetch_assoc($res_product)): ?>
    <tr>
        <td><?= $p['Product_ID'] ?></td>
        <td><?= htmlspecialchars($p['Product_Code']) ?></td>
        <td><?= htmlspecialchars($p['Product_Name']) ?></td>
        <td><?= number_format($p['Item_Price'],2) ?></td>
        <td><span class="badge badge-gray"><?= htmlspecialchars($p['Food_Type']) ?></span></td>
        <td>
            <a href="#" class="edit-btn" 
               data-id="<?= $p['Product_ID'] ?>"
               data-code="<?= htmlspecialchars($p['Product_Code'], ENT_QUOTES, 'UTF-8') ?>"
               data-name="<?= htmlspecialchars($p['Product_Name'], ENT_QUOTES, 'UTF-8') ?>"
               data-price="<?= htmlspecialchars((string)$p['Item_Price'], ENT_QUOTES, 'UTF-8') ?>"
               data-type="<?= htmlspecialchars($p['Food_Type'], ENT_QUOTES, 'UTF-8') ?>">Edit</a> <span class="muted">·</span>
            <a class="button-danger" href="?delete=<?= (int)$p['Product_ID'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</tbody></table></div>
<div class="pagination"><span class="pagination-info">Showing <?= $product_count ? $offset + 1 : 0 ?>–<?= min($offset + $per_page, $product_count) ?> of <?= $product_count ?> products</span><div class="pagination-actions">
    <?php if ($page > 1): ?><a class="button button-small" href="?<?= htmlspecialchars(http_build_query($pagination_filters + ['page' => $page - 1]), ENT_QUOTES, 'UTF-8') ?>">← Previous</a><?php else: ?><span class="button button-small disabled">← Previous</span><?php endif; ?>
    <span class="pagination-current">Page <?= $page ?> of <?= $total_pages ?></span>
    <?php if ($page < $total_pages): ?><a class="button button-small" href="?<?= htmlspecialchars(http_build_query($pagination_filters + ['page' => $page + 1]), ENT_QUOTES, 'UTF-8') ?>">Next →</a><?php else: ?><span class="button button-small disabled">Next →</span><?php endif; ?>
</div></div>

<!-- Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle">Add Product</h3>
        <form method="POST" id="productForm">
            <input type="hidden" name="Product_ID" id="Product_ID">
            <div class="field"><label for="Product_Code">Product code</label><input type="text" name="Product_Code" id="Product_Code" required></div>
            <div class="field"><label for="Product_Name">Product name</label><input type="text" name="Product_Name" id="Product_Name" required></div>
            <div class="field"><label for="Item_Price">Price</label><input type="number" name="Item_Price" id="Item_Price" step="0.01" required></div>
            <div class="field"><label for="Food_Type">Category</label>
            <select name="Food_Type" id="Food_Type" required>
                <?php foreach($types as $type): ?>
                <option value="<?= $type ?>"><?= $type ?></option>
                <?php endforeach; ?>
            </select></div><br>
            <button class="primary" type="submit" name="save" id="submitBtn">Save product</button>
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

</main></body></html>
