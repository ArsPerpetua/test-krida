<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$customers = $conn->query("SELECT custId, cust_nama FROM customers ORDER BY cust_nama ASC");
$items = $conn->query("SELECT itemId, deskripsi, price FROM items ORDER BY deskripsi ASC");
$itemsData = [];
$customersData = [];

if ($items) {
    while ($itemRow = $items->fetch_assoc()) {
        $itemsData[] = $itemRow;
    }
}

if ($customers) {
    while ($customerRow = $customers->fetch_assoc()) {
        $customersData[] = $customerRow;
    }
}

$order = [
    'orderId' => 0,
    'orderNo' => generate_order_no($conn),
    'orderDate' => date('Y-m-d'),
    'custId' => '',
];
$details = [];
$selectedCustomerName = '';

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
        while ($detailRow = $detailResult->fetch_assoc()) {
            $lineSubtotal = (float) $detailRow['qty'] * (float) $detailRow['price'];
            $detailRow['discPercent'] = 0;
            $detailRow['cashDisc'] = (float) $detailRow['discAmount'];
            if ($lineSubtotal > 0 && (float) $detailRow['discAmount'] > 0) {
                $detailRow['discPercent'] = ((float) $detailRow['discAmount'] / $lineSubtotal) * 100;
            }
            $details[] = $detailRow;
        }
        $detailStmt->close();
    }
}

if ($order['custId'] !== '') {
    foreach ($customersData as $customerRow) {
        if ((string) $customerRow['custId'] === (string) $order['custId']) {
            $selectedCustomerName = $customerRow['cust_nama'];
            break;
        }
    }
}

if (empty($details)) {
    $details[] = [
        'itemId' => '',
        'qty' => 1,
        'price' => 0,
        'discPercent' => 0,
        'cashDisc' => 0,
        'discAmount' => 0,
        'totalItem' => 0,
    ];
}
?>

