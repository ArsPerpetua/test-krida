CREATE DATABASE IF NOT EXISTS sales_order_app
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE sales_order_app;

CREATE TABLE IF NOT EXISTS customers (
  custId INT AUTO_INCREMENT PRIMARY KEY,
  cust_nama VARCHAR(100) NOT NULL,
  cust_alamat TEXT NOT NULL,
  cust_hp VARCHAR(30) NOT NULL,
  INDEX idx_customers_name (cust_nama)
);

CREATE TABLE IF NOT EXISTS items (
  itemId INT AUTO_INCREMENT PRIMARY KEY,
  deskripsi VARCHAR(150) NOT NULL,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  INDEX idx_items_deskripsi (deskripsi)
);

CREATE TABLE IF NOT EXISTS orders (
  orderId INT AUTO_INCREMENT PRIMARY KEY,
  orderNo VARCHAR(30) NOT NULL,
  orderDate DATE NOT NULL,
  custId INT NOT NULL,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  discAmount DECIMAL(14,2) NOT NULL DEFAULT 0,
  netto DECIMAL(14,2) NOT NULL DEFAULT 0,
  dpp DECIMAL(14,2) NOT NULL DEFAULT 0,
  ppn DECIMAL(14,2) NOT NULL DEFAULT 0,
  grandtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_orders_order_no (orderNo),
  INDEX idx_orders_order_date (orderDate),
  INDEX idx_orders_customer (custId),
  CONSTRAINT fk_orders_customer
    FOREIGN KEY (custId) REFERENCES customers(custId)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS orderItem (
  orderItemId INT AUTO_INCREMENT PRIMARY KEY,
  orderId INT NOT NULL,
  itemId INT NOT NULL,
  qty DECIMAL(14,2) NOT NULL DEFAULT 0,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  discAmount DECIMAL(14,2) NOT NULL DEFAULT 0,
  totalItem DECIMAL(14,2) NOT NULL DEFAULT 0,
  INDEX idx_orderitem_order (orderId),
  INDEX idx_orderitem_item (itemId),
  CONSTRAINT fk_orderitem_order
    FOREIGN KEY (orderId) REFERENCES orders(orderId)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_orderitem_item
    FOREIGN KEY (itemId) REFERENCES items(itemId)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);
