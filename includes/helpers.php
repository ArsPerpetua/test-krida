<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function old(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

function format_rupiah($number): string
{
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

function sanitize_number($value): float
{
    return (float) str_replace(',', '.', (string) $value);
}

function format_running_code(string $prefix, int $id, int $length = 3): string
{
    return $prefix . str_pad((string) $id, $length, '0', STR_PAD_LEFT);
}

function extract_code_number(string $value): string
{
    $digits = preg_replace('/\D+/', '', $value);
    return $digits !== '' ? $digits : trim($value);
}

function get_next_available_id(mysqli $conn, string $table, string $column): int
{
    $allowed = [
        'customers' => 'custId',
        'items' => 'itemId',
    ];

    if (!isset($allowed[$table]) || $allowed[$table] !== $column) {
        throw new InvalidArgumentException('Table or column is not allowed.');
    }

    $result = $conn->query("SELECT $column FROM $table ORDER BY $column ASC");
    $nextId = 1;

    while ($row = $result->fetch_assoc()) {
        $currentId = (int) $row[$column];
        if ($currentId !== $nextId) {
            break;
        }
        $nextId++;
    }

    return $nextId;
}

function generate_order_no(mysqli $conn): string
{
    $prefix = 'KW';
    $result = $conn->query("
        SELECT orderNo
        FROM orders
        WHERE orderNo LIKE 'KW%'
        ORDER BY CAST(SUBSTRING(orderNo, 3) AS UNSIGNED) ASC
    ");

    $running = 1;

    while ($row = $result->fetch_assoc()) {
        $current = (int) substr($row['orderNo'], 2);
        if ($current !== $running) {
            break;
        }
        $running++;
    }

    return format_running_code($prefix, $running);
}
