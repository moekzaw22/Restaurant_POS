<?php
require('database.php');
session_start();
include('Navigation.php');

// --- helpers ---
function get_open_order_id(mysqli $conn, int $table_id): ?int {
    $sql = "SELECT Order_ID FROM order_r WHERE Table_ID = $table_id AND Status = 'Open' ORDER BY Order_ID DESC LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($res)) return (int)$row['Order_ID'];
    return null;
}

// --- input ---
$table_id = isset($_POST['table_id']) ? (int)$_POST['table_id']
          : (isset($_GET['table_id']) ? (int)$_GET['table_id'] : 0);
if (!$table_id) { die("No table selected"); }

// Always derive order_id from DB for THIS table
$order_id = get_open_order_id($connection, $table_id);

// --- occupy table ---
if (isset($_POST['occupytable'])) {
    if ($order_id) {
        $msg = "Table $table_id is already occupied (order #$order_id).";
    } else {
        $date = date("Y-m-d H:i:s");
         $open_time =date("H:i:s");
         $close_time=date("H:i:s");
        $ins = "INSERT INTO order_r (Table_ID, Order_Date,Order_Open, Status) VALUES ($table_id, '$date','$open_time', 'Open')";
        if (!mysqli_query($connection, $ins)) die("Error creating order: " . mysqli_error($connection));

        $order_id = mysqli_insert_id($connection);
        $insert_payment = "INSERT INTO payment (Order_ID, Payment_total, Method, Payment_Date) VALUES ($order_id, 0, '', '$date')";
        mysqli_query($connection, $insert_payment);

        $upd = "UPDATE tables_assignment SET Status='Occupied', Assign_order=$order_id WHERE Table_ID=$table_id";
        mysqli_query($connection, $upd);

        $msg = "Table $table_id occupied. Created order #$order_id.";
    }
}

