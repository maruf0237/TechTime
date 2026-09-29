<?php
require_once __DIR__ . '/includes/header.php';
require_login();
$pageTitle = 'Checkout';

$stmt = $pdo->prepare('SELECT c.quantity, p.name, p.price, p.stock
                        FROM cart c JOIN products p ON p.id = c.product_id WHERE c.user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

if (!$items) {
    header('Location: cart.php');
    exit;
}

$subtotal = 0;
foreach ($items as $item) $subtotal += $item['price'] * $item['quantity'];
$shipping = 9.99;
$total = $subtotal + $shipping;
$error = '';

if (isset($_GET['error'])) $error = 'Please fill in your shipping address to continue.';
?>

<div class="container">
  <div class="page-header"><h1>Checkout</h1></div>

  <div class="cart-checkout-grid">
    <div>
      <?php if ($error): ?><div class="alert alert-error"><?php echo clean($error); ?></div><?php endif; ?>

      <form method="post" action="place_order.php" data-validate novalidate>
        <h3 style="margin-bottom:0.75rem;">Shipping address</h3>
        <div class="field">
          <label for="address">Full delivery address</label>
          <textarea id="address" name="address" rows="3" data-rule="required" required placeholder="House, road, area, city, postcode"></textarea>
          <span class="error" id="addressError"></span>
        </div>

        <h3 style="margin:1.5rem 0 0.75rem;">Payment method</h3>
        <div class="payment-options">
          <label><input type="radio" name="payment_method" value="cod" checked> <span>Cash on Delivery</span></label>
          <label><input type="radio" name="payment_method" value="demo_card"> <span>Demo Card Payment (no real charge)</span></label>
        </div>

        <button type="submit" class="btn btn-solid btn-block">Place order — <?php echo money($total); ?></button>
      </form>
    </div>

    <div class="summary-panel">
      <h3 style="margin-bottom:1rem;">Order summary</h3>
      <?php foreach ($items as $item): ?>
        <div class="summary-row"><span><?php echo clean($item['name']); ?> × <?php echo (int)$item['quantity']; ?></span><span><?php echo money($item['price'] * $item['quantity']); ?></span></div>
      <?php endforeach; ?>
      <div class="summary-row"><span>Shipping</span><span><?php echo money($shipping); ?></span></div>
      <div class="summary-row total"><span>Total</span><span><?php echo money($total); ?></span></div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
