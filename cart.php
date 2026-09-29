<?php
require_once __DIR__ . '/includes/header.php';
require_login();
$pageTitle = 'Your cart';

$stmt = $pdo->prepare('SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.stock, p.image, cat.name AS category_name
                        FROM cart c JOIN products p ON p.id = c.product_id
                        LEFT JOIN categories cat ON cat.id = p.category_id
                        WHERE c.user_id = ? ORDER BY c.added_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

$subtotal = 0;
foreach ($items as $item) $subtotal += $item['price'] * $item['quantity'];
$shipping = $subtotal > 0 ? 9.99 : 0;
$total = $subtotal + $shipping;
?>

<div class="container">
  <div class="page-header"><h1>Your cart</h1></div>

  <?php if (!$items): ?>
    <div class="empty-state">
      <i class="fa-solid fa-cart-shopping" style="font-size:2rem;color:var(--muted);"></i>
      <p style="margin:0.75rem 0 1.25rem;">Your cart is empty.</p>
      <a href="index.php" class="btn btn-solid">Start shopping</a>
    </div>
  <?php else: ?>
    <div class="cart-checkout-grid">
      <table class="cart-table">
        <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td>
                <div class="cart-prod">
                  <div class="cart-thumb"><img src="<?php echo product_image_path($item); ?>" alt="<?php echo clean($item['name']); ?>"></div>
                  <a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo clean($item['name']); ?></a>
                </div>
              </td>
              <td><?php echo money($item['price']); ?></td>
              <td>
                <form class="qty-form" method="post" action="update_cart.php">
                  <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                  <input type="number" name="quantity" value="<?php echo (int)$item['quantity']; ?>" min="1" max="<?php echo (int)$item['stock']; ?>">
                </form>
              </td>
              <td><?php echo money($item['price'] * $item['quantity']); ?></td>
              <td><a href="remove_from_cart.php?cart_id=<?php echo $item['cart_id']; ?>" class="remove-link">Remove</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="summary-panel">
        <h3 style="margin-bottom:1rem;">Order summary</h3>
        <div class="summary-row"><span>Subtotal</span><span><?php echo money($subtotal); ?></span></div>
        <div class="summary-row"><span>Shipping</span><span><?php echo money($shipping); ?></span></div>
        <div class="summary-row total"><span>Total</span><span><?php echo money($total); ?></span></div>
        <a href="checkout.php" class="btn btn-solid btn-block" style="margin-top:1rem;">Proceed to checkout</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
