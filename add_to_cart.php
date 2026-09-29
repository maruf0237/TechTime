<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$quantity  = max(1, (int)($_POST['quantity'] ?? 1));

$stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if ($product) {
    $quantity = min($quantity, (int)$product['stock']);

    $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$_SESSION['user_id'], $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = min($existing['quantity'] + $quantity, (int)$product['stock']);
        $stmt = $pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
        $stmt->execute([$newQty, $existing['id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $productId, $quantity]);
    }
}

header('Location: cart.php');
exit;
