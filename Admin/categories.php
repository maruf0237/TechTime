<?php
$pageTitle = 'Orders';
require_once __DIR__ . '/includes/header.php';

$statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $status  = in_array($_POST['status'] ?? '', $statuses, true) ? $_POST['status'] : 'pending';
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $orderId]);
    flash_set('success', 'Order #' . $orderId . ' marked as ' . $status . '.');
    header('Location: ' . asset('admin/orders.php'));
    exit;
}

$filter = in_array($_GET['status'] ?? '', $statuses, true) ? $_GET['status'] : '';
$sql = "SELECT o.*, u.name AS customer_name, u.email AS customer_email FROM orders o JOIN users u ON u.id = o.user_id";
$params = [];
if ($filter) { $sql .= " WHERE o.status = ?"; $params[] = $filter; }
$sql .= " ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="panel">
  <div class="panel-head">
    <h3 style="margin:0;">All orders (<?php echo count($orders); ?>)</h3>
    <form method="get" style="display:flex; gap:0.5rem;">
      <select name="status" class="status-select" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <?php foreach ($statuses as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo $filter === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <?php if (!$orders): ?>
    <p style="color:var(--muted);">No orders found.</p>
  <?php else: ?>
    <table class="data-table">
      <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Payment</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td>#<?php echo (int)$o['id']; ?></td>
            <td><?php echo clean($o['customer_name']); ?><br><span style="color:var(--muted); font-size:0.78rem;"><?php echo clean($o['customer_email']); ?></span></td>
            <td><?php echo date('M j, Y g:ia', strtotime($o['created_at'])); ?></td>
            <td><?php echo $o['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Demo Card'; ?></td>
            <td><?php echo money($o['total_amount']); ?></td>
            <td>
              <form method="post" class="status-form">
                <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>">
                <input type="hidden" name="update_status" value="1">
                <select name="status" class="status-select">
                  <?php foreach ($statuses as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $o['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
