<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$cartId = (int)($_GET['cart_id'] ?? 0);
$stmt = $pdo->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
$stmt->execute([$cartId, $_SESSION['user_id']]);

header('Location: cart.php');
exit;
