<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = [
    'itemId' => 0,
    'deskripsi' => '',
    'price' => 0,
];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM items WHERE itemId = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc() ?: $item;
    $stmt->close();
}
?>

<div class="card">
    <h2><?= $item['itemId'] ? 'Edit Item' : 'Tambah Item' ?></h2>
    <form action="/test-krida/items/save.php" method="post">
        <input type="hidden" name="itemId" value="<?= (int) $item['itemId'] ?>">
        <table>
            <tr>
                <td style="width: 220px;">Deskripsi</td>
                <td><input type="text" id="deskripsi" name="deskripsi" required value="<?= htmlspecialchars($item['deskripsi']) ?>"></td>
            </tr>
            <tr>
                <td>Harga</td>
                <td><input type="number" step="0.01" min="0" id="price" name="price" required value="<?= htmlspecialchars((string) $item['price']) ?>"></td>
            </tr>
        </table>
        <div class="actions" style="margin-top: 18px;">
            <button type="submit" class="btn btn-primary">Save</button>
            <a class="btn btn-light" href="/test-krida/items/index.php">Kembali</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
