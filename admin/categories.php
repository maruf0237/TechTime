<?php
$pageTitle = 'Categories';
require_once __DIR__ . '/includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = clean(trim($_POST['name'] ?? ''));
    if ($name === '') {
        $error = 'Category name is required.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE name = ?');
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $error = 'A category with that name already exists.';
        } else {
            $pdo->prepare('INSERT INTO categories (name) VALUES (?)')->execute([$name]);
            flash_set('success', 'Category "' . $name . '" added.');
            header('Location: ' . asset('admin/categories.php'));
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_category'])) {
    $catId = (int)$_POST['category_id'];
    $name  = clean(trim($_POST['name'] ?? ''));
    if ($name !== '') {
        $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?')->execute([$name, $catId]);
        flash_set('success', 'Category renamed.');
    }
    header('Location: ' . asset('admin/categories.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $catId = (int)$_POST['category_id'];
    // products.category_id has ON DELETE SET NULL, so this is safe —
    // products in this category just become "Uncategorized", nothing is lost.
    $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$catId]);
    flash_set('success', 'Category deleted.');
    header('Location: ' . asset('admin/categories.php'));
    exit;
}

$categories = $pdo->query('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
                            FROM categories c ORDER BY c.name')->fetchAll();
?>

<div class="panel" style="max-width:560px;">
  <div class="panel-head"><h3 style="margin:0;">Add a new category</h3></div>
  <?php if ($error): ?><div class="alert alert-error"><?php echo clean($error); ?></div><?php endif; ?>
  <form method="post" style="display:flex; gap:0.6rem; align-items:flex-end;">
    <div class="field" style="flex:1; margin-bottom:0;">
      <label for="name">Category name</label>
      <input type="text" id="name" name="name" placeholder="e.g. Smartwatches" required>
    </div>
    <button type="submit" name="add_category" value="1" class="btn btn-solid">Add category</button>
  </form>
</div>

<div class="panel" style="max-width:560px;">
  <div class="panel-head"><h3 style="margin:0;">All categories (<?php echo count($categories); ?>)</h3></div>
  <?php if (!$categories): ?>
    <p style="color:var(--muted);">No categories yet — add one above.</p>
  <?php else: ?>
    <table class="data-table">
      <thead><tr><th>Name</th><th>Products</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($categories as $c): ?>
          <tr>
            <td>
              <form method="post" style="display:flex; gap:0.4rem;">
                <input type="hidden" name="category_id" value="<?php echo $c['id']; ?>">
                <input type="text" name="name" value="<?php echo clean($c['name']); ?>" style="padding:0.4rem 0.6rem; border-radius:var(--radius-sm); border:1px solid var(--line); background:var(--surface); width:170px;">
                <button type="submit" name="rename_category" value="1" class="btn btn-ghost btn-sm">Save</button>
              </form>
            </td>
            <td><?php echo (int)$c['product_count']; ?></td>
            <td>
              <form method="post" onsubmit="return confirm('Delete this category? Its products will become Uncategorized, not deleted.');">
                <input type="hidden" name="category_id" value="<?php echo $c['id']; ?>">
                <button type="submit" name="delete_category" value="1" class="icon-action danger" title="Delete category"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
