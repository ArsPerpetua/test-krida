<?php
require_once __DIR__ . '/helpers.php';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Order App</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: #fff;
            color: #111;
        }
        .container {
            width: min(1000px, calc(100% - 32px));
            margin: 24px auto 40px;
        }
        .btn {
            text-decoration: none;
            border: 1px solid #000;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 0;
            font-size: 14px;
            background: #fff;
            color: #111;
        }
        .card {
            background: #fff;
            border: 1px solid #000;
            padding: 20px;
            margin-top: 20px;
        }
        .card h2, .card h3 {
            margin-top: 0;
        }
        .grid {
            display: grid;
            gap: 16px;
        }
        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #000;
            border-radius: 0;
            font: inherit;
            background: #fff;
        }
        textarea { min-height: 96px; resize: vertical; }
        .btn-primary, .btn-warning, .btn-danger, .btn-light { background: #fff; color: #111; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            padding: 12px 10px;
            border: 1px solid #000;
            text-align: left;
            vertical-align: top;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .alert {
            margin-top: 20px;
            padding: 14px 16px;
            border: 1px solid #000;
        }
        .alert-success, .alert-error { background: #fff; color: #111; }
        .summary {
            width: min(420px, 100%);
            margin-left: auto;
        }
        .summary td:first-child {
            font-weight: 600;
            width: 50%;
        }
        .text-right { text-align: right; }
        .muted { color: #444; }
        .empty {
            padding: 18px;
            border: 1px solid #000;
            color: #444;
        }
        .home-title {
            text-align: center;
            margin: 40px 0 24px;
            font-size: 28px;
            font-weight: 600;
        }
        .home-groups {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        .home-group-title {
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            padding-bottom: 8px;
            border-bottom: 1px solid #000;
        }
        .home-group-title a {
            color: #111;
            text-decoration: none;
            display: block;
        }
        .home-menu-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .home-menu {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 260px;
        }
        .home-menu a {
            color: #111;
            text-decoration: none;
            border: 1px solid #000;
            padding: 10px 12px;
            background: #fff;
        }
        @media (max-width: 720px) {
            .container { width: min(100% - 20px, 1100px); }
            .home-groups { grid-template-columns: 1fr; }
            .home-menu { width: 100%; }
            th:nth-child(3), td:nth-child(3),
            th:nth-child(4), td:nth-child(4) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>
