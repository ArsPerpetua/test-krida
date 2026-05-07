<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$showFilter = isset($_GET['show_filter']) ? 1 : 0;
$orderNo = trim($_GET['order_no'] ?? '');
$orderNoValue = extract_code_number($orderNo);
$orderDate = trim($_GET['order_date'] ?? '');
$customer = trim($_GET['customer'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$baseSql = "
    FROM orders o
    INNER JOIN customers c ON c.custId = o.custId
    WHERE 1=1
";
$types = '';
$params = [];

if ($orderNoValue !== '') {
    $baseSql .= " AND CAST(SUBSTRING(o.orderNo, 3) AS UNSIGNED) = ?";
    $types .= 'i';
    $params[] = (int) $orderNoValue;
}

if ($orderDate !== '') {
    $baseSql .= " AND o.orderDate = ?";
    $types .= 's';
    $params[] = $orderDate;
}

if ($customer !== '') {
    $baseSql .= " AND c.cust_nama LIKE ?";
    $types .= 's';
    $params[] = '%' . $customer . '%';
}

$countSql = "SELECT COUNT(*) AS total " . $baseSql;
$countStmt = $conn->prepare($countSql);

if ($types !== '') {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();
$totalRecords = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
$countStmt->close();

$totalPages = max(1, (int) ceil($totalRecords / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT o.orderId, o.orderNo, o.orderDate, c.cust_nama, c.custId
    FROM orders o
    INNER JOIN customers c ON c.custId = o.custId
    WHERE 1=1
";
$sql = "SELECT o.orderId, o.orderNo, o.orderDate, c.cust_nama, c.custId " . $baseSql . " ORDER BY o.orderId DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

$runTypes = $types . 'ii';
$runParams = [...$params, $perPage, $offset];
$stmt->bind_param($runTypes, ...$runParams);
$stmt->execute();
$result = $stmt->get_result();
$currentCount = $result ? $result->num_rows : 0;

$paginationQuery = [
    'show_filter' => $showFilter ? 1 : null,
    'order_no' => $orderNo !== '' ? $orderNo : null,
    'order_date' => $orderDate !== '' ? $orderDate : null,
    'customer' => $customer !== '' ? $customer : null,
];
?>

<div class="card">
    <div class="actions" style="justify-content: space-between; align-items: flex-start;">
        <div>
            <h2>Transaksi Sales Order</h2>
            <div style="margin-top: 12px;">
                <a href="/test-krida/orders/index.php?show_filter=1"
                    style="color: #111; text-decoration: underline; font-weight: 600;">Search Filtering</a>
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
        <div style="margin-top: 18px;">
            <div>Page <?= $page ?> of <?= $totalPages ?> show <?= $currentCount ?> record</div>
            <div class="actions" style="margin-top: 8px;">
                <?php
                $prevQuery = http_build_query(array_filter(array_merge($paginationQuery, ['page' => max(1, $page - 1)]), fn($v) => $v !== null));
                $nextQuery = http_build_query(array_filter(array_merge($paginationQuery, ['page' => min($totalPages, $page + 1)]), fn($v) => $v !== null));
                ?>
                <a href="/test-krida/orders/index.php<?= $page > 1 ? '?' . $prevQuery : '' ?>">previous</a>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                    $pageQuery = http_build_query(array_filter(array_merge($paginationQuery, ['page' => $i]), fn($v) => $v !== null));
                    ?>
                        <a href="/test-krida/orders/index.php?<?= $pageQuery ?>"><?= $i ?></a><?= $i < $totalPages ? '|' : '' ?>
                <?php endfor; ?>
                <a href="/test-krida/orders/index.php<?= $page < $totalPages ? '?' . $nextQuery : '' ?>">next</a>
                </div>
        </div>
    <?php else: ?>
            <div class="empty">Belum ada data sales order.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>