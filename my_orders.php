<?php
require_once __DIR__ . '/includes/header.php';
require_login();
$pageTitle = 'My orders';

$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<div class="container">
  <div class="page-header"><h1>My orders</h1><p>Track the status of every order you've placed.</p></div>

  <div style="padding:2rem 0;">
    <?php if (!$orders): ?>
      <div class="empty-state">
        <i class="fa-solid fa-box" style="font-size:2rem;color:var(--muted);"></i>
        <p style="margin:0.75rem 0 1.25rem;">You haven't placed any orders yet.</p>
        <a href="index.php" class="btn btn-solid">Start shopping</a>
      </div>
    <?php else: ?>
      <?php foreach ($orders as $order): ?>
        <div class="order-card">
          <div class="order-card-top">
            <div>
              <strong>Order #<?php echo (int)$order['id']; ?></strong>
              <span style="color:var(--muted); font-size:0.85rem; margin-left:0.5rem;"><?php echo date('M j, Y g:ia', strtotime($order['created_at'])); ?></span>
            </div>
            <span class="status-pill status-<?php echo clean($order['status']); ?>"><?php echo clean($order['status']); ?></span>
          </div>
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="color:var(--muted); font-size:0.9rem;">Total: <strong style="color:var(--ink);"><?php echo money($order['total_amount']); ?></strong></span>
            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-ghost btn-sm">View details</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
