<?php
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    echo '<div class="container" style="padding:3rem 0;">Product not found. <a href="index.php">Back to shop</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$pageTitle = $product['name'];
$reviewError = '';

// Submit a review (requires login)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    require_login();
    $rating  = (int)($_POST['rating'] ?? 0);
    $comment = clean($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $reviewError = 'Please select a star rating.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)');
        $stmt->execute([$id, $_SESSION['user_id'], $rating, $comment]);
        header('Location: product.php?id=' . $id . '#reviews');
        exit;
    }
}

// Edit an existing review (must be the review's own author)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_review'])) {
    require_login();
    $reviewId = (int)($_POST['review_id'] ?? 0);
    $rating   = (int)($_POST['rating'] ?? 0);
    $comment  = clean($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $reviewError = 'Please select a star rating.';
    } else {
        $stmt = $pdo->prepare('UPDATE reviews SET rating = ?, comment = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$rating, $comment, $reviewId, $_SESSION['user_id']]);
        header('Location: product.php?id=' . $id . '#reviews');
        exit;
    }
}

// Post a chat / Q&A message (requires login)
$chatError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_chat'])) {
    require_login();
    $message = clean($_POST['message'] ?? '');

    if ($message === '') {
        $chatError = 'Type a message before sending.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO product_chat (product_id, user_id, message) VALUES (?, ?, ?)');
        $stmt->execute([$id, $_SESSION['user_id'], $message]);
        header('Location: product.php?id=' . $id . '&tab=chat#reviews');
        exit;
    }
}

$stmt = $pdo->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS review_count FROM reviews WHERE product_id = ?');
$stmt->execute([$id]);
$ratingSummary = $stmt->fetch();

$stmt = $pdo->prepare('SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC');
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT c.*, u.name AS user_name, u.role AS user_role
                        FROM product_chat c JOIN users u ON u.id = c.user_id
                        WHERE c.product_id = ? ORDER BY c.created_at ASC');
$stmt->execute([$id]);
$chatMessages = $stmt->fetchAll();

$activeTab = ($_GET['tab'] ?? 'reviews') === 'chat' ? 'chat' : 'reviews';
$gallery = product_gallery($pdo, $product);
?>

