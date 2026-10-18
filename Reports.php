<?php
require('database.php');
session_start();
$period = ($_GET['period'] ?? 'daily') === 'monthly' ? 'monthly' : 'daily';
$group_by = $period === 'monthly'
    ? "DATE_FORMAT(o.Order_Date, '%Y-%m')"
    : 'DATE(o.Order_Date)';
$label_by = $period === 'monthly'
    ? "DATE_FORMAT(o.Order_Date, '%M %Y')"
    : "DATE_FORMAT(o.Order_Date, '%b %e, %Y')";
$date_column = $period === 'daily' ? 'DATE(o.Order_Date)' : 'NULL';
$per_page = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$period_count_q = mysqli_query($connection, "SELECT COUNT(*) AS total_periods FROM (SELECT $group_by AS period_key FROM order_r o GROUP BY $group_by) report_periods");
$total_periods = (int)(mysqli_fetch_assoc($period_count_q)['total_periods'] ?? 0);
$total_pages = max(1, (int)ceil($total_periods / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;
$summary_q = mysqli_query($connection, "
    SELECT $group_by AS report_key,
           $label_by AS report_label,
           $date_column AS report_date,
           COUNT(DISTINCT o.Order_ID) AS total_orders,
           COALESCE(SUM(items.total_items), 0) AS total_items,
           COALESCE(SUM(pay.total_sales), 0) AS total_sales
    FROM order_r o
    LEFT JOIN (
        SELECT Order_ID, SUM(Quantity) AS total_items
        FROM order_detail GROUP BY Order_ID
    ) items ON items.Order_ID = o.Order_ID
    LEFT JOIN (
        SELECT Order_ID, SUM(Payment_total) AS total_sales
        FROM payment GROUP BY Order_ID
    ) pay ON pay.Order_ID = o.Order_ID
    GROUP BY $group_by, $label_by
    ORDER BY report_key DESC
    LIMIT $per_page OFFSET $offset
");
include('Navigation.php');
include('Navigation_report.php');
?>
<div class="page-heading"><div><p class="eyebrow">Performance</p><h1><?= $period === 'monthly' ? 'Monthly report' : 'Daily report' ?></h1><p class="page-subtitle">Compare orders, items sold, and sales across <?= $period === 'monthly' ? 'months' : 'days' ?>.</p></div></div>
<div class="period-switch" aria-label="Report period"><a class="<?= $period === 'daily' ? 'active' : '' ?>" href="Reports.php?period=daily">Daily</a><a class="<?= $period === 'monthly' ? 'active' : '' ?>" href="Reports.php?period=monthly">Monthly</a></div>
<?php if ($period === 'daily'): ?>
<form method="GET" id="dateForm" class="filters"><input type="hidden" name="period" value="daily"><label for="date">Open a specific day</label><input id="date" type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '', ENT_QUOTES, 'UTF-8') ?>" onchange="if(this.value) window.location='Report_By_Date.php?date='+this.value;"><button type="button" onclick="const d=document.getElementById('date').value;if(d)window.location='Report_By_Date.php?date='+d;">View day</button></form>
<?php endif; ?>
<div class="table-wrap"><table><thead><tr>
    <th><?= $period === 'monthly' ? 'Month' : 'Date' ?></th><th>Orders</th><th>Items sold</th><th>Total sales</th>
    <?php if ($period === 'daily'): ?><th>Details</th><?php endif; ?>
</tr></thead><tbody>
<?php $has_rows = false; while ($row = mysqli_fetch_assoc($summary_q)): $has_rows = true; ?>
<tr>
    <td><strong><?= htmlspecialchars($row['report_label']) ?></strong></td>
    <td><?= (int)$row['total_orders'] ?></td>
    <td><?= (int)$row['total_items'] ?></td>
    <td><?= number_format((float)$row['total_sales'], 2) ?></td>
    <?php if ($period === 'daily'): ?><td><a href="Report_By_Date.php?date=<?= urlencode($row['report_date']) ?>">View orders</a></td><?php endif; ?>
</tr>
<?php endwhile; if (!$has_rows): ?><tr><td colspan="<?= $period === 'daily' ? 5 : 4 ?>" class="empty-state">No sales reports are available yet.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="pagination"><span class="pagination-info">Showing <?= $total_periods ? $offset + 1 : 0 ?>–<?= min($offset + $per_page, $total_periods) ?> of <?= $total_periods ?> <?= $period === 'monthly' ? 'months' : 'days' ?></span><div class="pagination-actions">
    <?php if ($page > 1): ?><a class="button button-small" href="?period=<?= $period ?>&amp;page=<?= $page - 1 ?>">← Previous</a><?php else: ?><span class="button button-small disabled">← Previous</span><?php endif; ?>
    <span class="pagination-current">Page <?= $page ?> of <?= $total_pages ?></span>
    <?php if ($page < $total_pages): ?><a class="button button-small" href="?period=<?= $period ?>&amp;page=<?= $page + 1 ?>">Next →</a><?php else: ?><span class="button button-small disabled">Next →</span><?php endif; ?>
</div></div>
</main></body></html>
