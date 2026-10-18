<?php
require('database.php');
session_start();
$date =date("Y-m-d");
// Fetch today's orders in pages of 20.
$per_page = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$count_result = mysqli_query($connection, "SELECT COUNT(*) AS total_orders FROM order_r o LEFT JOIN payment p ON p.Order_ID = o.Order_ID WHERE o.Order_Date = '$date'");
$total_orders = (int)(mysqli_fetch_assoc($count_result)['total_orders'] ?? 0);
$total_pages = max(1, (int)ceil($total_orders / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;
$orders = [];
$q = mysqli_query($connection, "
    SELECT o.Order_ID, o.Table_ID, o.Order_Date, o.Status, p.Payment_total, o.Order_Close, o.Order_Open
    FROM order_r o
    LEFT JOIN payment p ON p.Order_ID = o.Order_ID
    WHERE o.Order_Date ='$date' ORDER BY o.Order_Close DESC LIMIT $per_page OFFSET $offset
");
$item_count = mysqli_query($connection,"SELECT sum(od.Quantity) AS total_qty FROM Order_Detail od LEFT JOIN order_r o ON o.Order_ID= od.Order_ID WHERE o.Order_Date='$date'");
$item_count_q = mysqli_fetch_assoc($item_count);

$b = mysqli_query($connection, "SELECT sum(p.Payment_total) AS total_money FROM payment p LEFT JOIN order_r o ON p.Order_ID =o.Order_ID WHERE o.Order_Date ='$date'");
$total_payment = mysqli_fetch_assoc($b);
//payment total query

while ($row = mysqli_fetch_assoc($q)) {
    $orders[] = $row;
}
include('Navigation.php');
include('Navigation_report.php');
?>
<script>
function showOrderDetails(orderId){fetch('Order_Detail_Popup.php?order_id=' + orderId).then(response => response.text()).then(html => {document.getElementById('modal-body').innerHTML = html;document.getElementById('modal').style.display = 'block';});}
function closeModal(){document.getElementById('modal').style.display = 'none';}
window.addEventListener('click', function(event){if(event.target === document.getElementById('modal')) closeModal();});
</script>
<div class="page-heading"><div><p class="eyebrow">Daily activity · <?= htmlspecialchars($date) ?></p><h1>Today’s report</h1><p class="page-subtitle">Orders and sales recorded today.</p></div></div>
<div class="report-summary"><div class="summary-chip">Items sold<strong><?= (int)($item_count_q['total_qty'] ?? 0) ?></strong></div><div class="summary-chip">Total sales<strong><?= number_format((float)($total_payment['total_money'] ?? 0), 2) ?></strong></div><div class="summary-chip">Orders<strong><?= $total_orders ?></strong></div></div>
<div class="section-heading"><h2>Orders</h2></div>
<div class="table-wrap"><table>
<thead><tr>
<th>Order ID</th>
<th>Table</th>
<th>Open</th>
<th>Close</th>
<th>Status</th>
<th>Total</th>
</tr></thead><tbody>
<?php if ($total_orders === 0): ?><tr><td colspan="6" class="empty-state">No orders have been recorded today.</td></tr><?php endif; ?>
<?php foreach($orders as $o): ?>
<tr onclick="showOrderDetails(<?= (int)$o['Order_ID'] ?>)" style="cursor:pointer">
<td><?= $o['Order_ID'] ?></td>
<td><?= $o['Table_ID'] ?></td>
<td><?= $o['Order_Open'] ?></td>
<td><?= $o['Order_Close'] ?></td>
<td><?= htmlspecialchars($o['Status']) ?></td>
<td><?= number_format((float)$o['Payment_total'], 2) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="pagination"><span class="pagination-info">Showing <?= $total_orders ? $offset + 1 : 0 ?>–<?= min($offset + $per_page, $total_orders) ?> of <?= $total_orders ?> orders</span><div class="pagination-actions">
    <?php if ($page > 1): ?><a class="button button-small" href="?page=<?= $page - 1 ?>">← Previous</a><?php else: ?><span class="button button-small disabled">← Previous</span><?php endif; ?>
    <span class="pagination-current">Page <?= $page ?> of <?= $total_pages ?></span>
    <?php if ($page < $total_pages): ?><a class="button button-small" href="?page=<?= $page + 1 ?>">Next →</a><?php else: ?><span class="button button-small disabled">Next →</span><?php endif; ?>
</div></div>

<!-- Modal -->
<div id="modal" class="modal">
<div class="modal-content">
<button class="close" onclick="closeModal()" aria-label="Close">×</button>
<div id="modal-body">
<!-- order details will be loaded here -->
</div>
</div>
</div>

</main></body></html>
