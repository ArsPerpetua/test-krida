<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$showFilter = isset($_GET['show_filter']) ? 1 : 0;
$custKode = trim($_GET['cust_kode'] ?? '');
$custNama = trim($_GET['cust_nama'] ?? '');
$custHp = trim($_GET['cust_hp'] ?? '');
$custKodeNumber = extract_code_number($custKode);

$sql = "SELECT * FROM customers WHERE 1=1";
$types = '';
$params = [];

if ($custKodeNumber !== '') {
    $sql .= " AND custId = ?";
    $types .= 'i';
    $params[] = (int) $custKodeNumber;
}

if ($custNama !== '') {
    $sql .= " AND cust_nama LIKE ?";
    $types .= 's';
    $params[] = '%' . $custNama . '%';
}

if ($custHp !== '') {
    $sql .= " AND cust_hp LIKE ?";
    $types .= 's';
    $params[] = '%' . $custHp . '%';
}

$sql .= " ORDER BY custId DESC";
$stmt = $conn->prepare($sql);

if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="card">
    <div class="actions" style="justify-content: space-between; align-items: flex-start;">
        <div>
            <h2>Master Customer</h2>
            <div class="actions" style="margin-top: 12px;">
                <a class="btn btn-light" href="/test-krida/customers/index.php?show_filter=1">Search Filtering</a>
            </div>
        </div>
        <div class="actions">
            <a class="btn btn-primary" href="/test-krida/customers/form.php">Tambah Customer</a>
        </div>
    </div>

    <?php if ($showFilter): ?>
        <form action="/test-krida/customers/index.php" method="get" class="card">
            <input type="hidden" name="show_filter" value="1">
            <table>
                <tr>
                    <td style="width: 220px;">Kode Customer</td>
                    <td><input type="text" id="cust_kode" name="cust_kode" value="<?= htmlspecialchars($custKode) ?>"></td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td><input type="text" id="cust_nama" name="cust_nama" value="<?= htmlspecialchars($custNama) ?>"></td>
                </tr>
                <tr>
                    <td>Nomor Telepon</td>
                    <td><input type="text" id="cust_hp" name="cust_hp" value="<?= htmlspecialchars($custHp) ?>"></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <div class="actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a class="btn btn-light" href="/test-krida/customers/index.php?show_filter=1">Reset</a>
                            <a class="btn btn-light" href="/test-krida/customers/index.php">Tutup</a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Kode Customer</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>Nomor Telepon</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars(format_running_code('C', (int) $row['custId'])) ?></td>
                        <td><?= htmlspecialchars($row['cust_nama']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['cust_alamat'])) ?></td>
                        <td><?= htmlspecialchars($row['cust_hp']) ?></td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-light" href="/test-krida/customers/view.php?id=<?= (int) $row['custId'] ?>">View</a>
                                <a class="btn btn-warning" href="/test-krida/customers/form.php?id=<?= (int) $row['custId'] ?>">Edit</a>
                                <a class="btn btn-danger" href="/test-krida/customers/delete.php?id=<?= (int) $row['custId'] ?>" onclick="return confirm('Are you sure to delete <?= htmlspecialchars(format_running_code('C', (int) $row['custId'])) ?>?')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty">Data customer masih kosong.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
