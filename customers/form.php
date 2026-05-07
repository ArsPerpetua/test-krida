<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$customer = [
    'custId' => 0,
    'cust_nama' => '',
    'cust_alamat' => '',
    'cust_hp' => '',
];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE custId = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc() ?: $customer;
    $stmt->close();
}
?>

<div class="card">
    <h2><?= $customer['custId'] ? 'Edit Customer' : 'Tambah Customer' ?></h2>
    <form action="/test-krida/customers/save.php" method="post">
        <input type="hidden" name="custId" value="<?= (int) $customer['custId'] ?>">
        <table>
            <tr>
                <td style="width: 220px;">Nama</td>
                <td><input type="text" id="cust_nama" name="cust_nama" required value="<?= htmlspecialchars($customer['cust_nama']) ?>"></td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td><textarea id="cust_alamat" name="cust_alamat" required><?= htmlspecialchars($customer['cust_alamat']) ?></textarea></td>
            </tr>
            <tr>
                <td>Nomor HP</td>
                <td><input type="text" id="cust_hp" name="cust_hp" required value="<?= htmlspecialchars($customer['cust_hp']) ?>"></td>
            </tr>
        </table>
        <div class="actions" style="margin-top: 18px;">
            <button type="submit" class="btn btn-primary">Save</button>
            <a class="btn btn-light" href="/test-krida/customers/index.php">Kembali</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
