-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2024 at 11:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `online_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `pembeli`
--

CREATE TABLE `pembeli` (
  `id_pembeli` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembeli`
--

INSERT INTO `pembeli` (`id_pembeli`, `nama_lengkap`, `email`, `no_hp`, `alamat`, `created_at`) VALUES
(1, 'edwin', 'Jundy.alkr@gmail.com', '081366931334', '5yuyuk', '2024-12-07 08:40:19'),
(2, 'edwin', 'Jundy.alkr@gmail.com', '081366931334', '5yuyuk', '2024-12-07 08:40:19'),
(3, 'edwin', 'Jundy.alkr@gmail.com', '081366931334', 'ytdku', '2024-12-07 08:41:20'),
(4, 'edwin', 'Jundy.alkr@gmail.com', '081366931334', 'ytdku', '2024-12-07 08:41:20'),
(5, 'edwin', 'Jundy.alkr@gmail.com', '081366931334', 'dhfjgklh', '2024-12-07 08:49:10'),
(6, 'edwin', 'Jundy.alkr@gmail.com', '081366931334', 'dhfjgklh', '2024-12-07 08:49:10'),
(7, 'edwin', 'Jundy.alkr@gmail.com', '081366931334', 'c', '2024-12-07 09:41:22'),
(8, 'edwin', 'Jundy.alkr@gmail.com', '081366931334', 'tfyiu', '2024-12-07 10:24:17'),
(9, 'edwin', 'Jundy.alkr@gmail.com', '081366931334', 'qqrwtyeuriy', '2024-12-07 10:24:44'),
(10, 'edwin', 'Jundy.alkr@gmail.com', '081366931334', 'yiuil', '2024-12-07 10:25:15'),
(11, 'edwin', 'Jundy.alkr@gmail.com', '081366931334', 'ujkl;l', '2024-12-07 10:25:38'),
(12, 'sfdgdhjy', 'Jundy.alkr@gmail.com', '081366931334', 'dhxjfhk', '2024-12-07 10:26:08'),
(13, 'sfdgdhjy', 'Jundy.alkr@gmail.com', '081366931334', 'dhxjfhk', '2024-12-07 10:26:08'),
(14, 'sfdgdhjy', 'Jundy.alkr@gmail.com', '081366931334', 'bgnh', '2024-12-08 09:57:29'),
(15, 'sfdgdhjy', 'Jundy.alkr@gmail.com', '081366931334', 'bgnh', '2024-12-08 09:57:29'),
(16, 'sfdgdhjy', 'Jundy.alkr@gmail.com', 'ughi', 'jkjk', '2024-12-08 10:00:57'),
(17, 'sfdgdhjy', 'Jundy.alkr@gmail.com', 'ughi', 'jkjk', '2024-12-08 10:00:57'),
(18, 'sfdgdhjy', 'Jundy.alkr@gmail.com', 'ughi', 'grhetsjrydku', '2024-12-08 10:04:38'),
(19, 'sfdgdhjy', 'Jundy.alkr@gmail.com', 'ughi', 'grhetsjrydku', '2024-12-08 10:04:38'),
(20, 'sfdgdhjy', 'Jundy.alkr@gmail.com', 'ughi', 'u', '2024-12-08 10:06:55'),
(21, 'sfdgdhjy', 'Jundy.alkr@gmail.com', 'ughi', 'u', '2024-12-08 10:06:55');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `jasa_kirim` varchar(50) NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `status_pembayaran` enum('belum_bayar','sudah_bayar') DEFAULT 'belum_bayar',
  `informasi_pembayaran` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_produk`, `id_pembeli`, `jumlah`, `total_harga`, `jasa_kirim`, `metode_pembayaran`, `status_pembayaran`, `informasi_pembayaran`, `created_at`) VALUES
(1, 1, 1, 1, 150000.00, 'sicepat', 'transfer_mandiri', 'belum_bayar', NULL, '2024-12-07 08:40:19'),
(2, 1, 2, 1, 150000.00, 'sicepat', 'transfer_mandiri', 'belum_bayar', NULL, '2024-12-07 08:40:19'),
(3, 8, 3, 2, 160000.00, 'pos', 'transfer_bca', 'belum_bayar', NULL, '2024-12-07 08:41:20'),
(4, 8, 4, 2, 160000.00, 'pos', 'transfer_bca', 'belum_bayar', NULL, '2024-12-07 08:41:20'),
(5, 8, 5, 8, 640000.00, 'jne', 'transfer_mandiri', 'belum_bayar', NULL, '2024-12-07 08:49:10'),
(6, 8, 6, 8, 640000.00, 'jne', 'transfer_mandiri', 'belum_bayar', NULL, '2024-12-07 08:49:10'),
(7, 8, 7, 3, 240000.00, 'pos', 'transfer_mandiri', 'belum_bayar', NULL, '2024-12-07 09:41:22'),
(8, 8, 8, 4, 320000.00, 'jne', 'transfer_bca', 'belum_bayar', NULL, '2024-12-07 10:24:17'),
(9, 8, 9, 1, 80000.00, 'pos', 'transfer_bca', 'belum_bayar', NULL, '2024-12-07 10:24:44'),
(10, 8, 10, 1, 80000.00, 'sicepat', 'transfer_bca', 'belum_bayar', NULL, '2024-12-07 10:25:15'),
(11, 8, 11, 1, 80000.00, 'jne', 'transfer_mandiri', 'belum_bayar', NULL, '2024-12-07 10:25:38'),
(12, 8, 12, 3, 240000.00, 'pos', 'transfer_bca', 'belum_bayar', NULL, '2024-12-07 10:26:08'),
(13, 8, 13, 3, 240000.00, 'pos', 'transfer_bca', 'belum_bayar', NULL, '2024-12-07 10:26:08'),
(14, 2, 14, 2, 500000.00, 'jne', 'dana', 'belum_bayar', NULL, '2024-12-08 09:57:29'),
(15, 2, 15, 2, 500000.00, 'jne', 'dana', 'belum_bayar', NULL, '2024-12-08 09:57:29'),
(16, 2, 16, 2, 500000.00, 'jne', 'gopay', 'belum_bayar', NULL, '2024-12-08 10:00:57'),
(17, 2, 17, 2, 500000.00, 'jne', 'gopay', 'belum_bayar', NULL, '2024-12-08 10:00:57'),
(18, 2, 18, 2, 500000.00, 'pos', 'gopay', 'belum_bayar', NULL, '2024-12-08 10:04:38'),
(19, 2, 19, 2, 500000.00, 'pos', 'gopay', 'belum_bayar', NULL, '2024-12-08 10:04:38'),
(20, 2, 20, 2, 500000.00, 'jne', 'gopay', 'belum_bayar', NULL, '2024-12-08 10:06:55'),
(21, 2, 21, 2, 500000.00, 'jne', 'gopay', 'belum_bayar', NULL, '2024-12-08 10:06:55');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL,
  `spesifikasi` text DEFAULT NULL,
  `jumlah_terjual` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gambar_produk` varchar(255) DEFAULT NULL,
  `is_flash_sale` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `harga`, `stok`, `spesifikasi`, `jumlah_terjual`, `created_at`, `gambar_produk`, `is_flash_sale`) VALUES
