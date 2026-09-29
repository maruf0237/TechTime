<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$cartId   = (int)($_POST['cart_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

$stmt = $pdo->prepare('SELECT c.id, p.stock FROM cart c JOIN products p ON p.id = c.product_id WHERE c.id = ? AND c.user_id = ?');
$stmt->execute([$cartId, $_SESSION['user_id']]);
$row = $stmt->fetch();

if ($row) {
    $quantity = min($quantity, (int)$row['stock']);
    $stmt = $pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
    $stmt->execute([$quantity, $cartId]);
}

header('Location: cart.php');
exit;