// --- cancel item ---
if (isset($_POST['cancel_item'])) {
    $od_id = (int)$_POST['order_detail_id'];
    mysqli_query($connection, "DELETE FROM order_detail WHERE Order_Detail_ID=$od_id");
    // update payment total
    mysqli_query($connection, "
        UPDATE payment p
        JOIN (SELECT Order_ID, SUM(Quantity*Product_Price) AS t FROM order_detail WHERE Order_ID=$order_id) x
        ON p.Order_ID=x.Order_ID
        SET p.Payment_total = IFNULL(x.t,0)
    ");
    $msg = "Item canceled.";
}

// --- add product + extras ---
if (isset($_POST['addproduct'])) {
    if (!$order_id) {
        $msg = "Please occupy the table first before adding items.";
    } else {
        $extra_total = 0;
        $extra_note  = "";

        if (!empty($_POST['extra'])) {
            foreach ($_POST['extra'] as $extra_code) {
                $extra_code = mysqli_real_escape_string($connection, $extra_code);
                $exq = mysqli_query($connection, "SELECT Product_Name, Item_Price FROM product WHERE Product_Code='$extra_code' LIMIT 1");
                if ($ex = mysqli_fetch_assoc($exq)) {
                    $extra_total += (int)$ex['Item_Price'];
                    $extra_note .= "+" . $ex['Product_Name'] . " ";
                }
            }
        }

        $product_code = mysqli_real_escape_string($connection, $_POST['product_code'] ?? '');
        $quantity     = max(1, (int)($_POST['quantity'] ?? 1));
        $note         = mysqli_real_escape_string($connection, $_POST['note'] ?? '');

        $p = mysqli_query($connection, "SELECT Product_ID, Item_Price FROM product WHERE Product_Code='$product_code' LIMIT 1");
        if ($prod = mysqli_fetch_assoc($p)) {
            $pid = (int)$prod['Product_ID'];
            $price = (int)$prod['Item_Price'] + $extra_total;
            $note  = trim($note . " " . $extra_note);

            $ins = "INSERT INTO order_detail (Order_ID, Product_ID, Quantity, Note, Product_Price) VALUES ($order_id, $pid, $quantity, '$note', $price)";
            if (!mysqli_query($connection, $ins)) die("Add item error: " . mysqli_error($connection));

            // update payment total
            mysqli_query($connection, "
                UPDATE payment p
                JOIN (SELECT Order_ID, SUM(Quantity*Product_Price) AS t FROM order_detail WHERE Order_ID=$order_id) x
                ON p.Order_ID=x.Order_ID
                SET p.Payment_total = x.t
            ");

            $msg = "Added item ($product_code) x $quantity.";
        } else {
            $msg = "Invalid product code: $product_code";
        }
    }
}

// Refresh order_id
$order_id = get_open_order_id($connection, $table_id);

// Load items for this table’s open order
$items = [];
$grand_total = 0;
if ($order_id) {
    $q = mysqli_query($connection, "
        SELECT od.Order_Detail_ID, p.Product_Name, od.Quantity, od.Note, od.Product_Price, (od.Quantity*od.Product_Price) AS Line_Total
        FROM order_detail od
        JOIN product p ON p.Product_ID = od.Product_ID
        WHERE od.Order_ID = $order_id
        ORDER BY od.Order_Detail_ID ASC
    ");
    while ($row = mysqli_fetch_assoc($q)) {
        $items[] = $row;
        $grand_total += (int)$row['Line_Total'];
    }
}
?>
<script>
function openExtraModal(){document.getElementById('extraModal').style.display='block';}
function closeExtraModal(){document.getElementById('extraModal').style.display='none';}
function updateExtraCount(){var count=document.querySelectorAll('#extraModal input[name="extra[]"]:checked').length;document.getElementById('extraSelectionLabel').textContent=count ? count+' selected' : 'Optional add-ons';}
window.addEventListener('click',function(event){if(event.target===document.getElementById('extraModal'))closeExtraModal();});
window.addEventListener('keydown',function(event){if(event.key==='Escape')closeExtraModal();});
</script>
<div class="page-heading"><div><p class="eyebrow">Table service</p><div class="title-row"><h1>Table <?= htmlspecialchars($table_id) ?></h1><?php if ($order_id): ?><span class="badge badge-orange">❗️Already Occupied (Order #<?= (int)$order_id ?>)</span><?php endif; ?></div></div><a class="button" href="Tables.php">← All tables</a></div>
<?php if (!empty($msg)) echo '<div class="panel">'.htmlspecialchars($msg).'</div>'; ?>

<?php if (!$order_id): ?>
<form method="POST" class="panel">
    <input type="hidden" name="table_id" value="<?= htmlspecialchars($table_id) ?>">
    <button class="primary" type="submit" name="occupytable">Occupy Table</button>
</form>
<?php endif; ?>
<?php if ($order_id): ?>
<div class="order-layout"><form method="POST" class="panel order-form">
    <div><p class="eyebrow">Add to order</p><h2>New item</h2></div>
    <input type="hidden" name="table_id" value="<?= htmlspecialchars($table_id) ?>">
    <label>Product Code:</label>
    <input type="text" name="product_code" required autocomplete="off">
    <label>Qty:</label>
    <input type="number" name="quantity" value="1" min="1">
    <label>Note:</label>
    <input type="text" name="note">
    <button type="button" onclick="openExtraModal()">＋ Choose extras <span id="extraSelectionLabel" class="muted">Optional add-ons</span></button>
    <div id="extraModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="extraModalTitle">
      <div class="modal-content extra-modal-content">
        <button class="close" type="button" onclick="closeExtraModal()" aria-label="Close">×</button>
        <p class="eyebrow">Customize this item</p><h2 id="extraModalTitle">Choose extras</h2>
        <p class="page-subtitle">Select any add-ons to include with this order item.</p>
        <div class="extra-options">
        <?php
        $extra_q = mysqli_query($connection, "SELECT Product_Code, Product_Name, Item_Price FROM product WHERE Food_Type='Extra' ORDER BY Product_Name");
        $extra_count = 0;
        while($ex = mysqli_fetch_assoc($extra_q)) {
            $extra_count++;
            echo '<label class="extra-option"><input type="checkbox" name="extra[]" value="'.htmlspecialchars($ex['Product_Code'], ENT_QUOTES, 'UTF-8').'" onchange="updateExtraCount()">'
                 .'<span>'.htmlspecialchars($ex['Product_Name']).'</span><strong>+'.number_format((float)$ex['Item_Price'], 2).'</strong></label>';
        }
        ?>
        <?php if ($extra_count === 0): ?><p class="empty-state">No products are in the Extra category yet.</p><?php endif; ?>
        </div>
        <div class="modal-actions"><button type="button" onclick="closeExtraModal()">Close</button><button class="primary" type="button" onclick="closeExtraModal()">Done</button></div>
      </div>
    </div>
    <button class="primary" type="submit" name="addproduct">Add item</button>
</form>

<section class="panel"><div class="section-heading" style="margin-top:0"><div><p class="eyebrow">Active order</p><h2>Order #<?= (int)$order_id ?></h2></div><span class="badge badge-orange">In progress</span></div>
<div class="table-wrap"><table>
<tr>
<th>Product</th>
<th>Qty</th>
<th>Note</th>
<th>Price</th>
<th>Total</th>
<th>Action</th>
</tr>
<?php foreach ($items as $it): ?>
<tr>
<td><?= htmlspecialchars($it['Product_Name']) ?></td>
<td><?= (int)$it['Quantity'] ?></td>
<td><?= htmlspecialchars($it['Note']) ?></td>
<td><?= number_format((float)$it['Product_Price'], 2) ?></td>
<td><?= number_format((float)$it['Line_Total'], 2) ?></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="table_id" value="<?= $table_id ?>">
<input type="hidden" name="order_detail_id" value="<?= $it['Order_Detail_ID'] ?>">
<button class="button-small button-danger" type="submit" name="cancel_item">Remove</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<tr>
<td colspan="4" style="text-align:right"><strong>Grand total</strong></td>
<td><strong><?= number_format((float)$grand_total, 2) ?></strong></td>
<td></td>
</tr>
</table></div>

<form action="check_out_function.php" method="POST">
    <input type="hidden" name="table_id" value="<?= htmlspecialchars($table_id) ?>">
    <input type="hidden" name="order_id" value="<?= $order_id ?>">
    <button class="primary" type="submit" name="btncheckout" <?= empty($items) ? 'disabled' : '' ?>>Check out · <?= number_format((float)$grand_total, 2) ?></button>
</form>
</section></div>

<?php else: ?>
<p>No open order yet for this table.</p>
<?php endif; ?>

<p><a href="index.php">← Back to tables</a></p>
</main></body></html>
