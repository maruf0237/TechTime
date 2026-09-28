<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();

$monthRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders
                                     WHERE status != 'cancelled'
                                       AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();

$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

// Most-sold product + top 5 for the bar chart
$topProducts = $pdo->query("SELECT p.id, p.name, SUM(oi.quantity) AS units_sold
                             FROM order_items oi JOIN products p ON p.id = oi.product_id
                             GROUP BY oi.product_id ORDER BY units_sold DESC LIMIT 5")->fetchAll();
$mostSold = $topProducts[0] ?? null;
$maxUnits = $mostSold ? (int)$mostSold['units_sold'] : 1;

// Monthly revenue table (last 12 months with any orders)
$monthlyRevenue = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym,
                                       DATE_FORMAT(created_at, '%b %Y') AS label,
                                       COUNT(*) AS order_count,
                                       SUM(total_amount) AS revenue
                                FROM orders WHERE status != 'cancelled'
                                GROUP BY ym ORDER BY ym DESC LIMIT 12")->fetchAll();

// Recent orders
$recentOrders = $pdo->query("SELECT o.*, u.name AS customer_name FROM orders o
                              JOIN users u ON u.id = o.user_id
                              ORDER BY o.created_at DESC LIMIT 8")->fetchAll();
?>

<div class="stat-grid">
  <div class="stat-card">
    <div class="label">Total revenue</div>
    <div class="value"><?php echo money($totalRevenue); ?></div>
  </div>
  <div class="stat-card">
    <div class="label">This month's revenue</div>
    <div class="value"><?php echo money($monthRevenue); ?></div>
  </div>
  <div class="stat-card">
    <div class="label">Most-sold product</div>
    <div class="value" style="font-size:1.05rem;"><?php echo $mostSold ? clean($mostSold['name']) : '—'; ?></div>
    <?php if ($mostSold): ?><div class="sub"><?php echo (int)$mostSold['units_sold']; ?> units sold</div><?php endif; ?>
  </div>
  <div class="stat-card">
    <div class="label">Orders</div>
    <div class="value"><?php echo $totalOrders; ?></div>
    <div class="sub" style="color:var(--warn);"><?php echo $pendingOrders; ?> pending</div>
  </div>
  <div class="stat-card">
    <div class="label">Customers</div>
    <div class="value"><?php echo $totalUsers; ?></div>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h3 style="margin:0;">Top-selling products</h3></div>
  <?php if (!$topProducts): ?>
    <p style="color:var(--muted);">No sales yet.</p>
  <?php else: foreach ($topProducts as $tp): ?>
    <div class="bar-row">
      <span><?php echo clean($tp['name']); ?></span>
      <div class="bar-track"><div class="bar-fill" style="width: <?php echo max(6, round(((int)$tp['units_sold'] / $maxUnits) * 100)); ?>%;"></div></div>
      <span style="text-align:right; color:var(--muted);"><?php echo (int)$tp['units_sold']; ?> sold</span>
    </div>
  <?php endforeach; endif; ?>
</div>

<div class="panel">
  <div class="panel-head"><h3 style="margin:0;">Monthly revenue</h3></div>
  <?php if (!$monthlyRevenue): ?>
    <p style="color:var(--muted);">No orders yet.</p>
  <?php else: ?>
    <table class="data-table">
      <thead><tr><th>Month</th><th>Orders</th><th>Revenue</th></tr></thead>
      <tbody>
        <?php foreach ($monthlyRevenue as $m): ?>
          <tr>
            <td><?php echo clean($m['label']); ?></td>
            <td><?php echo (int)$m['order_count']; ?></td>
            <td><?php echo money($m['revenue']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<div class="panel">
  <div class="panel-head">
    <h3 style="margin:0;">Recent orders</h3>
    <a href="<?php echo asset('admin/orders.php'); ?>" class="btn btn-ghost btn-sm">View all</a>
  </div>
  <?php if (!$recentOrders): ?>
    <p style="color:var(--muted);">No orders yet.</p>
  <?php else: ?>
    <table class="data-table">
      <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Status</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td>#<?php echo (int)$o['id']; ?></td>
            <td><?php echo clean($o['customer_name']); ?></td>
            <td><?php echo date('M j, Y', strtotime($o['created_at'])); ?></td>
            <td><span class="status-pill status-<?php echo clean($o['status']); ?>"><?php echo clean($o['status']); ?></span></td>
            <td><?php echo money($o['total_amount']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
