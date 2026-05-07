<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = isset($_POST['itemId']) ? (int) $_POST['itemId'] : 0;
$deskripsi = trim($_POST['deskripsi'] ?? '');
$price = sanitize_number($_POST['price'] ?? 0);

if ($deskripsi === '' || $price < 0) {
    set_flash('error', 'Data item tidak valid.');
    redirect('/test-krida/items/index.php');
}

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE items SET deskripsi = ?, price = ? WHERE itemId = ?");
    $stmt->bind_param('sdi', $deskripsi, $price, $id);
    $message = 'Data item berhasil diperbarui.';
} else {
    $nextId = get_next_available_id($conn, 'items', 'itemId');
    $stmt = $conn->prepare("INSERT INTO items (itemId, deskripsi, price) VALUES (?, ?, ?)");
    $stmt->bind_param('isd', $nextId, $deskripsi, $price);
    $message = 'Data item berhasil ditambahkan.';
}

$stmt->execute();
$stmt->close();

set_flash('success', $message);
redirect('/test-krida/items/index.php');
