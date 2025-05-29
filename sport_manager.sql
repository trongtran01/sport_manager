-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 29, 2025 lúc 06:20 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `sport_manager`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `bookings`
--

INSERT INTO `bookings` (`id`, `field_id`, `customer_name`, `customer_phone`, `date`, `start_time`, `end_time`, `created_at`) VALUES
(1, 1, 'Việt Hưng', '0912345678', '2025-05-29', '06:00:00', '07:30:00', '2025-05-29 15:28:52'),
(2, 4, 'Việt Hưng', '0912345678', '2025-05-29', '06:00:00', '07:30:00', '2025-05-29 15:29:12'),
(3, 1, 'Việt Hưng 2', '0912345678', '2025-05-29', '12:00:00', '13:30:00', '2025-05-29 15:37:07'),
(4, 1, 'Việt Hưng', '0912345678', '2025-05-29', '07:30:00', '09:00:00', '2025-05-29 15:50:22'),
(5, 1, 'Đèo Việt Hưng', '0123456789', '2025-05-29', '09:00:00', '10:30:00', '2025-05-29 16:11:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `fields`
--

CREATE TABLE `fields` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('vip','thường') NOT NULL DEFAULT 'thường',
  `open_time` time NOT NULL DEFAULT '06:00:00',
  `close_time` time NOT NULL DEFAULT '22:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `fields`
--

INSERT INTO `fields` (`id`, `name`, `type`, `open_time`, `close_time`) VALUES
(1, 'Sân A', 'vip', '06:00:00', '22:00:00'),
(2, 'Sân B', 'thường', '06:00:00', '22:00:00'),
(3, 'Sân C', 'thường', '06:00:00', '22:00:00'),
(4, 'Sân D', 'vip', '06:00:00', '22:00:00'),
(5, 'Sân E', 'thường', '06:00:00', '22:00:00'),
(6, 'Sân F', 'thường', '06:00:00', '22:00:00');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `field_id` (`field_id`);

--
-- Chỉ mục cho bảng `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
