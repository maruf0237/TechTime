<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
$flashSuccess = flash_get('success');
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo isset($pageTitle) ? clean($pageTitle) . ' — Tech Time Admin' : 'Tech Time Admin'; ?></title>
<link rel="icon" href="data:,">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
</head>
<body<?php if ($flashSuccess): ?> data-toast="<?php echo clean($flashSuccess); ?>"<?php endif; ?>>

<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="logo"><i class="fa-solid fa-bolt"></i> Tech Time</div>
    <nav class="admin-nav">
      <a href="<?php echo asset('admin/dashboard.php'); ?>" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
      <a href="<?php echo asset('admin/products.php'); ?>" class="<?php echo in_array($currentPage, ['products.php','product_form.php']) ? 'active' : ''; ?>"><i class="fa-solid fa-box-open"></i> Products</a>
      <a href="<?php echo asset('admin/categories.php'); ?>" class="<?php echo $currentPage === 'categories.php' ? 'active' : ''; ?>"><i class="fa-solid fa-tags"></i> Categories</a>
      <a href="<?php echo asset('admin/orders.php'); ?>" class="<?php echo $currentPage === 'orders.php' ? 'active' : ''; ?>"><i class="fa-solid fa-truck"></i> Orders</a>
      <a href="<?php echo asset('admin/users.php'); ?>" class="<?php echo $currentPage === 'users.php' ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i> Users</a>
      <a href="<?php echo asset('index.php'); ?>"><i class="fa-solid fa-arrow-left"></i> Back to store</a>
      <a href="<?php echo asset('admin/logout.php'); ?>"><i class="fa-solid fa-right-from-bracket"></i> Log out</a>
    </nav>
  </aside>

  <main class="admin-main">
    <div class="admin-topbar">
      <h1 style="margin:0;"><?php echo clean($pageTitle ?? 'Dashboard'); ?></h1>
      <div style="display:flex; align-items:center; gap:0.6rem;">
        <button type="button" class="theme-toggle" aria-label="Toggle dark mode"><i class="fa-solid fa-moon"></i></button>
        <span class="user-avatar"><?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?></span>
      </div>
    </div>
