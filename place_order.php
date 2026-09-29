<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$address = clean($_POST['address'] ?? '');
$paymentMethod = ($_POST['payment_method'] ?? 'cod') === 'demo_card' ? 'demo_card' : 'cod';

if ($address === '') {
    header('Location: checkout.php?error=1');
    exit;
}

$stmt = $pdo->prepare('SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.price, p.stock
                        FROM cart c JOIN products p ON p.id = c.product_id WHERE c.user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

if (!$items) {
    header('Location: cart.php');
    exit;
}

$subtotal = 0;
foreach ($items as $item) $subtotal += $item['price'] * min($item['quantity'], $item['stock']);
$total = $subtotal + 9.99;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, payment_method, status, shipping_address) VALUES (?, ?, ?, "pending", ?)');
    $stmt->execute([$_SESSION['user_id'], $total, $paymentMethod, $address]);
    $orderId = $pdo->lastInsertId();

    foreach ($items as $item) {
        $qty = min((int)$item['quantity'], (int)$item['stock']);
        if ($qty <= 0) continue;

        $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
        $stmt->execute([$orderId, $item['product_id'], $qty, $item['price']]);

        $stmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
        $stmt->execute([$qty, $item['product_id']]);
    }

    $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);

    $pdo->commit();
    header('Location: order_details.php?id=' . $orderId);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: checkout.php?error=1');
    exit;
}
