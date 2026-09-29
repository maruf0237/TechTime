<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$product = ['id' => 0, 'name' => '', 'description' => '', 'price' => '', 'stock' => '', 'category_id' => '', 'image' => null];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) $product = $found;
}
$pageTitle = $id ? 'Edit product' : 'Add product';
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['add_gallery']) && !isset($_POST['delete_gallery_id'])) {
    $name        = clean($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $categoryId  = (int)($_POST['category_id'] ?? 0) ?: null;
    $imageName   = $product['image'];

    if ($name === '') $errors[] = 'Product name is required.';
    if ($price <= 0)  $errors[] = 'Price must be greater than 0.';
    if ($stock < 0)   $errors[] = 'Stock cannot be negative.';

    // ---- Photo upload (JPG/PNG/WEBP/GIF, up to 3MB) ----
    if (!empty($_FILES['photo']['name'])) {
        $file = $_FILES['photo'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'The photo failed to upload — please try again.';
        } elseif ($file['size'] > 3 * 1024 * 1024) {
            $errors[] = 'Photo must be 3MB or smaller.';
        } else {
            $mime = mime_content_type($file['tmp_name']);
            if (!isset($allowed[$mime])) {
                $errors[] = 'Photo must be a JPG, PNG, WEBP or GIF file.';
            } else {
                $newName = 'product_' . uniqid() . '.' . $allowed[$mime];
                $destDir = __DIR__ . '/../uploads/products/';
                if (!is_dir($destDir)) @mkdir($destDir, 0775, true);
                if (move_uploaded_file($file['tmp_name'], $destDir . $newName)) {
                    if ($imageName && is_file($destDir . $imageName)) @unlink($destDir . $imageName);
                    $imageName = $newName;
                } else {
                    $errors[] = 'Could not save the uploaded photo — check the uploads/products folder is writable.';
                }
            }
        }
    }

    if (!$errors) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, image=? WHERE id=?');
            $stmt->execute([$name, $description, $price, $stock, $categoryId, $imageName, $id]);
            flash_set('success', 'Product updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (name, description, price, stock, category_id, image) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$name, $description, $price, $stock, $categoryId, $imageName]);
            flash_set('success', 'Product added.');
        }
        header('Location: ' . asset('admin/products.php'));
        exit;
    }
    $product = array_merge($product, ['name' => $name, 'description' => $description, 'price' => $price, 'stock' => $stock, 'category_id' => $categoryId, 'image' => $imageName]);
}

// ---- Delete one gallery photo (separate small form, own POST flag) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_gallery_id']) && $id) {
    $gid = (int)$_POST['delete_gallery_id'];
    $stmt = $pdo->prepare('SELECT image FROM product_images WHERE id = ? AND product_id = ?');
    $stmt->execute([$gid, $id]);
    $gimg = $stmt->fetchColumn();
    if ($gimg) {
        $pdo->prepare('DELETE FROM product_images WHERE id = ?')->execute([$gid]);
        if (is_file(__DIR__ . '/../uploads/products/' . $gimg)) @unlink(__DIR__ . '/../uploads/products/' . $gimg);
    }
    header('Location: ' . asset('admin/product_form.php?id=' . $id));
    exit;
}

// ---- Add extra gallery photos (separate upload field, only once the product exists) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_gallery']) && $id && !empty($_FILES['gallery_photos']['name'][0])) {
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $destDir = __DIR__ . '/../uploads/products/';
    if (!is_dir($destDir)) @mkdir($destDir, 0775, true);
    $count = count($_FILES['gallery_photos']['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['gallery_photos']['error'][$i] !== UPLOAD_ERR_OK) continue;
        if ($_FILES['gallery_photos']['size'][$i] > 3 * 1024 * 1024) continue;
        $mime = mime_content_type($_FILES['gallery_photos']['tmp_name'][$i]);
        if (!isset($allowed[$mime])) continue;
        $newName = 'product_' . uniqid() . '_' . $i . '.' . $allowed[$mime];
        if (move_uploaded_file($_FILES['gallery_photos']['tmp_name'][$i], $destDir . $newName)) {
            $pdo->prepare('INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)')->execute([$id, $newName, $i]);
        }
    }
    flash_set('success', 'Gallery photos added.');
    header('Location: ' . asset('admin/product_form.php?id=' . $id));
    exit;
}

