<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$showFilter = isset($_GET['show_filter']) ? 1 : 0;
$orderNo = trim($_GET['order_no'] ?? '');
$orderNoValue = extract_code_number($orderNo);
$orderDate = trim($_GET['order_date'] ?? '');
$customer = trim($_GET['customer'] ?? '');

$sql = "
    SELECT o.orderId, o.orderNo, o.orderDate, c.cust_nama, c.custId
    FROM orders o
    INNER JOIN customers c ON c.custId = o.custId
    WHERE 1=1
";
$types = '';
$params = [];

if ($orderNoValue !== '') {
    $sql .= " AND CAST(SUBSTRING(o.orderNo, 3) AS UNSIGNED) = ?";
    $types .= 'i';
    $params[] = (int) $orderNoValue;
}

if ($orderDate !== '') {
    $sql .= " AND o.orderDate = ?";
    $types .= 's';
    $params[] = $orderDate;
}

if ($customer !== '') {
    $sql .= " AND c.cust_nama LIKE ?";
    $types .= 's';
    $params[] = '%' . $customer . '%';
}

$sql .= " ORDER BY o.orderId DESC";
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
            <h2>Transaksi Sales Order</h2>
            <div class="actions" style="margin-top: 12px;">
                <a class="btn btn-light" href="/test-krida/orders/index.php?show_filter=1">Search Filtering</a>
            </div>
        </div>
        <div class="actions">
            <a class="btn btn-primary" href="/test-krida/orders/form.php">Tambah Sales Order</a>
        </div>
    </div>

    <?php if ($showFilter): ?>
            <form action="/test-krida/orders/index.php" method="get" class="card">
                <input type="hidden" name="show_filter" value="1">
                <table>
                    <tr>
                        <td style="width: 220px;">Nomor Order</td>
                        <td><input type="text" id="order_no" name="order_no" value="<?= htmlspecialchars($orderNo) ?>"></td>
                    </tr>
                    <tr>
                        <td>Tanggal Order</td>
                        <td><input type="date" id="order_date" name="order_date" value="<?= htmlspecialchars($orderDate) ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Nama Customer</td>
                        <td><input type="text" id="customer" name="customer" value="<?= htmlspecialchars($customer) ?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <div class="actions">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a class="btn btn-light" href="/test-krida/orders/index.php?show_filter=1">Reset</a>
                                <a class="btn btn-light" href="/test-krida/orders/index.php">Tutup</a>
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
                        <th>Nomor Order</th>
                        <th>Tanggal Order</th>
                        <th>Nama Customer</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['orderNo']) ?></td>
                                <td><?= htmlspecialchars($row['orderDate']) ?></td>
                                <td><?= htmlspecialchars($row['cust_nama']) ?></td>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn-light"
                                            href="/test-krida/orders/view.php?id=<?= (int) $row['orderId'] ?>">View</a>
                                    </div>
                                </td>
                            </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
    <?php else: ?>
            <div class="empty">Belum ada data sales order.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
