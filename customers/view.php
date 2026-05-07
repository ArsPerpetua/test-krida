<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$customer = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE custId = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<div class="card">
    <?php if ($customer): ?>
        <div class="actions" style="justify-content: flex-end; margin-bottom: 18px;">
            <a class="btn btn-warning" href="/test-krida/customers/form.php?id=<?= (int) $customer['custId'] ?>">Edit Customer</a>
            <a class="btn btn-danger" href="/test-krida/customers/delete.php?id=<?= (int) $customer['custId'] ?>" onclick="return confirm('Are you sure to delete <?= htmlspecialchars(format_running_code('C', (int) $customer['custId'])) ?>?')">Delete Customer</a>
        </div>

        <div style="margin-bottom: 16px;">
            <strong>Kode Customer</strong> <?= htmlspecialchars(format_running_code('C', (int) $customer['custId'])) ?>
        </div>

        <hr style="border: 0; border-top: 1px solid #000; margin: 16px 0;">

        <table>
            <tr>
                <td style="width: 220px;">Nama</td>
                <td><?= htmlspecialchars($customer['cust_nama']) ?></td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td><?= nl2br(htmlspecialchars($customer['cust_alamat'])) ?></td>
            </tr>
            <tr>
                <td>Nomor Hp</td>
                <td><?= htmlspecialchars($customer['cust_hp']) ?></td>
            </tr>
        </table>

        <div class="actions" style="margin-top: 18px;">
            <a class="btn btn-light" href="/test-krida/customers/index.php">Kembali</a>
            <a class="btn btn-primary" href="/test-krida/customers/form.php?id=<?= (int) $customer['custId'] ?>">Save</a>
        </div>
    <?php else: ?>
        <div class="empty">Data customer tidak ditemukan.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
