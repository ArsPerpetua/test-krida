<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$order = null;
$details = null;
$itemDiscountTotal = 0;

if ($id > 0) {
    $stmt = $conn->prepare("
        SELECT o.*, c.cust_nama, c.custId
        FROM orders o
        INNER JOIN customers c ON c.custId = o.custId
        WHERE o.orderId = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($order) {
        $detailStmt = $conn->prepare("
            SELECT oi.*, i.deskripsi
            FROM orderItem oi
            INNER JOIN items i ON i.itemId = oi.itemId
            WHERE oi.orderId = ?
            ORDER BY oi.orderItemId ASC
        ");
        $detailStmt->bind_param('i', $id);
        $detailStmt->execute();
        $details = $detailStmt->get_result();
        $detailStmt->close();

        if ($details) {
            foreach ($details as $detailRow) {
                $itemDiscountTotal += (float) $detailRow['discAmount'];
                $lineSubtotal = (float) $detailRow['qty'] * (float) $detailRow['price'];
                $detailRow['cashDisc'] = (float) $detailRow['discAmount'];
                $detailRow['discPercent'] = 0;
                if ($lineSubtotal > 0 && (float) $detailRow['discAmount'] > 0) {
                    $detailRow['cashDisc'] = (float) $detailRow['discAmount'];
                }
            }
            $details->data_seek(0);
        }
    }
}
?>

<div class="card">
    <?php if ($order): ?>
        <div class="actions" style="justify-content: flex-end; margin-bottom: 18px;">
            <a class="btn btn-warning" href="/test-krida/orders/form.php?id=<?= (int) $order['orderId'] ?>">Edit Order</a>
            <a class="btn btn-danger" href="/test-krida/orders/delete.php?id=<?= (int) $order['orderId'] ?>"
                onclick="return confirm('Are you sure to delete <?= htmlspecialchars($order['orderNo']) ?>?')">Delete
                Order</a>
        </div>

        <div style="margin-bottom: 16px;">
            <strong>Nomor Order</strong> <?= htmlspecialchars($order['orderNo']) ?>
        </div>

        <hr style="border: 0; border-top: 1px solid #000; margin: 16px 0;">

        <table>
            <tr>
                <td style="width: 220px;">Tanggal Order</td>
                <td><?= htmlspecialchars($order['orderDate']) ?></td>
            </tr>
            <tr>
                <td>Customer</td>
                <td><?= htmlspecialchars($order['cust_nama']) ?></td>
            </tr>
            <tr>
                <td>Diskon</td>
                <td><?= format_rupiah($order['discAmount']) ?></td>
            </tr>
            <tr>
                <td>Netto</td>
                <td><?= format_rupiah($order['netto']) ?></td>
            </tr>
            <tr>
                <td>DPP</td>
                <td><?= format_rupiah($order['dpp']) ?></td>
            </tr>
            <tr>
                <td>PPN</td>
                <td><?= format_rupiah($order['ppn']) ?></td>
            </tr>
            <tr>
                <td>Total Kwitansi</td>
                <td><?= format_rupiah($order['grandtotal']) ?></td>
            </tr>
        </table>

        <div style="margin-top: 20px;">
            <strong>Detail Item</strong>
            <?php if ($details && $details->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item Number</th>
                            <th>Deskripsi</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Discount (%)</th>
                            <th>Cash Disc</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($detail = $details->fetch_assoc()): ?>
                            <?php $lineSubtotal = (float) $detail['qty'] * (float) $detail['price']; ?>
                            <?php $discPercent = $lineSubtotal > 0 ? ((float) $detail['discAmount'] / $lineSubtotal) * 100 : 0; ?>
                            <tr>
                                <td><?= htmlspecialchars(format_running_code('P', (int) $detail['itemId'])) ?></td>
                                <td><?= htmlspecialchars($detail['deskripsi']) ?></td>
                                <td><?= htmlspecialchars((string) $detail['qty']) ?></td>
                                <td><?= format_rupiah($detail['price']) ?></td>
                                <td><?= htmlspecialchars(number_format($discPercent, 2)) ?></td>
                                <td><?= format_rupiah($detail['discAmount']) ?></td>
                                <td><?= format_rupiah($detail['totalItem']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="actions" style="margin-top: 18px;">
            <a class="btn btn-light" href="/test-krida/orders/index.php">Kembali</a>
            <a class="btn btn-primary" href="/test-krida/orders/form.php?id=<?= (int) $order['orderId'] ?>">Save</a>
        </div>
    <?php else: ?>
        <div class="empty">Data sales order tidak ditemukan.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
