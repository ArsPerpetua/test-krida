<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    set_flash('error', 'Sales order tidak ditemukan.');
    redirect('/test-krida/orders/index.php');
}

$stmt = $conn->prepare("DELETE FROM orders WHERE orderId = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

set_flash('success', 'Sales order berhasil dihapus.');
redirect('/test-krida/orders/index.php');
