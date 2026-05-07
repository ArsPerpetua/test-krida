<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$customers = $conn->query("SELECT custId, cust_nama FROM customers ORDER BY cust_nama ASC");
$items = $conn->query("SELECT itemId, deskripsi, price FROM items ORDER BY deskripsi ASC");
$itemsData = [];

if ($items) {
    while ($itemRow = $items->fetch_assoc()) {
        $itemsData[] = $itemRow;
    }
}

$order = [
    'orderId' => 0,
    'orderNo' => generate_order_no($conn),
    'orderDate' => date('Y-m-d'),
    'custId' => '',
    'headerDiscPercent' => 0,
    'headerDisc' => 0,
];
$details = [];

if ($orderId > 0) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE orderId = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $dbOrder = $result->fetch_assoc();
    $stmt->close();

    if ($dbOrder) {
        $order['orderId'] = (int) $dbOrder['orderId'];
        $order['orderNo'] = $dbOrder['orderNo'];
        $order['orderDate'] = $dbOrder['orderDate'];
        $order['custId'] = $dbOrder['custId'];

        $detailStmt = $conn->prepare("SELECT * FROM orderItem WHERE orderId = ?");
        $detailStmt->bind_param('i', $orderId);
        $detailStmt->execute();
        $detailResult = $detailStmt->get_result();
        $lineDiscountTotal = 0;

        while ($detailRow = $detailResult->fetch_assoc()) {
            $details[] = $detailRow;
            $lineDiscountTotal += (float) $detailRow['discAmount'];
        }

        $detailStmt->close();
        $order['headerDisc'] = max(0, (float) $dbOrder['discAmount'] - $lineDiscountTotal);
        $subtotalBase = 0;
        $order['headerDiscPercent'] = 0;

        foreach ($details as $detail) {
            $subtotalBase += (float) $detail['qty'] * (float) $detail['price'];
        }

        if ($subtotalBase > 0) {
            $order['headerDiscPercent'] = ($order['headerDisc'] / $subtotalBase) * 100;
        }
    }
}

if (empty($details)) {
    $details[] = [
        'itemId' => '',
        'qty' => 1,
        'price' => 0,
        'discAmount' => 0,
        'totalItem' => 0,
    ];
}
?>

