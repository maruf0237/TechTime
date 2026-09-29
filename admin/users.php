<?php
$pageTitle = 'Users';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($userId === (int)$_SESSION['user_id']) {
        flash_set('success', "You can't change your own account here.");
    } elseif ($action === 'promote') {
        $pdo->prepare('UPDATE users SET role = "admin" WHERE id = ?')->execute([$userId]);
        flash_set('success', 'User promoted to admin.');
    } elseif ($action === 'demote') {
        $pdo->prepare('UPDATE users SET role = "customer" WHERE id = ?')->execute([$userId]);
        flash_set('success', 'Admin demoted to customer.');
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
        flash_set('success', 'User deleted.');
    }
    header('Location: ' . asset('admin/users.php'));
    exit;
}

$users = $pdo->query('SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count
                       FROM users u ORDER BY u.created_at DESC')->fetchAll();
?>

<div class="panel">
  <div class="panel-head"><h3 style="margin:0;">All users (<?php echo count($users); ?>)</h3></div>
  <table class="data-table">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Orders</th><th>Joined</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?php echo clean($u['name']); ?><?php if ((int)$u['id'] === (int)$_SESSION['user_id']): ?> <span style="color:var(--muted); font-size:0.78rem;">(you)</span><?php endif; ?></td>
          <td><?php echo clean($u['email']); ?></td>
          <td><span class="role-pill role-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
          <td><?php echo (int)$u['order_count']; ?></td>
          <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
          <td style="display:flex; gap:0.4rem;">
            <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
              <?php if ($u['role'] === 'customer'): ?>
                <form method="post"><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="promote">
                  <button type="submit" class="icon-action" title="Promote to admin"><i class="fa-solid fa-arrow-up"></i></button>
                </form>
              <?php else: ?>
                <form method="post"><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="demote">
                  <button type="submit" class="icon-action" title="Demote to customer"><i class="fa-solid fa-arrow-down"></i></button>
                </form>
              <?php endif; ?>
              <form method="post" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="delete">
                <button type="submit" class="icon-action danger" title="Delete user"><i class="fa-solid fa-trash"></i></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
