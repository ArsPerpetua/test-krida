<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbName = 'sales_order_app';

$conn = new mysqli($host, $user, $pass, $dbName);

if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
