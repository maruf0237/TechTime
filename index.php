<?php
require_once __DIR__ . '/includes/header.php';
$pageTitle = 'Shop';

$q          = clean($_GET['q'] ?? '');
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$minPrice   = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$maxPrice   = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$sql    = 'SELECT p.*, c.name AS category_name,
             (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) AS avg_rating,
             (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) AS review_count
           FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE 1=1';
$params = [];

if ($q !== '') {
    $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($categoryId > 0) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $categoryId;
}
if ($minPrice !== null) {
    $sql .= ' AND p.price >= ?';
    $params[] = $minPrice;
}
if ($maxPrice !== null) {
    $sql .= ' AND p.price <= ?';
    $params[] = $maxPrice;
}
$sql .= ' ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<?php if ($q === '' && $categoryId === 0 && $minPrice === null): ?>
<section class="hero">
  <div class="hero-inner">
    <div>
      <span class="hero-badge">New arrivals every week</span>
      <h1>Tech that keeps up with you.</h1>
      <p>Laptops, phones, monitors, keyboards and headphones — sourced, reviewed, and shipped fast.</p>
      <a href="#shop" class="btn btn-solid">Browse products</a>
    </div>
    <div class="hero-visual"><img src="<?php echo asset('assets/images/hero.svg'); ?>" alt="Laptop, monitor, phone and headphones"></div>
  </div>
</section>
<?php endif; ?>

<div class="container" id="shop">
  <div class="shop-layout">
    <aside class="filters">
      <form method="get">
        <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?php echo clean($q); ?>"><?php endif; ?>

        <h3>Category</h3>
        <div class="filter-group">
          <label><input type="radio" name="category" value="0" onchange="this.form.submit()" <?php echo $categoryId === 0 ? 'checked' : ''; ?>> All products</label>
          <?php foreach ($categories as $cat): ?>
            <label>
              <input type="radio" name="category" value="<?php echo $cat['id']; ?>" onchange="this.form.submit()" <?php echo $categoryId === (int)$cat['id'] ? 'checked' : ''; ?>>
              <?php echo clean($cat['name']); ?>
            </label>
          <?php endforeach; ?>
        </div>

        <h3>Price range</h3>
        <div class="filter-group">
          <div class="price-range">
            <input type="number" name="min_price" placeholder="Min" min="0" value="<?php echo clean($_GET['min_price'] ?? ''); ?>">
            <input type="number" name="max_price" placeholder="Max" min="0" value="<?php echo clean($_GET['max_price'] ?? ''); ?>">
          </div>
          <button type="submit" class="btn btn-ghost btn-sm" style="margin-top:0.75rem;width:100%;">Apply</button>
        </div>

        <?php if ($categoryId || $minPrice !== null || $maxPrice !== null || $q !== ''): ?>
          <a href="index.php" class="btn btn-ghost btn-sm" style="width:100%;">Clear filters</a>
        <?php endif; ?>
      </form>
    </aside>

    <div>
      <div class="results-header">
        <h2><?php echo $q !== '' ? 'Results for "' . clean($q) . '"' : 'All products'; ?> (<?php echo count($products); ?>)</h2>
      </div>

      <?php if (!$products): ?>
        <div class="empty-state">
          <i class="fa-solid fa-box-open" style="font-size:2rem;color:var(--muted);"></i>
          <p style="margin-top:0.75rem;">No products match your filters.</p>
        </div>
      <?php else: ?>
        <div class="product-grid">
          <?php foreach ($products as $p): ?>
            <a href="product.php?id=<?php echo $p['id']; ?>" class="product-card">
              <div class="product-thumb"><img src="<?php echo product_image_path($p); ?>" alt="<?php echo clean($p['name']); ?>" loading="lazy"></div>
              <div class="product-body">
                <div class="product-cat"><?php echo clean($p['category_name'] ?? ''); ?></div>
                <div class="product-name"><?php echo clean($p['name']); ?></div>
                <?php echo render_stars($p['avg_rating'] ?? 0, $p['review_count']); ?>
                <div class="product-price"><?php echo money($p['price']); ?></div>
                <?php if ($p['stock'] <= 5 && $p['stock'] > 0): ?>
                  <div class="stock-warning">Only <?php echo (int)$p['stock']; ?> left</div>
                <?php elseif ($p['stock'] == 0): ?>
                  <div class="stock-warning">Out of stock</div>
                <?php endif; ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
