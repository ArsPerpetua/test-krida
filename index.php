<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="home-title">Halaman Utama</div>

<div class="home-groups">
    <div class="home-group-title"><a href="">Master File</a></div>
    <div class="home-group-title"><a href="/test-krida/orders/index.php">Transaksi</a></div>
</div>

<div class="card">
    <div class="home-menu-title">Menu</div>
    <div class="home-menu">
        <a href="/test-krida/customers/index.php">Master Customer</a>
        <a href="/test-krida/items/index.php">Master Item</a>
        <a href="/test-krida/orders/index.php">Transaksi</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
