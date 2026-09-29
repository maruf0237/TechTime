<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
$error = '';

if (is_admin()) {
    header('Location: ' . asset('admin/dashboard.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, name, password, role FROM users WHERE email = ? AND role = "admin"');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];
        header('Location: ' . asset('admin/dashboard.php'));
        exit;
    }
    $error = 'Incorrect email or password, or this account is not an admin.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin login — Tech Time</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
</head>
<body>
<div class="form-page">
  <div class="logo" style="margin-bottom:1.5rem;"><i class="fa-solid fa-bolt"></i> Tech Time</div>
  <h1>Admin panel</h1>
  <p class="sub">Sign in to manage products, orders and users.</p>

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
  <div class="form-footer-link">Demo login: admin@techtime.com / admin123</div>
  <div class="form-footer-link"><a href="<?php echo asset('index.php'); ?>" style="color:var(--accent); font-weight:600;">← Back to store</a></div>
</div>
<script src="<?php echo asset('assets/js/app.js'); ?>"></script>
</body>
</html>
