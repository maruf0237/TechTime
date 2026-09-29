<?php
require_once __DIR__ . '/includes/header.php';
require_login();
$pageTitle = 'Order details';

$orderId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    echo '<div class="container" style="padding:3rem 0;">Order not found. <a href="my_orders.php">Back to my orders</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
$stmt->execute([$orderId]);
$lineItems = $stmt->fetchAll();

$trackSteps = ['pending', 'processing', 'shipped', 'delivered'];
$currentStep = array_search($order['status'], $trackSteps);
?>

<div class="container">
  <div class="page-header"><h1>Order #<?php echo (int)$order['id']; ?></h1><p>Placed on <?php echo date('M j, Y g:ia', strtotime($order['created_at'])); ?></p></div>

  <div style="max-width:720px; padding:2rem 0;">
    <?php if ($order['status'] === 'cancelled'): ?>
      <div class="alert alert-error">This order was cancelled.</div>
    <?php else: ?>
      <div class="order-card" style="display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
        <?php foreach ($trackSteps as $i => $step): ?>
          <div style="text-align:center; flex:1;">
            <div style="width:34px;height:34px;border-radius:50%;margin:0 auto 0.4rem;display:flex;align-items:center;justify-content:center;
                        background:<?php echo $i <= $currentStep ? 'var(--accent)' : 'var(--line)'; ?>;
                        color:<?php echo $i <= $currentStep ? '#fff' : 'var(--muted)'; ?>;">
              <i class="fa-solid fa-check"></i>
            </div>
            <span style="font-size:0.8rem; text-transform:capitalize; color:<?php echo $i <= $currentStep ? 'var(--ink)' : 'var(--muted)'; ?>;"><?php echo $step; ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="order-card" style="margin-top:1.25rem;">
      <h3 style="margin-bottom:0.75rem;">Items</h3>
      <?php foreach ($lineItems as $li): ?>
        <div class="order-item-row"><span><?php echo clean($li['name']); ?> × <?php echo (int)$li['quantity']; ?></span><span><?php echo money($li['price'] * $li['quantity']); ?></span></div>
      <?php endforeach; ?>
      <div class="summary-row total"><span>Total</span><span><?php echo money($order['total_amount']); ?></span></div>
    </div>

    <div class="order-card" style="margin-top:1.25rem;">
      <h3 style="margin-bottom:0.5rem;">Shipping address</h3>
      <p style="color:var(--muted);"><?php echo nl2br(clean($order['shipping_address'])); ?></p>
      <h3 style="margin:1rem 0 0.3rem;">Payment method</h3>
      <p style="color:var(--muted);"><?php echo $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Demo Card Payment'; ?></p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
