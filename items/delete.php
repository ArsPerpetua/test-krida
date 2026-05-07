<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    set_flash('error', 'Item tidak ditemukan.');
    redirect('/test-krida/items/index.php');
}

$stmt = $conn->prepare("DELETE FROM items WHERE itemId = ?");
$stmt->bind_param('i', $id);

try {
    $stmt->execute();
    set_flash('success', 'Data item berhasil dihapus.');
} catch (mysqli_sql_exception $e) {
    set_flash('error', 'Item tidak bisa dihapus karena sudah dipakai di transaksi.');
}

$stmt->close();
redirect('/test-krida/items/index.php');
