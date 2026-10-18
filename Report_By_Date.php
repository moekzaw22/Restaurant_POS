<?php
require('database.php');
session_start();
include('Navigation.php');
include('Navigation_report.php');
$date = $_GET['date'] ?? '';
$orders = false;
$per_page = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$total_pages = 1;
$offset = 0;
$day_summary = ['total_orders' => 0, 'total_items' => 0, 'total_sales' => 0];
if ($date !== '') {
    $safe_date = mysqli_real_escape_string($connection, $date);
    $summary_result = mysqli_query($connection, "
        SELECT COUNT(DISTINCT o.Order_ID) AS total_orders,
               COALESCE(SUM(items.total_items), 0) AS total_items,
               COALESCE(SUM(pay.total_sales), 0) AS total_sales
        FROM order_r o
        LEFT JOIN (SELECT Order_ID, SUM(Quantity) AS total_items FROM order_detail GROUP BY Order_ID) items ON items.Order_ID = o.Order_ID
        LEFT JOIN (SELECT Order_ID, SUM(Payment_total) AS total_sales FROM payment GROUP BY Order_ID) pay ON pay.Order_ID = o.Order_ID
        WHERE DATE(o.Order_Date) = '$safe_date'
    ");
    $day_summary = mysqli_fetch_assoc($summary_result) ?: $day_summary;
    $total_orders = (int)$day_summary['total_orders'];
    $total_pages = max(1, (int)ceil($total_orders / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;
    $orders = mysqli_query($connection, "
        SELECT o.Order_ID, o.Table_ID, o.Order_Date, o.Status, p.Payment_total, o.Order_Close
        FROM order_r o
        LEFT JOIN (SELECT Order_ID, SUM(Payment_total) AS Payment_total FROM payment GROUP BY Order_ID) p ON p.Order_ID = o.Order_ID
        WHERE DATE(o.Order_Date) = '$safe_date'
        ORDER BY o.Order_Close DESC
        LIMIT $per_page OFFSET $offset
    ");
}
?>
<div class="page-heading"><div><p class="eyebrow">Performance</p><h1>Daily report</h1><p class="page-subtitle">Choose a date to review items sold, sales, and its orders.</p></div></div>
<form method="GET" class="filters"><label for="report-date">Report date</label><input id="report-date" type="date" name="date" value="<?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?>"><button class="primary" type="submit">View report</button></form>
<?php if ($orders !== false): ?>
<div class="report-summary"><div class="summary-chip">Orders<strong><?= (int)$day_summary['total_orders'] ?></strong></div><div class="summary-chip">Items sold<strong><?= (int)$day_summary['total_items'] ?></strong></div><div class="summary-chip">Total sales<strong><?= number_format((float)$day_summary['total_sales'], 2) ?></strong></div></div>
<div class="section-heading"><h2>Orders for <?= htmlspecialchars($date) ?></h2></div>
<div class="table-wrap"><table><thead><tr><th>Order</th><th>Table</th><th>Order date</th><th>Closed</th><th>Status</th><th>Total</th><th></th></tr></thead><tbody>
<?php $order_count = 0; while ($row = mysqli_fetch_assoc($orders)): $order_count++; ?>
<tr><td><strong>#<?= (int)$row['Order_ID'] ?></strong></td><td><?= (int)$row['Table_ID'] ?></td><td><?= htmlspecialchars($row['Order_Date']) ?></td><td><?= htmlspecialchars($row['Order_Close'] ?? '—') ?></td><td><span class="badge badge-gray"><?= htmlspecialchars($row['Status']) ?></span></td><td><?= number_format((float)($row['Payment_total'] ?? 0), 2) ?></td><td><button type="button" class="button-small" onclick="showOrderDetails(<?= (int)$row['Order_ID'] ?>)">Details</button></td></tr>
<?php endwhile; if ($order_count === 0): ?><tr><td colspan="7" class="empty-state">No orders were recorded on this date.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="pagination"><span class="pagination-info">Showing <?= $total_orders ? $offset + 1 : 0 ?>–<?= min($offset + $per_page, $total_orders) ?> of <?= $total_orders ?> orders</span><div class="pagination-actions">
    <?php if ($page > 1): ?><a class="button button-small" href="?date=<?= urlencode($date) ?>&amp;page=<?= $page - 1 ?>">← Previous</a><?php else: ?><span class="button button-small disabled">← Previous</span><?php endif; ?>
    <span class="pagination-current">Page <?= $page ?> of <?= $total_pages ?></span>
    <?php if ($page < $total_pages): ?><a class="button button-small" href="?date=<?= urlencode($date) ?>&amp;page=<?= $page + 1 ?>">Next →</a><?php else: ?><span class="button button-small disabled">Next →</span><?php endif; ?>
</div></div>
<?php endif; ?>
<div id="modal" class="modal"><div class="modal-content"><button class="close" onclick="closeModal()" aria-label="Close">×</button><div id="modal-body"></div></div></div>
<script>
function showOrderDetails(orderId){fetch('Order_Detail_Popup.php?order_id='+orderId).then(r=>r.text()).then(html=>{document.getElementById('modal-body').innerHTML=html;document.getElementById('modal').style.display='block';});}
function closeModal(){document.getElementById('modal').style.display='none';}
window.addEventListener('click',e=>{if(e.target===document.getElementById('modal'))closeModal();});
</script>
</main></body></html>
