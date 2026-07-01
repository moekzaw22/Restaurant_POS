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
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Order - Table <?= htmlspecialchars($table_id) ?></title>
<script>
function toggleExtra(){
    var panel = document.getElementById("extraPanel");
    panel.style.display = (panel.style.display === "none") ? "block" : "none";
}
</script>
</head>
<body>
<h2>Table <?= htmlspecialchars($table_id) ?></h2>
<?php if (!empty($msg)) echo "<p><strong>$msg</strong></p>"; ?>

<form method="POST">
    <input type="hidden" name="table_id" value="<?= htmlspecialchars($table_id) ?>">
    <button type="submit" name="occupytable" <?= $order_id ? 'disabled' : '' ?>>
        <?= $order_id ? "❗️Already Occupied (Order #$order_id)" : "Occupy Table" ?>
    </button>
</form>
<hr>

<?php if ($order_id): ?>
<form method="POST">
    <input type="hidden" name="table_id" value="<?= htmlspecialchars($table_id) ?>">
    <label>Product Code:</label>
    <input type="text" name="product_code" required autocomplete="off">
    <label>Qty:</label>
    <input type="number" name="quantity" value="1" min="1">
    <label>Note:</label>
    <input type="text" name="note">
    <button type="button" onclick="toggleExtra()">+ Add Extra</button>
    <div id="extraPanel" style="display:none; margin-top:10px; border:1px solid #ccc; padding:10px;">
        <h4>Extras</h4>
        <?php
        $extra_q = mysqli_query($connection, "SELECT Product_Code, Product_Name, Item_Price FROM product WHERE Food_Type='Extra' ORDER BY Product_Name");
        while($ex = mysqli_fetch_assoc($extra_q)) {
            echo '<label><input type="checkbox" name="extra[]" value="'.htmlspecialchars($ex['Product_Code']).'">'
                 .htmlspecialchars($ex['Product_Name']).' (+'.(int)$ex['Item_Price'].')</label><br>';
        }
        ?>
    </div>
    <br>
    <button type="submit" name="addproduct">Add</button>
</form>

<h3>Order #<?= $order_id ?> Items</h3>
<table width="100%" border="1" cellpadding="6" cellspacing="0">
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
<td><?= (int)$it['Product_Price'] ?></td>
<td><?= (int)$it['Line_Total'] ?></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="table_id" value="<?= $table_id ?>">
<input type="hidden" name="order_detail_id" value="<?= $it['Order_Detail_ID'] ?>">
<button type="submit" name="cancel_item">Cancel</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<tr>
<td colspan="4" align="right"><strong>Grand Total:</strong></td>
<td><strong><?= (int)$grand_total ?></strong></td>
<td></td>
</tr>
</table>

<form action="check_out_function.php" method="POST">
    <input type="hidden" name="table_id" value="<?= htmlspecialchars($table_id) ?>">
    <input type="hidden" name="order_id" value="<?= $order_id ?>">
    <button type="submit" name="btncheckout" <?= empty($items) ? 'disabled' : '' ?>>Check Out #<?= $order_id ?></button>
</form>

<?php else: ?>
<p>No open order yet for this table.</p>
<?php endif; ?>

<p><a href="index.php">← Back to tables</a></p>
</body>
</html>