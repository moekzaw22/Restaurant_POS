-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2026 at 11:25 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `order_detail`
--

CREATE TABLE `order_detail` (
  `Order_Detail_ID` int(11) NOT NULL,
  `Order_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `Note` text DEFAULT NULL,
  `Product_Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_detail`
--

INSERT INTO `order_detail` (`Order_Detail_ID`, `Order_ID`, `Product_ID`, `Quantity`, `Note`, `Product_Price`) VALUES
(1, 20, 1, 1, '', 2500.00),
(2, 20, 2, 1, '', 1300.00),
(3, 21, 1, 1, '', 2500.00),
(4, 20, 1, 1, '', 2500.00),
(5, 22, 1, 1, '', 2500.00),
(6, 22, 2, 1, '', 1300.00),
(12, 25, 3, 1, '', 300.00),
(13, 25, 1, 1, '', 2500.00),
(14, 25, 1, 1, '', 2500.00),
(15, 25, 1, 1, '', 2500.00),
(16, 25, 1, 1, '', 2500.00),
(17, 25, 3, 1, '', 300.00),
(18, 25, 3, 1, '', 300.00),
(19, 25, 3, 1, '', 300.00),
(20, 24, 1, 1, '+Extra Cheese', 2800.00),
(21, 24, 1, 1, '+Extra Cheese +Extra Egg', 3200.00),
(22, 26, 1, 1, '+Extra Cheese', 2800.00),
(23, 25, 1, 1, '', 2600.00),
(24, 27, 1, 1, '+Extra Egg', 2800.00),
(25, 28, 1, 2, '', 2400.00),
(26, 29, 1, 1, '', 2400.00),
(27, 29, 2, 1, '', 1400.00),
(28, 30, 1, 1, '', 2400.00),
(29, 31, 1, 1, '', 2400.00),
(30, 31, 2, 1, '', 1400.00),
(31, 32, 1, 1, '', 2400.00),
(32, 33, 1, 1, '+Extra Cheese +Extra Egg', 3200.00),
(33, 34, 1, 1, '', 2400.00),
(34, 34, 1, 1, '+Extra Cheese', 2800.00),
(35, 35, 1, 1, '+Extra Cheese', 2800.00),
(36, 36, 1, 1, '+Extra Cheese +Extra Egg', 3200.00),
(37, 36, 1, 1, '+Extra Cheese', 2800.00),
(38, 37, 1, 1, '', 2400.00),
(39, 38, 1, 1, '+Extra Cheese', 2800.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_r`
--

CREATE TABLE `order_r` (
  `Order_ID` int(11) NOT NULL,
  `Table_ID` int(11) NOT NULL,
  `Order_Date` date DEFAULT current_timestamp(),
  `Order_Open` time NOT NULL,
  `Order_Close` time NOT NULL,
  `Status` enum('Open','Paid','Cancelled') DEFAULT 'Open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_r`
--

INSERT INTO `order_r` (`Order_ID`, `Table_ID`, `Order_Date`, `Order_Open`, `Order_Close`, `Status`) VALUES
(20, 1, '2026-02-18', '00:00:00', '00:00:00', ''),
(21, 2, '2026-02-18', '00:00:00', '00:00:00', ''),
(22, 3, '2026-02-18', '00:00:00', '00:00:00', ''),
(23, 4, '2026-02-18', '00:00:00', '00:00:00', 'Paid'),
(24, 2, '2026-02-18', '00:00:00', '00:00:00', 'Paid'),
(25, 3, '2026-02-18', '00:00:00', '08:11:34', 'Paid'),
(26, 1, '2026-02-23', '16:16:21', '17:16:59', 'Paid'),
(27, 1, '2026-02-26', '08:14:36', '08:14:57', 'Paid'),
(28, 1, '2026-02-26', '08:29:58', '08:30:06', 'Paid'),
(29, 1, '2026-02-26', '08:31:00', '08:31:11', 'Paid'),
(30, 1, '2026-02-26', '08:36:17', '08:36:53', 'Paid'),
(31, 1, '2026-02-26', '15:49:22', '15:49:30', 'Paid'),
(32, 5, '2026-02-28', '10:27:07', '10:27:15', 'Paid'),
(33, 1, '2026-03-01', '22:44:33', '22:44:51', 'Paid'),
(34, 1, '2026-03-01', '22:44:58', '10:55:49', 'Paid'),
(35, 1, '2026-04-19', '20:03:29', '20:02:46', 'Paid'),
(36, 1, '2026-06-14', '22:24:00', '19:46:55', 'Paid'),
(37, 3, '2026-10-13', '19:47:11', '19:47:59', 'Paid'),
(38, 1, '2026-10-13', '19:59:13', '00:04:59', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Payment_ID` int(11) NOT NULL,
  `Order_ID` int(11) NOT NULL,
  `Payment_total` int(11) NOT NULL,
  `Method` enum('Cash','Card') NOT NULL,
  `Payment_Date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`Payment_ID`, `Order_ID`, `Payment_total`, `Method`, `Payment_Date`) VALUES
(1, 21, 2500, 'Cash', '2026-02-18 13:00:24'),
(2, 22, 3800, 'Cash', '2026-02-18 13:04:56'),
(3, 23, 0, 'Cash', '2026-02-18 13:06:14'),
(4, 24, 6000, 'Cash', '2026-02-23 16:01:08'),
(5, 25, 13800, 'Cash', '2026-02-26 08:11:34'),
(6, 26, 2800, 'Cash', '2026-02-23 16:16:59'),
(7, 27, 2800, 'Cash', '2026-02-26 08:14:57'),
(8, 28, 4800, 'Cash', '2026-02-26 08:30:06'),
(9, 29, 3800, 'Cash', '2026-02-26 08:31:11'),
(10, 30, 2400, 'Cash', '2026-02-26 08:36:53'),
(11, 31, 3800, 'Cash', '2026-02-26 15:49:30'),
(12, 32, 2400, 'Cash', '2026-02-28 10:27:15'),
(13, 33, 3200, 'Cash', '2026-03-01 22:44:51'),
(14, 34, 5200, 'Cash', '2026-03-18 10:55:49'),
(15, 35, 2800, 'Cash', '2026-05-15 20:02:46'),
(16, 36, 6000, 'Cash', '2026-10-13 19:46:55'),
(17, 37, 2400, 'Cash', '2026-10-13 19:47:59'),
(18, 38, 2800, 'Cash', '2026-10-14 00:04:59');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `Product_ID` int(11) NOT NULL,
  `Product_Name` varchar(200) NOT NULL,
  `Product_Code` varchar(100) NOT NULL,
  `Item_Price` int(11) NOT NULL,
  `Ingredient_stock` int(11) NOT NULL,
  `Food_Type` varchar(50) NOT NULL DEFAULT 'Other'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`Product_ID`, `Product_Name`, `Product_Code`, `Item_Price`, `Ingredient_stock`, `Food_Type`) VALUES
(1, 'Sandwich ', 'SW01', 2300, 1, 'Fastfood'),
(2, 'Coca Cola ', 'CC01', 1600, 0, 'Can'),
(3, 'Extra Cheese', 'EXCE01', 400, 0, 'Extra'),
(4, 'Extra Egg', 'EXEGG', 400, 0, 'Extra'),
(5, 'Burger', 'BR01', 4000, 0, 'Fastfood'),
(6, 'Fried Rice ', 'FR01', 6000, 0, 'Meal');

-- --------------------------------------------------------

--
-- Table structure for table `tables_assignment`
--

CREATE TABLE `tables_assignment` (
  `Table_ID` int(11) NOT NULL,
  `Status` enum('Available','Occupied') DEFAULT 'Available',
  `Assign_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tables_assignment`
--

INSERT INTO `tables_assignment` (`Table_ID`, `Status`, `Assign_order`) VALUES
(1, 'Available', NULL),
(2, 'Available', NULL),
(3, 'Available', NULL),
(4, 'Available', NULL),
(5, 'Available', NULL),
(6, 'Available', NULL),
(7, 'Available', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `order_detail`
--
ALTER TABLE `order_detail`
  ADD PRIMARY KEY (`Order_Detail_ID`),
  ADD KEY `Order_ID` (`Order_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `order_r`
--
ALTER TABLE `order_r`
  ADD PRIMARY KEY (`Order_ID`),
  ADD KEY `Table_ID` (`Table_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Payment_ID`),
  ADD KEY `Order_ID` (`Order_ID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`Product_ID`);

--
-- Indexes for table `tables_assignment`
--
ALTER TABLE `tables_assignment`
  ADD PRIMARY KEY (`Table_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `Order_Detail_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `order_r`
--
ALTER TABLE `order_r`
  MODIFY `Order_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tables_assignment`
--
ALTER TABLE `tables_assignment`
  MODIFY `Table_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_detail`
--
ALTER TABLE `order_detail`
  ADD CONSTRAINT `order_detail_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `order_r` (`Order_ID`),
  ADD CONSTRAINT `order_detail_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`);

--
-- Constraints for table `order_r`
--
ALTER TABLE `order_r`
  ADD CONSTRAINT `order_r_ibfk_1` FOREIGN KEY (`Table_ID`) REFERENCES `tables_assignment` (`Table_ID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `order_r` (`Order_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
