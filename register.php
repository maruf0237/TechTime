<?php
require_once __DIR__ . '/includes/header.php';
$pageTitle = 'Create account';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean($_POST['name'] ?? '');
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (strlen($name) < 2) $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "customer")');
        $stmt->execute([$name, $email, $hash]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['name']    = $name;
        $_SESSION['role']    = 'customer';
        header('Location: index.php');
        exit;
    }
}
?>
<div class="form-page">
  <h1>Create your account</h1>
  <p class="sub">Join Tech Time to track orders and save your cart.</p>

  <?php if ($errors): ?>
    <div class="alert alert-error"><?php echo implode('<br>', array_map('clean', $errors)); ?></div>
  <?php endif; ?>

  <form method="post" data-validate novalidate>
    <div class="field">
      <label for="name">Full name</label>
      <input type="text" id="name" name="name" data-rule="min2" value="<?php echo clean($_POST['name'] ?? ''); ?>" required>
      <span class="error" id="nameError"></span>
    </div>
    <div class="field">
      <label for="email">Email address</label>
      <input type="email" id="email" name="email" data-rule="email" value="<?php echo clean($_POST['email'] ?? ''); ?>" required>
      <span class="error" id="emailError"></span>
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" data-rule="password" required>
      <span class="error" id="passwordError"></span>
    </div>
    <div class="field">
      <label for="confirm">Confirm password</label>
      <input type="password" id="confirm" name="confirm" data-rule="confirm" data-matches="password" required>
      <span class="error" id="confirmError"></span>
    </div>
    <button type="submit" class="btn btn-solid btn-block">Create account</button>
  </form>
  <div class="form-footer-link">Already have an account? <a href="login.php" style="color:var(--accent); font-weight:600;">Log in</a></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
