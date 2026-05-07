<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="home-title">Halaman Utama</div>

<div class="home-groups">
    <div class="home-group-title"><button type="button" class="home-group-link" data-target="master-file-preview">Master
            File</button></div>
    <div class="home-group-title"><button type="button" class="home-group-link"
            data-target="transaksi-preview">Transaksi</button></div>
</div>

<div style="margin-top: 32px;">
    <div style="width: 100%; max-width: 350px;">
        <div id="show-all-menus"
            style="font-size: 22px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 8px; cursor: pointer; user-select: none;">
            Daftar Menu</div>

        <div style="padding-left: 10px;">
            <div class="home-preview active" id="master-file-preview" style="margin-top: 0; margin-bottom: 24px;">
                <div style="font-size: 18px; font-weight: 600; margin-bottom: 12px; color: #111;">Master File</div>
                <div class="home-menu"
                    style="width: auto; margin-left: 20px; border-left: 2px solid #ddd; padding-left: 16px;">
                    <a href="/test-krida/customers/index.php" style="padding: 6px 0; display: block;">&#8226; Master
                        Customer</a>
                    <a href="/test-krida/items/index.php" style="padding: 6px 0; display: block;">&#8226; Master
                        Item</a>
                </div>
            </div>

            <div class="home-preview active" id="transaksi-preview" style="margin-top: 0;">
                <div style="font-size: 18px; font-weight: 600; margin-bottom: 12px; color: #111;">Transaksi</div>
                <div class="home-menu"
                    style="width: auto; margin-left: 20px; border-left: 2px solid #ddd; padding-left: 16px;">
                    <a href="/test-krida/orders/index.php" style="padding: 6px 0; display: block;">&#8226; Order</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const homeGroupLinks = document.querySelectorAll('.home-group-link');
    const allPreviews = document.querySelectorAll('.home-preview');
    const showAllButton = document.getElementById('show-all-menus');
    let isFirstClick = true; // Penanda klik pertama

    if (showAllButton) {
        showAllButton.addEventListener('click', () => {
            // Tampilkan semua menu
            allPreviews.forEach(menu => {
                menu.classList.add('active');
            });
            // Reset state agar klik pada tombol atas (Master/Transaksi) berfungsi seperti semula
            isFirstClick = true;
        });
    }

    homeGroupLinks.forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.target;

            if (isFirstClick) {
                // Klik pertama kali: Tampilkan hanya yang diklik, sembunyikan yang lain
                allPreviews.forEach(menu => { menu.classList.toggle('active', menu.id === targetId); });
                isFirstClick = false;
            } else {
                // Klik selanjutnya: Buka-tutup menu target, tutup menu lainnya
                allPreviews.forEach(menu => {
                    if (menu.id === targetId) { menu.classList.toggle('active'); }
                    else { menu.classList.remove('active'); }
                });
            }
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>