<div class="card">
    <h2><?= $order['orderId'] ? 'Edit Sales Order' : 'Tambah Sales Order' ?></h2>

    <?php if (count($itemsData) === 0 || count($customersData) === 0): ?>
        <div class="alert alert-error">
            Master customer dan item harus tersedia lebih dulu sebelum membuat sales order.
        </div>
        <?php if (count($customersData) === 0): ?>
            <script>
                if (confirm('Data customer kosong. Add data customer?')) {
                    window.location.href = '/test-krida/customers/form.php';
                }
            </script>
        <?php endif; ?>
    <?php else: ?>
        <form action="/test-krida/orders/save.php" method="post" id="order-form">
            <input type="hidden" name="orderId" value="<?= (int) $order['orderId'] ?>">
            <input type="hidden" name="custId" id="custId" value="<?= htmlspecialchars((string) $order['custId']) ?>">
            <table>
                <tr>
                    <td style="width: 220px;">Nomor Order</td>
                    <td><input type="text" id="orderNo" name="orderNo" required readonly value="<?= htmlspecialchars($order['orderNo']) ?>"></td>
                </tr>
                <tr>
                    <td>Tanggal Order</td>
                    <td><input type="date" id="orderDate" name="orderDate" required value="<?= htmlspecialchars($order['orderDate']) ?>"></td>
                </tr>
                <tr>
                    <td>Nama Customer</td>
                    <td>
                        <input type="text" id="customerSearch" list="customerList" value="<?= htmlspecialchars($selectedCustomerName) ?>" placeholder="Cari nama customer" autocomplete="off" required>
                        <datalist id="customerList">
                            <?php foreach ($customersData as $customer): ?>
                                <option value="<?= htmlspecialchars($customer['cust_nama']) ?>" data-id="<?= (int) $customer['custId'] ?>">
                                    <?= htmlspecialchars($customer['cust_nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 20px;">
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
                            <th>+</th>
                            <th>-</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $detail): ?>
                            <tr>
                                <td>
                                    <select name="itemId[]" class="item-select" required>
                                        <option value="">Pilih item</option>
                                        <?php foreach ($itemsData as $item): ?>
                            <option
                                                value="<?= (int) $item['itemId'] ?>"
                                                data-price="<?= htmlspecialchars((string) $item['price']) ?>"
                                                <?= (string) $detail['itemId'] === (string) $item['itemId'] ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars(format_running_code('P', (int) $item['itemId']) . ' - ' . $item['deskripsi']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="text" class="desc-input" readonly value="<?php
                                    foreach ($itemsData as $item) {
                                        if ((string) $detail['itemId'] === (string) $item['itemId']) {
                                            echo htmlspecialchars($item['deskripsi']);
                                            break;
                                        }
                                    }
                                ?>"></td>
                                <td><input type="number" step="0.01" min="0.01" name="qty[]" class="qty-input" required value="<?= htmlspecialchars((string) $detail['qty']) ?>"></td>
                                <td><input type="number" step="0.01" min="0" name="price[]" class="price-input" required value="<?= htmlspecialchars((string) $detail['price']) ?>"></td>
                                <td><input type="number" step="0.01" min="0" name="itemDiscPercent[]" class="disc-percent-input" value="<?= htmlspecialchars((string) ($detail['discPercent'] ?? 0)) ?>"></td>
                                <td><input type="number" step="0.01" min="0" name="itemDisc[]" class="cash-disc-input" readonly value="<?= htmlspecialchars((string) ($detail['cashDisc'] ?? $detail['discAmount'])) ?>"></td>
                                <td><input type="number" step="0.01" min="0" name="totalItem[]" class="total-input" readonly value="<?= htmlspecialchars((string) $detail['totalItem']) ?>"></td>
                                <td><button type="button" class="btn btn-light add-row-inline">+</button></td>
                                <td><button type="button" class="btn btn-danger remove-row">-</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <table class="summary">
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
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>

        <template id="row-template">
            <tr>
                <td>
                    <select name="itemId[]" class="item-select" required>
                        <option value="">Pilih item</option>
                        <?php foreach ($itemsData as $item): ?>
                            <option value="<?= (int) $item['itemId'] ?>" data-price="<?= htmlspecialchars((string) $item['price']) ?>">
                                <?= htmlspecialchars(format_running_code('P', (int) $item['itemId']) . ' - ' . $item['deskripsi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" class="desc-input" readonly value=""></td>
                <td><input type="number" step="0.01" min="0.01" name="qty[]" class="qty-input" required value="1"></td>
                <td><input type="number" step="0.01" min="0" name="price[]" class="price-input" required value="0"></td>
                <td><input type="number" step="0.01" min="0" name="itemDiscPercent[]" class="disc-percent-input" value="0"></td>
                <td><input type="number" step="0.01" min="0" name="itemDisc[]" class="cash-disc-input" readonly value="0"></td>
                <td><input type="number" step="0.01" min="0" name="totalItem[]" class="total-input" readonly value="0"></td>
                <td><button type="button" class="btn btn-light add-row-inline">+</button></td>
                <td><button type="button" class="btn btn-danger remove-row">-</button></td>
            </tr>
        </template>

        <script>
            const form = document.getElementById('order-form');
            const tableBody = document.querySelector('#items-table tbody');
            const template = document.getElementById('row-template');
            const customerSearchInput = document.getElementById('customerSearch');
            const customerIdInput = document.getElementById('custId');
            const customersData = <?= json_encode($customersData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

            function toNumber(value) {
                const parsed = parseFloat(value);
                return Number.isFinite(parsed) ? parsed : 0;
            }

            function resolveCustomerId() {
                const keyword = customerSearchInput.value.trim().toLowerCase();
                const found = customersData.find((customer) => customer.cust_nama.toLowerCase() === keyword);
                customerIdInput.value = found ? found.custId : '';
                return found || null;
            }

            function recalculateRow(row) {
                const qty = toNumber(row.querySelector('.qty-input').value);
                const price = toNumber(row.querySelector('.price-input').value);
                const discPercent = Math.min(Math.max(toNumber(row.querySelector('.disc-percent-input').value), 0), 100);
                const cashDisc = (qty * price) * (discPercent / 100);
                row.querySelector('.cash-disc-input').value = cashDisc.toFixed(2);
                const total = Math.max((qty * price) - cashDisc, 0);
                row.querySelector('.total-input').value = total.toFixed(2);
            }

            function recalculateSummary() {
                let netto = 0;

                tableBody.querySelectorAll('tr').forEach((row) => {
                    const qty = toNumber(row.querySelector('.qty-input').value);
                    const price = toNumber(row.querySelector('.price-input').value);
                    recalculateRow(row);
                    netto += toNumber(row.querySelector('.total-input').value);
                });

                const dpp = netto;
                const ppn = dpp * 0.11;
                const grandtotal = dpp + ppn;

                document.getElementById('netto').value = netto.toFixed(2);
                document.getElementById('dpp').value = dpp.toFixed(2);
                document.getElementById('ppn').value = ppn.toFixed(2);
                document.getElementById('grandtotal').value = grandtotal.toFixed(2);
            }

            function bindRowEvents(row) {
                row.querySelector('.item-select').addEventListener('change', (event) => {
                    const option = event.target.selectedOptions[0];
                    if (option && option.dataset.price) {
                        row.querySelector('.price-input').value = option.dataset.price;
                        const text = option.textContent || '';
                        const parts = text.split(' - ');
                        row.querySelector('.desc-input').value = parts.length > 1 ? parts.slice(1).join(' - ') : text;
                    }
                    recalculateSummary();
                });

                row.querySelectorAll('.qty-input, .price-input, .disc-percent-input, .cash-disc-input').forEach((input) => {
                    input.addEventListener('input', recalculateSummary);
                });

                row.querySelector('.add-row-inline').addEventListener('click', () => {
                    const clone = template.content.firstElementChild.cloneNode(true);
                    tableBody.appendChild(clone);
                    bindRowEvents(clone);
                    recalculateSummary();
                });

                row.querySelector('.remove-row').addEventListener('click', () => {
                    if (tableBody.querySelectorAll('tr').length > 1) {
                        row.remove();
                        recalculateSummary();
                    }
                });
            }

            customerSearchInput.addEventListener('change', resolveCustomerId);
            customerSearchInput.addEventListener('blur', resolveCustomerId);

            tableBody.querySelectorAll('tr').forEach(bindRowEvents);
            recalculateSummary();
            resolveCustomerId();

            form.addEventListener('submit', (event) => {
                const foundCustomer = resolveCustomerId();
                if (!foundCustomer) {
                    event.preventDefault();
                    if (confirm('Data customer tidak ditemukan. Add data customer?')) {
                        window.location.href = '/test-krida/customers/form.php';
                    }
                    return;
                }
                recalculateSummary();
            });
        </script>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
