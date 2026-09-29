<?php
require_once __DIR__ . '/includes/header.php';
$pageTitle = 'Log in';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];
        header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
        exit;
    } else {
        $error = 'Incorrect email or password.';
    }
}
?>
<div class="form-page">
  <h1>Welcome back</h1>
  <p class="sub">Log in to check out and track your orders.</p>

  <?php if ($error): ?><div class="alert alert-error"><?php echo clean($error); ?></div><?php endif; ?>

  <form method="post" data-validate novalidate>
    <div class="field">
      <label for="email">Email address</label>
      <input type="email" id="email" name="email" data-rule="email" value="<?php echo clean($_POST['email'] ?? ''); ?>" required>
      <span class="error" id="emailError"></span>
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" data-rule="required" required>
      <span class="error" id="passwordError"></span>
    </div>
    <button type="submit" class="btn btn-solid btn-block">Log in</button>
  </form>
  <div class="form-footer-link">New to Tech Time? <a href="register.php" style="color:var(--accent); font-weight:600;">Create an account</a></div>
  <div class="form-footer-link">Admin demo login: admin@techtime.com / admin123</div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