<div class="card">
    <h2><?= $order['orderId'] ? 'Edit Sales Order' : 'Tambah Sales Order' ?></h2>

    <?php if (count($itemsData) === 0 || !$customers || $customers->num_rows === 0): ?>
        <div class="alert alert-error">
            Master customer dan item harus tersedia lebih dulu sebelum membuat sales order.
        </div>
    <?php else: ?>
        <form action="/test-krida/orders/save.php" method="post" id="order-form">
            <input type="hidden" name="orderId" value="<?= (int) $order['orderId'] ?>">
            <input type="hidden" id="headerDiscPercent" name="headerDiscPercent" value="<?= (float) $order['headerDiscPercent'] ?>">
            <input type="hidden" id="headerDisc" name="headerDisc" value="<?= (float) $order['headerDisc'] ?>">
            <table style="max-width: 500px;">
                <tr>
                    <td style="width: 150px; font-weight: 600; vertical-align: middle;">Nomor Order</td>
                    <td><input type="text" id="orderNo" name="orderNo" required readonly value="<?= htmlspecialchars($order['orderNo']) ?>"></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; vertical-align: middle;">Tanggal Order</td>
                    <td><input type="date" id="orderDate" name="orderDate" required value="<?= htmlspecialchars($order['orderDate']) ?>"></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; vertical-align: middle;">Nama Customer</td>
                    <td>
                        <select id="custId" name="custId" required>
                            <option value="">Pilih customer</option>
                            <?php
                            $customers->data_seek(0);
                            while ($customer = $customers->fetch_assoc()):
                                ?>
                                <option value="<?= (int) $customer['custId'] ?>" <?= (string) $order['custId'] === (string) $customer['custId'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(format_running_code('C', (int) $customer['custId']) . ' - ' . $customer['cust_nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 20px;">
                <div class="actions" style="justify-content: space-between;">
                    <h3>Detail Item</h3>
                </div>

                <table id="items-table">
                    <thead>
                        <tr>
                            <th>Item Number</th>
                            <th>Deskripsi</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Discount (%)</th>
                            <th>Cash Disc</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $detail): ?>
                            <?php
                            $detailDesc = '';
                            foreach ($itemsData as $item) {
                                if ((string) $item['itemId'] === (string) $detail['itemId']) {
                                    $detailDesc = $item['deskripsi'];
                                    break;
                                }
                            }
                            $lineSubtotal = (float) $detail['qty'] * (float) $detail['price'];
                            $discPercent = $lineSubtotal > 0 ? ((float) $detail['discAmount'] / $lineSubtotal) * 100 : 0;
                            ?>
                            <tr>
                                <td>
                                    <select name="itemId[]" class="item-select" required>
                                        <option value="">Pilih item</option>
                                        <?php foreach ($itemsData as $item): ?>
                                            <option value="<?= (int) $item['itemId'] ?>" data-price="<?= (float) $item['price'] ?>"
                                                data-desc="<?= htmlspecialchars($item['deskripsi']) ?>"
                                                <?= (string) $detail['itemId'] === (string) $item['itemId'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(format_running_code('P', (int) $item['itemId'])) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="text" class="desc-input" readonly value="<?= htmlspecialchars($detailDesc) ?>"></td>
                                <td><input type="number" step="0.01" min="0.01" name="qty[]" class="qty-input" required style="width: 70px;"
                                        value="<?= (float) $detail['qty'] ?>"></td>
                                <td><input type="number" step="0.01" min="0" name="price[]" class="price-input" required style="width: 100px;"
                                        value="<?= (float) $detail['price'] ?>"></td>
                                <td><input type="number" step="0.01" min="0" name="discPercent[]" class="disc-percent-input" style="width: 80px;"
                                        value="<?= round($discPercent, 2) ?>"></td>
                                <td><input type="number" step="0.01" min="0" name="itemDisc[]" class="disc-input" style="width: 100px;"
                                        value="<?= (float) $detail['discAmount'] ?>"></td>
                                <td><input type="number" step="0.01" min="0" name="totalItem[]" class="total-input" readonly style="width: 120px;"
                                        value="<?= (float) $detail['totalItem'] ?>"></td>
                                <td>
                                    <div class="actions" style="flex-wrap: nowrap;">
                                        <button type="button" class="btn btn-primary add-row-btn" style="padding: 6px 12px; font-size: 16px;">+</button>
                                        <button type="button" class="btn btn-danger remove-row-btn" style="padding: 6px 12px; font-size: 16px;">-</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <table class="summary">
                <input type="hidden" name="subtotal" id="subtotal">
                <input type="hidden" name="discAmount" id="discAmount">
                <tr>
                    <td>Netto</td>
                    <td><input type="number" step="0.01" min="0" name="netto" id="netto" readonly></td>
                </tr>
                <tr>
                    <td>DPP</td>
                    <td><input type="number" step="0.01" min="0" name="dpp" id="dpp" readonly></td>
                </tr>
                <tr>
                    <td>PPN</td>
                    <td><input type="number" step="0.01" min="0" name="ppn" id="ppn" readonly></td>
                </tr>
                <tr>
                    <td>Total Kwitansi</td>
                    <td><input type="number" step="0.01" min="0" name="grandtotal" id="grandtotal" readonly></td>
                </tr>
            </table>

            <div class="actions" style="margin-top: 18px;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a class="btn btn-light" href="/test-krida/orders/index.php">Kembali</a>
            </div>
        </form>

        <template id="row-template">
            <tr>
                <td>
                    <select name="itemId[]" class="item-select" required>
                        <option value="">Pilih item</option>
                        <?php foreach ($itemsData as $item): ?>
                            <option value="<?= (int) $item['itemId'] ?>" data-price="<?= (float) $item['price'] ?>" data-desc="<?= htmlspecialchars($item['deskripsi']) ?>">
                                <?= htmlspecialchars(format_running_code('P', (int) $item['itemId'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" class="desc-input" readonly value=""></td>
                <td><input type="number" step="0.01" min="0.01" name="qty[]" class="qty-input" required style="width: 70px;" value="1"></td>
                <td><input type="number" step="0.01" min="0" name="price[]" class="price-input" required style="width: 100px;" value="0"></td>
                <td><input type="number" step="0.01" min="0" name="discPercent[]" class="disc-percent-input" style="width: 80px;" value="0"></td>
                <td><input type="number" step="0.01" min="0" name="itemDisc[]" class="disc-input" style="width: 100px;" value="0"></td>
                <td><input type="number" step="0.01" min="0" name="totalItem[]" class="total-input" readonly style="width: 120px;" value="0"></td>
                <td>
                    <div class="actions" style="flex-wrap: nowrap;">
                        <button type="button" class="btn btn-primary add-row-btn" style="padding: 6px 12px; font-size: 16px;">+</button>
                        <button type="button" class="btn btn-danger remove-row-btn" style="padding: 6px 12px; font-size: 16px;">-</button>
                    </div>
                </td>
            </tr>
        </template>

        <script>
            const form = document.getElementById('order-form');
            const tableBody = document.querySelector('#items-table tbody');
            const template = document.getElementById('row-template');
            const headerDiscPercentInput = document.getElementById('headerDiscPercent');
            const headerDiscInput = document.getElementById('headerDisc');

            function toNumber(value) {
                const parsed = parseFloat(value);
                return Number.isFinite(parsed) ? parsed : 0;
            }

            function recalculateRow(row) {
                const qty = toNumber(row.querySelector('.qty-input').value);
                const price = toNumber(row.querySelector('.price-input').value);
                const disc = toNumber(row.querySelector('.disc-input').value);
                const total = Math.max((qty * price) - disc, 0);
                row.querySelector('.total-input').value = Number(total.toFixed(2));
            }

            function recalculateSummary() {
                let subtotal = 0;
                let itemDiscTotal = 0;

                tableBody.querySelectorAll('tr').forEach((row) => {
                    const qty = toNumber(row.querySelector('.qty-input').value);
                    const price = toNumber(row.querySelector('.price-input').value);
                    const disc = toNumber(row.querySelector('.disc-input').value);
                    subtotal += qty * price;
                    itemDiscTotal += disc;
                    recalculateRow(row);
                });

                const headerDiscPercent = Math.min(Math.max(toNumber(headerDiscPercentInput.value), 0), 100);
                const headerDisc = subtotal * (headerDiscPercent / 100);
                headerDiscInput.value = Number(headerDisc.toFixed(2));
                const discAmount = itemDiscTotal + headerDisc;
                const netto = Math.max(subtotal - discAmount, 0);
                const dpp = netto;
                const ppn = dpp * 0.11;
                const grandtotal = dpp + ppn;

                document.getElementById('subtotal').value = Number(subtotal.toFixed(2));
                document.getElementById('discAmount').value = Number(discAmount.toFixed(2));
                document.getElementById('netto').value = Number(netto.toFixed(2));
                document.getElementById('dpp').value = Number(dpp.toFixed(2));
                document.getElementById('ppn').value = Number(ppn.toFixed(2));
                document.getElementById('grandtotal').value = Number(grandtotal.toFixed(2));
            }

            function bindRowEvents(row) {
                row.querySelector('.item-select').addEventListener('change', (event) => {
                    const option = event.target.selectedOptions[0];
                    if (option) {
                        if (option.dataset.price) row.querySelector('.price-input').value = option.dataset.price;
                        if (option.dataset.desc) row.querySelector('.desc-input').value = option.dataset.desc;
                    } else {
                        row.querySelector('.price-input').value = 0;
                        row.querySelector('.desc-input').value = '';
                    }
                    recalculateSummary();
                });

                const qtyInput = row.querySelector('.qty-input');
                const priceInput = row.querySelector('.price-input');
                const discPercentInput = row.querySelector('.disc-percent-input');
                const discInput = row.querySelector('.disc-input');

                [qtyInput, priceInput].forEach((input) => {
                    input.addEventListener('input', () => {
                        const qty = toNumber(qtyInput.value);
                        const price = toNumber(priceInput.value);
                        const percent = toNumber(discPercentInput.value);
                        discInput.value = Number(((qty * price) * (percent / 100)).toFixed(2));
                        recalculateSummary();
                    });
                });

                discPercentInput.addEventListener('input', () => {
                    const qty = toNumber(qtyInput.value);
                    const price = toNumber(priceInput.value);
                    const percent = toNumber(discPercentInput.value);
                    discInput.value = Number(((qty * price) * (percent / 100)).toFixed(2));
                    recalculateSummary();
                });

                discInput.addEventListener('input', () => {
                    const qty = toNumber(qtyInput.value);
                    const price = toNumber(priceInput.value);
                    const disc = toNumber(discInput.value);
                    const sub = qty * price;
                    if (sub > 0) {
                        discPercentInput.value = Number(((disc / sub) * 100).toFixed(2));
                    } else {
                        discPercentInput.value = 0;
                    }
                    recalculateSummary();
                });

                row.querySelector('.remove-row-btn').addEventListener('click', () => {
                    if (tableBody.querySelectorAll('tr').length > 1) {
                        row.remove();
                        recalculateSummary();
                    }
                });

                row.querySelector('.add-row-btn').addEventListener('click', () => {
                    const clone = template.content.firstElementChild.cloneNode(true);
                    tableBody.appendChild(clone);
                    bindRowEvents(clone);
                    recalculateSummary();
                });
            }

            headerDiscPercentInput.addEventListener('input', recalculateSummary);

            tableBody.querySelectorAll('tr').forEach(bindRowEvents);
            recalculateSummary();

            form.addEventListener('submit', () => {
                recalculateSummary();
            });
        </script>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>