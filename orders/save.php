<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$orderId = isset($_POST['orderId']) ? (int) $_POST['orderId'] : 0;
$orderNo = trim($_POST['orderNo'] ?? '');
$orderDate = trim($_POST['orderDate'] ?? '');
$custId = isset($_POST['custId']) ? (int) $_POST['custId'] : 0;
$itemIds = $_POST['itemId'] ?? [];
$qtyList = $_POST['qty'] ?? [];
$priceList = $_POST['price'] ?? [];
$itemDiscPercentList = $_POST['itemDiscPercent'] ?? [];
$itemDiscList = $_POST['itemDisc'] ?? [];

if ($orderNo === '' || $orderDate === '' || $custId <= 0 || empty($itemIds)) {
    set_flash('error', 'Header sales order belum lengkap.');
    redirect('/test-krida/orders/index.php');
}

$details = [];
$subtotal = 0;
$itemDiscTotal = 0;

for ($i = 0; $i < count($itemIds); $i++) {
    $itemId = (int) ($itemIds[$i] ?? 0);
    $qty = sanitize_number($qtyList[$i] ?? 0);
    $price = sanitize_number($priceList[$i] ?? 0);
    $discPercent = sanitize_number($itemDiscPercentList[$i] ?? 0);
    $cashDisc = sanitize_number($itemDiscList[$i] ?? 0);

    if ($itemId <= 0 || $qty <= 0 || $price < 0 || $discPercent < 0 || $cashDisc < 0) {
        continue;
    }

    $lineSubtotal = $qty * $price;
    $percentDiscAmount = $lineSubtotal * (min(max($discPercent, 0), 100) / 100);
    $disc = $percentDiscAmount + $cashDisc;
    $totalItem = max($lineSubtotal - $disc, 0);
    $subtotal += $lineSubtotal;
    $itemDiscTotal += $disc;

    $details[] = [
        'itemId' => $itemId,
        'qty' => $qty,
        'price' => $price,
        'discAmount' => $disc,
        'totalItem' => $totalItem,
    ];
}

if (empty($details)) {
    set_flash('error', 'Minimal harus ada satu detail item yang valid.');
    redirect('/test-krida/orders/index.php');
}

$discAmount = $itemDiscTotal;
$netto = max($subtotal - $discAmount, 0);
$dpp = $netto;
$ppn = $dpp * 0.11;
$grandtotal = $dpp + $ppn;

$checkSql = "SELECT orderId FROM orders WHERE orderNo = ?" . ($orderId > 0 ? " AND orderId <> ?" : "");
$checkStmt = $conn->prepare($checkSql);

if ($orderId > 0) {
    $checkStmt->bind_param('si', $orderNo, $orderId);
} else {
    $checkStmt->bind_param('s', $orderNo);
}

$checkStmt->execute();
$exists = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if ($exists) {
    set_flash('error', 'Nomor order sudah digunakan. Gunakan nomor lain.');
    redirect('/test-krida/orders/index.php');
}

if ($orderId <= 0) {
    $orderNo = generate_order_no($conn);
}

$conn->begin_transaction();

try {
    if ($orderId > 0) {
        $stmt = $conn->prepare("
            UPDATE orders
            SET orderNo = ?, orderDate = ?, custId = ?, subtotal = ?, discAmount = ?, netto = ?, dpp = ?, ppn = ?, grandtotal = ?
            WHERE orderId = ?
        ");
        $stmt->bind_param('ssiddddddi', $orderNo, $orderDate, $custId, $subtotal, $discAmount, $netto, $dpp, $ppn, $grandtotal, $orderId);
        $stmt->execute();
        $stmt->close();

        $deleteDetail = $conn->prepare("DELETE FROM orderItem WHERE orderId = ?");
        $deleteDetail->bind_param('i', $orderId);
        $deleteDetail->execute();
        $deleteDetail->close();
        $savedOrderId = $orderId;
        $message = 'Sales order berhasil diperbarui.';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO orders (orderNo, orderDate, custId, subtotal, discAmount, netto, dpp, ppn, grandtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('ssidddddd', $orderNo, $orderDate, $custId, $subtotal, $discAmount, $netto, $dpp, $ppn, $grandtotal);
        $stmt->execute();
        $savedOrderId = $stmt->insert_id;
        $stmt->close();
        $message = 'Sales order berhasil ditambahkan.';
    }

    $detailStmt = $conn->prepare("
        INSERT INTO orderItem (orderId, itemId, qty, price, discAmount, totalItem)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($details as $detail) {
        $detailStmt->bind_param(
            'iidddd',
            $savedOrderId,
            $detail['itemId'],
            $detail['qty'],
            $detail['price'],
            $detail['discAmount'],
            $detail['totalItem']
        );
        $detailStmt->execute();
    }

    $detailStmt->close();
    $conn->commit();
    set_flash('success', $message);
} catch (Throwable $e) {
    $conn->rollback();
    set_flash('error', 'Gagal menyimpan sales order: ' . $e->getMessage());
}

redirect('/test-krida/orders/index.php');
