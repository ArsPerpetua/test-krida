<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    set_flash('error', 'Customer tidak ditemukan.');
    redirect('/test-krida/customers/index.php');
}

$stmt = $conn->prepare("DELETE FROM customers WHERE custId = ?");
$stmt->bind_param('i', $id);

try {
    $stmt->execute();
    set_flash('success', 'Data customer berhasil dihapus.');
} catch (mysqli_sql_exception $e) {
    set_flash('error', 'Customer tidak bisa dihapus karena sudah dipakai di transaksi.');
}

$stmt->close();
redirect('/test-krida/customers/index.php');
