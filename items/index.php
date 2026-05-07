<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$showFilter = isset($_GET['show_filter']) ? 1 : 0;
$itemNumber = trim($_GET['item_number'] ?? '');
$deskripsi = trim($_GET['deskripsi'] ?? '');
$itemNumberValue = extract_code_number($itemNumber);
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$baseSql = "FROM items WHERE 1=1";
$types = '';
$params = [];

if ($itemNumberValue !== '') {
    $baseSql .= " AND itemId = ?";
    $types .= 'i';
    $params[] = (int) $itemNumberValue;
}

if ($deskripsi !== '') {
    $baseSql .= " AND deskripsi LIKE ?";
    $types .= 's';
    $params[] = '%' . $deskripsi . '%';
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

$sql = "SELECT * " . $baseSql . " ORDER BY itemId DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$runTypes = $types . 'ii';
$runParams = [...$params, $perPage, $offset];
$stmt->bind_param($runTypes, ...$runParams);
$stmt->execute();
$result = $stmt->get_result();
$currentCount = $result ? $result->num_rows : 0;

$paginationQuery = [
    'show_filter' => $showFilter ? 1 : null,
    'item_number' => $itemNumber !== '' ? $itemNumber : null,
    'deskripsi' => $deskripsi !== '' ? $deskripsi : null,
];
?>

<div class="card">
    <div class="actions" style="justify-content: space-between; align-items: flex-start;">
        <div>
            <h2>Master Item</h2>
            <div style="margin-top: 12px;">
                <a href="/test-krida/items/index.php?show_filter=1"
                    style="color: #111; text-decoration: underline; font-weight: 600;">Search Filtering</a>
            </div>
        </div>
        <div class="actions">
            <a class="btn btn-primary" href="/test-krida/items/form.php">Tambah Item</a>
        </div>
    </div>

    <?php if ($showFilter): ?>
        <form action="/test-krida/items/index.php" method="get" class="card">
            <input type="hidden" name="show_filter" value="1">
            <table>
                <tr>
                    <td style="width: 220px;">Item Number</td>
                    <td><input type="text" id="item_number" name="item_number" value="<?= htmlspecialchars($itemNumber) ?>">
                    </td>
                </tr>
                <tr>
                    <td>Deskripsi</td>
                    <td><input type="text" id="deskripsi" name="deskripsi" value="<?= htmlspecialchars($deskripsi) ?>"></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <div class="actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a class="btn btn-light" href="/test-krida/items/index.php?show_filter=1">Reset</a>
                            <a class="btn btn-light" href="/test-krida/items/index.php">Tutup</a>
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
                    <th>Item Number</th>
                    <th>Deskripsi</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars(format_running_code('P', (int) $row['itemId'])) ?></td>
                        <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                        <td><?= format_rupiah($row['price']) ?></td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-light"
                                    href="/test-krida/items/view.php?id=<?= (int) $row['itemId'] ?>">View</a>
                                <a class="btn btn-warning"
                                    href="/test-krida/items/form.php?id=<?= (int) $row['itemId'] ?>">Edit</a>
                                <a class="btn btn-danger" href="/test-krida/items/delete.php?id=<?= (int) $row['itemId'] ?>"
                                    onclick="return confirm('Are you sure to delete <?= htmlspecialchars(format_running_code('P', (int) $row['itemId'])) ?>?')">Hapus</a>
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
                $prevQuery = http_build_query(array_filter([...$paginationQuery, 'page' => max(1, $page - 1)], fn($v) => $v !== null));
                $nextQuery = http_build_query(array_filter([...$paginationQuery, 'page' => min($totalPages, $page + 1)], fn($v) => $v !== null));
                ?>
                <a href="/test-krida/items/index.php<?= $page > 1 ? '?' . $prevQuery : '' ?>">previous</a>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                    $pageQuery = http_build_query(array_filter([...$paginationQuery, 'page' => $i], fn($v) => $v !== null));
                    ?>
                    <a href="/test-krida/items/index.php?<?= $pageQuery ?>"><?= $i ?></a><?= $i < $totalPages ? '|' : '' ?>
                <?php endfor; ?>
                <a href="/test-krida/items/index.php<?= $page < $totalPages ? '?' . $nextQuery : '' ?>">next</a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty">Data item masih kosong.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>