(1, 'Cute Teddy Bear Shirt', 150000.00, 49, 'Soft cotton t-shirt with teddy bear print', 25, '2024-12-07 06:21:51', 'sepatu.jpg', 1),
(2, 'Rainbow Unicorn Dress', 250000.00, 22, 'Colorful dress with unicorn pattern', 15, '2024-12-07 06:21:51', 'sepatu1.jpg', 1),
(3, 'Dinosaur Pajama Set', 180000.00, 40, 'Comfortable pajama set with dinosaur design', 20, '2024-12-07 06:21:51', 'sepatu2.jpg', 0),
(4, 'Butterfly Hair Clips Set', 50000.00, 100, 'Set of 5 colorful butterfly hair clips', 50, '2024-12-07 06:21:51', NULL, 0),
(5, 'Space Rocket Backpack', 200000.00, 25, 'Durable backpack with space rocket design', 10, '2024-12-07 06:21:51', NULL, 0),
(6, 'Mermaid Swimsuit', 175000.00, 35, 'One-piece swimsuit with mermaid scale pattern', 30, '2024-12-07 06:21:51', NULL, 0),
(7, 'Superhero Cape and Mask Set', 120000.00, 60, 'Dress-up set with cape and mask', 40, '2024-12-07 06:21:51', '', 0),
(8, 'Animal Friends Socks Pack', 80000.00, 52, 'Pack of 5 pairs of socks with cute animal designs', 55, '2024-12-07 06:21:51', 'sepatu3.jpg', 1),
(9, 'Princess Tiara Headband', 70000.00, 45, 'Sparkly tiara headband for little princesses', 35, '2024-12-07 06:21:51', NULL, 0),
(10, 'Pirate Adventure Playset', 300000.00, 20, 'Complete pirate-themed playset with accessories', 5, '2024-12-07 06:21:51', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `otp`, `created_at`) VALUES
(1, 'Ahmad Jundy', 'edwin@gmail.com', '$2y$10$8GcY73IUWhn1Asd0fS2Fe.LQLFXqGFqJMV4y/S5a8Fp.Ux5PGnUMS', 'Ahmad Jundy', NULL, '2024-12-07 04:48:03'),
(2, 'anjay', 'jundy.kr@gmail.com', '$2y$10$PPUcTPs0YqtDrZE2WN9.gOiRnLXi3ebhMdxvCxwkYePDRexfsNs/2', 'anjay', NULL, '2024-12-07 08:48:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pembeli`
--
ALTER TABLE `pembeli`
  ADD PRIMARY KEY (`id_pembeli`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_pembeli` (`id_pembeli`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pembeli`
--
ALTER TABLE `pembeli`
  MODIFY `id_pembeli` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`),
  ADD CONSTRAINT `pesanan_ibfk_2` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
