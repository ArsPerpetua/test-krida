<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM items WHERE itemId = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<div class="card">
    <?php if ($item): ?>
        <div class="actions" style="justify-content: flex-end; margin-bottom: 18px;">
            <a class="btn btn-warning" href="/test-krida/items/form.php?id=<?= (int) $item['itemId'] ?>">Edit Item</a>
            <a class="btn btn-danger" href="/test-krida/items/delete.php?id=<?= (int) $item['itemId'] ?>" onclick="return confirm('Are you sure to delete <?= htmlspecialchars(format_running_code('P', (int) $item['itemId'])) ?>?')">Delete Item</a>
        </div>

        <div style="margin-bottom: 16px;">
            <strong>Item Number</strong> <?= htmlspecialchars(format_running_code('P', (int) $item['itemId'])) ?>
        </div>

        <hr style="border: 0; border-top: 1px solid #000; margin: 16px 0;">

        <table>
            <tr>
                <td style="width: 220px;">Deskripsi</td>
                <td><?= htmlspecialchars($item['deskripsi']) ?></td>
            </tr>
            <tr>
                <td>Harga</td>
                <td><?= format_rupiah($item['price']) ?></td>
            </tr>
        </table>

        <div class="actions" style="margin-top: 18px;">
            <a class="btn btn-light" href="/test-krida/items/index.php">Kembali</a>
            <a class="btn btn-primary" href="/test-krida/items/form.php?id=<?= (int) $item['itemId'] ?>">Save</a>
        </div>
    <?php else: ?>
        <div class="empty">Data item tidak ditemukan.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
