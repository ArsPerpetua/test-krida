<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = isset($_POST['custId']) ? (int) $_POST['custId'] : 0;
$nama = trim($_POST['cust_nama'] ?? '');
$alamat = trim($_POST['cust_alamat'] ?? '');
$hp = trim($_POST['cust_hp'] ?? '');

if ($nama === '' || $alamat === '' || $hp === '') {
    set_flash('error', 'Semua field customer wajib diisi.');
    redirect('/test-krida/customers/index.php');
}

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE customers SET cust_nama = ?, cust_alamat = ?, cust_hp = ? WHERE custId = ?");
    $stmt->bind_param('sssi', $nama, $alamat, $hp, $id);
    $message = 'Data customer berhasil diperbarui.';
} else {
    $nextId = get_next_available_id($conn, 'customers', 'custId');
    $stmt = $conn->prepare("INSERT INTO customers (custId, cust_nama, cust_alamat, cust_hp) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $nextId, $nama, $alamat, $hp);
    $message = 'Data customer berhasil ditambahkan.';
}

$stmt->execute();
$stmt->close();

set_flash('success', $message);
redirect('/test-krida/customers/index.php');
