<?php
$pageTitle = 'Products';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    if ($img && is_file(__DIR__ . '/../uploads/products/' . $img)) {
        @unlink(__DIR__ . '/../uploads/products/' . $img);
    }
    flash_set('success', 'Product deleted.');
    header('Location: ' . asset('admin/products.php'));
    exit;
}

$products = $pdo->query('SELECT p.*, c.name AS category_name,
                            (SELECT COALESCE(SUM(oi.quantity),0) FROM order_items oi WHERE oi.product_id = p.id) AS units_sold
                          FROM products p LEFT JOIN categories c ON c.id = p.category_id
                          ORDER BY p.created_at DESC')->fetchAll();
?>

<div class="panel">
  <div class="panel-head">
    <h3 style="margin:0;">All products (<?php echo count($products); ?>)</h3>
    <a href="<?php echo asset('admin/product_form.php'); ?>" class="btn btn-solid btn-sm"><i class="fa-solid fa-plus"></i> Add product</a>
  </div>

  <table class="data-table">
    <thead><tr><th></th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Sold</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><img src="<?php echo product_image_path($p); ?>" class="table-thumb" alt=""></td>
          <td><?php echo clean($p['name']); ?></td>
          <td><?php echo clean($p['category_name'] ?? '—'); ?></td>
          <td><?php echo money($p['price']); ?></td>
          <td><?php echo (int)$p['stock']; ?></td>
          <td><?php echo (int)$p['units_sold']; ?></td>
          <td style="display:flex; gap:0.4rem;">
            <a href="<?php echo asset('admin/product_form.php?id=' . $p['id']); ?>" class="icon-action" title="Edit"><i class="fa-solid fa-pen"></i></a>
            <form method="post" onsubmit="return confirm('Delete this product? This cannot be undone.');">
              <input type="hidden" name="delete_id" value="<?php echo $p['id']; ?>">
              <button type="submit" class="icon-action danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
