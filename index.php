<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="home-title">Halaman Utama</div>

<div class="home-groups">
    <div class="home-group-title"><button type="button" class="home-group-link" data-target="master-file-preview">Master File</button></div>
    <div class="home-group-title"><button type="button" class="home-group-link" data-target="transaksi-preview">Transaksi</button></div>
</div>

<div class="card home-preview" id="master-file-preview">
    <div class="home-menu-title">Menu</div>
    <div class="home-menu">
        <a href="/test-krida/customers/index.php">Master Customer</a>
        <a href="/test-krida/items/index.php">Master Item</a>
    </div>
</div>

<div class="card home-preview" id="transaksi-preview">
    <div class="home-menu-title">Menu</div>
    <div class="home-menu">
        <a href="/test-krida/orders/index.php">Transaksi</a>
    </div>
</div>

<script>
    const homeGroupLinks = document.querySelectorAll('.home-group-link');
    const homePreviewCards = document.querySelectorAll('.home-preview');

    homeGroupLinks.forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.target;

            homePreviewCards.forEach((card) => {
                card.classList.toggle('active', card.id === targetId);
            });
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
