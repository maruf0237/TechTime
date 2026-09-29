<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <a href="<?php echo asset('index.php'); ?>" class="footer-logo"><i class="fa-solid fa-bolt"></i> Tech <span class="logo-accent">Time</span></a>
      <p>Laptops, phones, monitors and accessories — picked for people who actually use their gear.</p>
    </div>
    <div>
      <h4>Shop</h4>
      <ul>
        <li><a href="<?php echo asset('index.php'); ?>">All products</a></li>
        <li><a href="<?php echo asset('my_orders.php'); ?>">Track an order</a></li>
      </ul>
    </div>
    <div>
      <h4>Account</h4>
      <ul>
        <?php if (is_logged_in()): ?>
          <li><a href="<?php echo asset('my_orders.php'); ?>">My orders</a></li>
          <li><a href="<?php echo asset('logout.php'); ?>">Log out</a></li>
        <?php else: ?>
          <li><a href="<?php echo asset('login.php'); ?>">Log in</a></li>
          <li><a href="<?php echo asset('register.php'); ?>">Create account</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="container footer-bottom">
    <span>© <?php echo date('Y'); ?> Tech Time. All rights reserved.</span>
  </div>
</footer>

<div id="toast-stack"></div>
<script src="<?php echo asset('assets/js/app.js'); ?>"></script>
</body>
</html>
