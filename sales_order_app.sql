-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Bulan Mei 2026 pada 05.01
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sales_order_app`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `custId` int(11) NOT NULL,
  `cust_nama` varchar(100) NOT NULL,
  `cust_alamat` text NOT NULL,
  `cust_hp` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`custId`, `cust_nama`, `cust_alamat`, `cust_hp`) VALUES
(1, 'Andra', 'Pagesangan', '087292938283'),
(2, 'Asrul', 'Lombok Timur', '082382382322');

-- --------------------------------------------------------

--
-- Struktur dari tabel `items`
--

CREATE TABLE `items` (
  `itemId` int(11) NOT NULL,
  `deskripsi` varchar(150) NOT NULL,
  `price` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `items`
--

INSERT INTO `items` (`itemId`, `deskripsi`, `price`) VALUES
(1, 'Pensil', 2000.00),
(2, 'Pulpen', 3000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `orderitem`
--

CREATE TABLE `orderitem` (
  `orderItemId` int(11) NOT NULL,
  `orderId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `qty` decimal(14,2) NOT NULL DEFAULT 0.00,
  `price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discAmount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `totalItem` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `orderitem`
--

INSERT INTO `orderitem` (`orderItemId`, `orderId`, `itemId`, `qty`, `price`, `discAmount`, `totalItem`) VALUES
(9, 4, 1, 5.00, 2000.00, 18000.00, 0.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `orderId` int(11) NOT NULL,
  `orderNo` varchar(30) NOT NULL,
  `orderDate` date NOT NULL,
  `custId` int(11) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discAmount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `netto` decimal(14,2) NOT NULL DEFAULT 0.00,
  `dpp` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ppn` decimal(14,2) NOT NULL DEFAULT 0.00,
  `grandtotal` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`orderId`, `orderNo`, `orderDate`, `custId`, `subtotal`, `discAmount`, `netto`, `dpp`, `ppn`, `grandtotal`) VALUES
(4, 'KW001', '2023-01-21', 2, 10000.00, 18000.00, 0.00, 0.00, 0.00, 0.00);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`custId`),
  ADD KEY `idx_customers_name` (`cust_nama`);

--
-- Indeks untuk tabel `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`itemId`),
  ADD KEY `idx_items_deskripsi` (`deskripsi`);

--
-- Indeks untuk tabel `orderitem`
--
ALTER TABLE `orderitem`
  ADD PRIMARY KEY (`orderItemId`),
  ADD KEY `idx_orderitem_order` (`orderId`),
  ADD KEY `idx_orderitem_item` (`itemId`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderId`),
  ADD UNIQUE KEY `uq_orders_order_no` (`orderNo`),
  ADD KEY `idx_orders_order_date` (`orderDate`),
  ADD KEY `idx_orders_customer` (`custId`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `customers`
--
ALTER TABLE `customers`
  MODIFY `custId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `items`
--
ALTER TABLE `items`
  MODIFY `itemId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `orderitem`
--
ALTER TABLE `orderitem`
  MODIFY `orderItemId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `orderId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `orderitem`
--
ALTER TABLE `orderitem`
  ADD CONSTRAINT `fk_orderitem_item` FOREIGN KEY (`itemId`) REFERENCES `items` (`itemId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orderitem_order` FOREIGN KEY (`orderId`) REFERENCES `orders` (`orderId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`custId`) REFERENCES `customers` (`custId`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