$galleryPhotos = [];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$id]);
    $galleryPhotos = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="panel" style="max-width:640px;">
  <?php if ($errors): ?><div class="alert alert-error"><?php echo implode('<br>', array_map('clean', $errors)); ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="field">
      <label>Current photo</label>
      <img src="<?php echo product_image_path($product); ?>" style="width:90px;height:90px;object-fit:contain;background:var(--surface-2);border:1px solid var(--line);border-radius:var(--radius-sm);padding:8px;">
    </div>
    <div class="field">
      <label for="photo">Product photo (JPG / PNG / WEBP / GIF, up to 3MB)</label>
      <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
    </div>
    <div class="field">
      <label for="name">Product name</label>
      <input type="text" id="name" name="name" value="<?php echo clean($product['name']); ?>" required>
    </div>
    <div class="field">
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="4"><?php echo clean($product['description']); ?></textarea>
    </div>
    <div class="field">
      <label for="category_id">Category</label>
      <select id="category_id" name="category_id">
        <option value="">Uncategorized</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?php echo $c['id']; ?>" <?php echo (int)$product['category_id'] === (int)$c['id'] ? 'selected' : ''; ?>><?php echo clean($c['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <span style="font-size:0.8rem; color:var(--muted); margin-top:0.4rem; display:inline-block;">
        Don't see the right category? <a href="<?php echo asset('admin/categories.php'); ?>" style="color:var(--accent); font-weight:600;">Add a new one here</a> first, then come back.
      </span>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
      <div class="field">
        <label for="price">Price ($)</label>
        <input type="number" id="price" name="price" step="0.01" min="0.01" value="<?php echo clean($product['price']); ?>" required>
      </div>
      <div class="field">
        <label for="stock">Stock</label>
        <input type="number" id="stock" name="stock" min="0" value="<?php echo clean($product['stock']); ?>" required>
      </div>
    </div>
    <div style="display:flex; gap:0.6rem;">
      <button type="submit" class="btn btn-solid"><?php echo $id ? 'Save changes' : 'Add product'; ?></button>
      <a href="<?php echo asset('admin/products.php'); ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

<?php if ($id): ?>
<div class="panel" style="max-width:640px;">
  <div class="panel-head"><h3 style="margin:0;">Extra gallery photos</h3></div>
  <p style="color:var(--muted); font-size:0.85rem; margin-top:-0.5rem;">These show as extra thumbnails on the product page, alongside the main photo above.</p>

  <?php if ($galleryPhotos): ?>
    <div style="display:flex; flex-wrap:wrap; gap:0.8rem; margin:1rem 0;">
      <?php foreach ($galleryPhotos as $gp): ?>
        <div style="text-align:center;">
          <img src="<?php echo asset('uploads/products/' . rawurlencode($gp['image'])); ?>" style="width:80px;height:80px;object-fit:contain;background:var(--surface-2);border:1px solid var(--line);border-radius:var(--radius-sm);padding:6px;">
          <form method="post" onsubmit="return confirm('Remove this photo?');" style="margin-top:0.3rem;">
            <input type="hidden" name="delete_gallery_id" value="<?php echo $gp['id']; ?>">
            <button type="submit" class="icon-action danger" style="width:auto;padding:0.2rem 0.5rem;font-size:0.75rem;"><i class="fa-solid fa-trash"></i></button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="color:var(--muted);">No extra photos yet.</p>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" style="margin-top:0.5rem;">
    <input type="hidden" name="add_gallery" value="1">
    <div class="field">
      <label for="gallery_photos">Add more photos (select several at once, JPG/PNG/WEBP/GIF, up to 3MB each)</label>
      <input type="file" id="gallery_photos" name="gallery_photos[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
    </div>
    <button type="submit" class="btn btn-ghost btn-sm">Upload photos</button>
  </form>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
