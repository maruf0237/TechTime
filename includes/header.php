<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

$cartCount = 0;
$cartSubtotal = 0;
$cartItems = [];
if (is_logged_in()) {
    $stmt = $pdo->prepare('SELECT c.quantity, p.id AS product_id, p.name, p.price, p.image, cat.name AS category_name
                            FROM cart c JOIN products p ON p.id = c.product_id
                            LEFT JOIN categories cat ON cat.id = p.category_id
                            WHERE c.user_id = ? ORDER BY c.added_at DESC');
    $stmt->execute([$_SESSION['user_id']]);
    $allCart = $stmt->fetchAll();
    foreach ($allCart as $ci) {
        $cartCount    += (int)$ci['quantity'];
        $cartSubtotal += $ci['price'] * $ci['quantity'];
    }
    $cartItems = array_slice($allCart, 0, 4);
    $cartExtra = max(0, count($allCart) - 4);
}
$flashSuccess = flash_get('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo isset($pageTitle) ? clean($pageTitle) . ' — Tech Time' : 'Tech Time — Tech that keeps up with you'; ?></title>
<link rel="icon" href="data:,">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
</head>
<body<?php if ($flashSuccess): ?> data-toast="<?php echo clean($flashSuccess); ?>"<?php endif; ?>>

<header class="site-header">
  <div class="site-header-inner">
    <a href="<?php echo asset('index.php'); ?>" class="logo"><i class="fa-solid fa-bolt"></i> Tech <span class="logo-accent">Time</span></a>

    <div class="site-search">
      <form method="get" action="<?php echo asset('index.php'); ?>">
        <input type="search" name="q" placeholder="Search laptops, phones, monitors…" value="<?php echo clean($_GET['q'] ?? ''); ?>">
        <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>
    </div>

    <div class="nav-actions">
      <button type="button" class="theme-toggle" aria-label="Toggle dark and light theme"><i class="fa-solid fa-moon"></i></button>

      <?php if (is_logged_in()): ?>
        <div class="cart-menu">
          <button type="button" class="icon-btn cart-menu-btn" aria-label="Open cart">
            <i class="fa-solid fa-cart-shopping"></i>
            <?php if ($cartCount > 0): ?><span class="cart-badge"><?php echo $cartCount; ?></span><?php endif; ?>
          </button>
          <div class="cart-dropdown">
            <?php if (!$cartItems): ?>
              <div class="cart-dd-head">Your cart</div>
              <div class="cart-dd-empty">Your cart is empty.</div>
            <?php else: ?>
              <div class="cart-dd-head">Your cart (<?php echo $cartCount; ?> item<?php echo $cartCount === 1 ? '' : 's'; ?>)</div>
              <?php foreach ($cartItems as $ci): ?>
                <a href="<?php echo asset('product.php?id=' . $ci['product_id']); ?>" class="cart-dd-item">
                  <span class="cart-dd-thumb"><img src="<?php echo product_image_path($ci); ?>" alt=""></span>
                  <span style="min-width:0; flex:1;">
                    <span class="cart-dd-name" style="display:block;"><?php echo clean($ci['name']); ?></span>
                    <span class="cart-dd-meta"><?php echo (int)$ci['quantity']; ?> × <?php echo money($ci['price']); ?></span>
                  </span>
                  <strong><?php echo money($ci['price'] * $ci['quantity']); ?></strong>
                </a>
              <?php endforeach; ?>
              <?php if ($cartExtra > 0): ?><div class="cart-dd-more">+ <?php echo $cartExtra; ?> more item<?php echo $cartExtra === 1 ? '' : 's'; ?> in your cart</div><?php endif; ?>
              <div class="cart-dd-foot">
                <div class="cart-dd-sub"><span>Subtotal</span><span><?php echo money($cartSubtotal); ?></span></div>
                <a href="<?php echo asset('cart.php'); ?>" class="btn btn-solid btn-block">View cart</a>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="user-menu">
          <button type="button" class="user-menu-btn">
            <span class="user-avatar"><?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?></span>
          </button>
          <div class="user-dropdown">
            <a href="<?php echo asset('my_orders.php'); ?>"><i class="fa-solid fa-box"></i> My orders</a>
            <?php if (is_admin()): ?>
              <a href="<?php echo asset('admin/dashboard.php'); ?>"><i class="fa-solid fa-gauge"></i> Admin panel</a>
            <?php endif; ?>
            <a href="<?php echo asset('logout.php'); ?>"><i class="fa-solid fa-right-from-bracket"></i> Log out</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?php echo asset('login.php'); ?>" class="icon-btn" aria-label="Cart"><i class="fa-solid fa-cart-shopping"></i></a>
        <a href="<?php echo asset('login.php'); ?>" class="btn btn-ghost btn-sm nav-auth">Log in</a>
        <a href="<?php echo asset('register.php'); ?>" class="btn btn-solid btn-sm nav-auth">Sign up</a>
      <?php endif; ?>
    </div>
  </div>
</header>