<div class="container">
  <div class="product-detail">
    <div>
      <div class="product-detail-image">
        <img src="<?php echo $gallery[0]; ?>" alt="<?php echo clean($product['name']); ?>" id="mainProductImage">
      </div>
      <?php if (count($gallery) > 1): ?>
        <div class="product-thumb-strip">
          <?php foreach ($gallery as $i => $imgUrl): ?>
            <button type="button" class="product-thumb-btn <?php echo $i === 0 ? 'active' : ''; ?>" data-img="<?php echo clean($imgUrl); ?>">
              <img src="<?php echo $imgUrl; ?>" alt="<?php echo clean($product['name']); ?> photo <?php echo $i + 1; ?>">
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div>
      <div class="product-cat"><?php echo clean($product['category_name'] ?? 'Uncategorized'); ?></div>
      <h1><?php echo clean($product['name']); ?></h1>
      <?php echo render_stars($ratingSummary['avg_rating'] ?? 0, $ratingSummary['review_count']); ?>
      <div class="product-price"><?php echo money($product['price']); ?></div>
      <p style="color:var(--muted); margin:1rem 0;"><?php echo nl2br(clean($product['description'])); ?></p>

      <?php if ($product['stock'] > 0): ?>
        <form method="post" action="add_to_cart.php">
          <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
          <div class="qty-row">
            <label for="quantity">Qty</label>
            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock']; ?>">
            <span style="color:var(--muted); font-size:0.85rem;"><?php echo (int)$product['stock']; ?> in stock</span>
          </div>
          <button type="submit" class="btn btn-solid"><i class="fa-solid fa-cart-plus"></i> Add to cart</button>
        </form>
      <?php else: ?>
        <div class="alert alert-error" style="max-width:320px;">Currently out of stock.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="reviews-section" id="reviews">
    <div class="review-tabs">
      <button type="button" class="review-tab-btn <?php echo $activeTab === 'reviews' ? 'active' : ''; ?>" data-tab="reviews">
        <i class="fa-solid fa-star"></i> Reviews (<?php echo (int)$ratingSummary['review_count']; ?>)
      </button>
      <button type="button" class="review-tab-btn <?php echo $activeTab === 'chat' ? 'active' : ''; ?>" data-tab="chat">
        <i class="fa-solid fa-comments"></i> Chat / Q&amp;A (<?php echo count($chatMessages); ?>)
      </button>
    </div>

    <!-- ---------- Star reviews panel ---------- -->
    <div class="review-panel" id="panel-reviews" style="<?php echo $activeTab === 'chat' ? 'display:none;' : ''; ?>">
      <?php if (is_logged_in()): ?>
        <?php if ($reviewError): ?><div class="alert alert-error" style="max-width:480px;margin-top:1rem;"><?php echo clean($reviewError); ?></div><?php endif; ?>
        <form method="post" style="max-width:480px; margin:1.25rem 0;">
          <label style="font-weight:600; font-size:0.9rem;">Your rating</label>
          <div class="star-input">
            <i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i>
          </div>
          <input type="hidden" name="rating" id="ratingValue" class="ratingValue" value="0">
          <div class="field">
            <label for="comment">Comment (optional)</label>
            <textarea id="comment" name="comment" rows="3" placeholder="What did you think of this product?"></textarea>
          </div>
          <button type="submit" name="submit_review" class="btn btn-solid btn-sm">Post review</button>
        </form>
      <?php else: ?>
        <p style="margin:1rem 0; color:var(--muted);"><a href="login.php" style="color:var(--accent); font-weight:600;">Log in</a> to leave a star rating and review.</p>
      <?php endif; ?>

      <?php if (!$reviews): ?>
        <p style="color:var(--muted);">No reviews yet — be the first to share your thoughts.</p>
      <?php else: ?>
        <?php foreach ($reviews as $r):
          $isMyReview = is_logged_in() && (int)$r['user_id'] === (int)$_SESSION['user_id'];
        ?>
          <div class="review-item">
            <div class="review-view" id="review-view-<?php echo $r['id']; ?>">
              <?php echo render_stars($r['rating']); ?>
              <?php if ($r['comment']): ?><p style="margin-top:0.4rem;"><?php echo clean($r['comment']); ?></p><?php endif; ?>
              <div class="review-meta">
                <?php echo clean($r['user_name']); ?> · <?php echo date('M j, Y', strtotime($r['created_at'])); ?>
                <?php if ($isMyReview): ?>
                  <button type="button" class="review-edit-toggle" data-target="review-edit-<?php echo $r['id']; ?>">Edit</button>
                <?php endif; ?>
              </div>
            </div>

            <?php if ($isMyReview): ?>
              <form method="post" class="review-edit-form" id="review-edit-<?php echo $r['id']; ?>" style="display:none;">
                <input type="hidden" name="review_id" value="<?php echo $r['id']; ?>">
                <label style="font-weight:600; font-size:0.85rem;">Your rating</label>
                <div class="star-input">
                  <?php for ($s = 1; $s <= 5; $s++): ?>
                    <i class="<?php echo $s <= (int)$r['rating'] ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                  <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" class="ratingValue" value="<?php echo (int)$r['rating']; ?>">
                <div class="field">
                  <textarea name="comment" rows="2"><?php echo clean($r['comment']); ?></textarea>
                </div>
                <button type="submit" name="edit_review" class="btn btn-solid btn-sm">Save changes</button>
                <button type="button" class="btn btn-ghost btn-sm review-edit-cancel" data-target="review-edit-<?php echo $r['id']; ?>">Cancel</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- ---------- Chat / Q&A panel ---------- -->
    <div class="review-panel" id="panel-chat" style="<?php echo $activeTab === 'reviews' ? 'display:none;' : ''; ?>">
      <p style="color:var(--muted); font-size:0.88rem; margin:1rem 0 1.25rem;">Ask the seller or other buyers a question about this product — everyone can see the thread.</p>

      <div class="chat-thread">
        <?php if (!$chatMessages): ?>
          <p style="color:var(--muted);">No messages yet — ask the first question.</p>
        <?php else: foreach ($chatMessages as $m):
          $isMine  = is_logged_in() && (int)$m['user_id'] === (int)$_SESSION['user_id'];
          $isAdmin = $m['user_role'] === 'admin';
        ?>
          <div class="chat-bubble-row <?php echo $isMine ? 'mine' : ''; ?>">
            <div class="chat-bubble <?php echo $isAdmin ? 'admin' : ''; ?>">
              <div class="chat-bubble-meta">
                <strong><?php echo clean($m['user_name']); ?></strong>
                <?php if ($isAdmin): ?><span class="chat-admin-badge">Seller</span><?php endif; ?>
                <span class="chat-time"><?php echo date('M j, g:ia', strtotime($m['created_at'])); ?></span>
              </div>
              <div class="chat-bubble-text"><?php echo nl2br(clean($m['message'])); ?></div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <?php if (is_logged_in()): ?>
        <?php if ($chatError): ?><div class="alert alert-error" style="max-width:520px;margin-top:0.75rem;"><?php echo clean($chatError); ?></div><?php endif; ?>
        <form method="post" class="chat-input-row">
          <textarea name="message" rows="2" placeholder="Ask a question about this product..." required></textarea>
          <button type="submit" name="submit_chat" class="btn btn-solid btn-sm"><i class="fa-solid fa-paper-plane"></i> Send</button>
        </form>
      <?php else: ?>
        <p style="margin:1rem 0; color:var(--muted);"><a href="login.php" style="color:var(--accent); font-weight:600;">Log in</a> to ask a question.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
