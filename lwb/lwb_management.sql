-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 17, 2025 at 12:33 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lwb_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

DROP TABLE IF EXISTS `bills`;
CREATE TABLE IF NOT EXISTS `bills` (
  `Bill_ID` int NOT NULL AUTO_INCREMENT,
  `Meter_ID` int DEFAULT NULL,
  `Billing_Period` varchar(20) DEFAULT NULL,
  `Units_Consumed` int DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL,
  `Due_Date` date DEFAULT NULL,
  `Status` enum('Unpaid','Paid','Overdue') DEFAULT NULL,
  PRIMARY KEY (`Bill_ID`),
  KEY `Meter_ID` (`Meter_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`Bill_ID`, `Meter_ID`, `Billing_Period`, `Units_Consumed`, `Amount`, `Due_Date`, `Status`) VALUES
(6, 23, '2025-09', 180, 14310.00, '2025-09-12', 'Unpaid'),
(5, 22, '2025-09', 180, 14310.00, '2025-09-15', 'Overdue'),
(7, 24, '2025-09', 34, 14310.00, '2025-09-15', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `call_logs`
--

DROP TABLE IF EXISTS `call_logs`;
CREATE TABLE IF NOT EXISTS `call_logs` (
  `Call_ID` int NOT NULL AUTO_INCREMENT,
  `Customer_ID` int NOT NULL,
  `Staff_ID` int NOT NULL,
  `Call_Date` datetime DEFAULT CURRENT_TIMESTAMP,
  `Notes` text,
  `Status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Call_ID`),
  KEY `Customer_ID` (`Customer_ID`),
  KEY `Staff_ID` (`Staff_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `call_logs`
--

INSERT INTO `call_logs` (`Call_ID`, `Customer_ID`, `Staff_ID`, `Call_Date`, `Notes`, `Status`) VALUES
(1, 24, 5, '2025-09-15 18:00:37', 'no water for two days help him ', 'Pending'),
(2, 24, 5, '2025-09-17 13:51:12', 'no water for 6 days and the customer has been calling over and over Chiso help the customer ', 'Follow-up');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
CREATE TABLE IF NOT EXISTS `complaints` (
  `Complaint_ID` int NOT NULL AUTO_INCREMENT,
  `Customer_ID` int DEFAULT NULL,
  `Date_Logged` date DEFAULT NULL,
  `Type` enum('Leakage','Billing','Meter Fault','Service Request') DEFAULT NULL,
  `Status` enum('Open','In Progress','Resolved') DEFAULT NULL,
  `Resolved_By` int DEFAULT NULL,
  PRIMARY KEY (`Complaint_ID`),
  KEY `Customer_ID` (`Customer_ID`),
  KEY `Resolved_By` (`Resolved_By`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`Complaint_ID`, `Customer_ID`, `Date_Logged`, `Type`, `Status`, `Resolved_By`) VALUES
(8, 21, '2025-09-12', 'Billing', 'Open', 4),
(7, 21, '2025-09-12', 'Billing', 'Open', 4),
(6, 21, '2025-09-12', 'Billing', 'Open', 4),
(5, 21, '2025-09-12', 'Billing', 'Open', 4),
(9, 21, '2025-09-12', 'Billing', 'Open', 4),
(10, 21, '2025-09-12', 'Billing', 'Open', 4),
(11, 21, '2025-09-12', 'Billing', 'Open', 4),
(12, 23, '2025-09-14', 'Billing', 'Open', 5),
(13, 24, '2025-09-13', '', 'Open', 5),
(14, 25, '2025-09-15', 'Service Request', 'Resolved', 5),
(15, 23, '2025-09-15', '', 'In Progress', 5);

-- --------------------------------------------------------

--
-- Table structure for table `connections`
--

DROP TABLE IF EXISTS `connections`;
CREATE TABLE IF NOT EXISTS `connections` (
  `Connection_ID` int NOT NULL AUTO_INCREMENT,
  `Customer_ID` int NOT NULL,
  `Connection_Type` varchar(50) NOT NULL,
  PRIMARY KEY (`Connection_ID`),
  KEY `Customer_ID` (`Customer_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `connections`
--

INSERT INTO `connections` (`Connection_ID`, `Customer_ID`, `Connection_Type`) VALUES
(14, 22, 'Plumbing connection'),
(6, 18, 'Plumbing connection'),
(5, 18, 'Plumbing connection'),
(13, 21, 'Main pipe'),
(15, 23, 'Plumbing connection'),
(16, 24, 'Main pipe'),
(17, 25, 'Commercial');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `Customer_ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Category` enum('Domestic','Commercial','Institutional') DEFAULT NULL,
  PRIMARY KEY (`Customer_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`Customer_ID`, `Name`, `Address`, `Phone`, `Email`, `Category`) VALUES
(23, 'Robe', 'tnm box 658783', '099634521', 'tiya@gmail.com', 'Domestic'),
(24, 'mose', 'banda', '0994267367', 'a@gmail.com', 'Domestic'),
(25, 'Yakho', 'tnm box 89 ll', '0994267367', 'mda25-rbanda@mubas.ac.mw', 'Domestic');

-- --------------------------------------------------------

--
-- Table structure for table `meterreadings`
--

DROP TABLE IF EXISTS `meterreadings`;
CREATE TABLE IF NOT EXISTS `meterreadings` (
  `Reading_ID` int NOT NULL AUTO_INCREMENT,
  `Meter_ID` int NOT NULL,
  `Reading_Date` date NOT NULL,
  `Reading_Value` decimal(10,2) NOT NULL,
  `Recorded_By` int NOT NULL,
  PRIMARY KEY (`Reading_ID`),
  KEY `Meter_ID` (`Meter_ID`),
  KEY `Recorded_By` (`Recorded_By`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meterreadings`
--

INSERT INTO `meterreadings` (`Reading_ID`, `Meter_ID`, `Reading_Date`, `Reading_Value`, `Recorded_By`) VALUES
(1, 20, '2025-09-11', 10.00, 4),
(2, 21, '2025-09-13', 10.00, 4),
(3, 20, '2025-09-11', 10.00, 4),
(4, 22, '2025-09-14', 180.00, 5),
(5, 20, '2025-09-11', 10.00, 4),
(6, 23, '2025-09-13', 180.00, 5),
(7, 24, '2025-09-15', 34.00, 6);

-- --------------------------------------------------------

--
-- Table structure for table `meters`
--

DROP TABLE IF EXISTS `meters`;
CREATE TABLE IF NOT EXISTS `meters` (
  `Meter_ID` int NOT NULL AUTO_INCREMENT,
  `Customer_ID` int DEFAULT NULL,
  `Meter_Number` varchar(50) DEFAULT NULL,
  `Installation_Date` date DEFAULT NULL,
  `Status` enum('Working','Faulty','Replaced') DEFAULT NULL,
  `Last_Service_Date` date DEFAULT NULL,
  PRIMARY KEY (`Meter_ID`),
  KEY `Customer_ID` (`Customer_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meters`
--

INSERT INTO `meters` (`Meter_ID`, `Customer_ID`, `Meter_Number`, `Installation_Date`, `Status`, `Last_Service_Date`) VALUES
(24, 25, '20213456678', '2025-09-15', '', '2025-09-15'),
(23, 24, '20218788888', '2025-09-13', '', '2025-09-13'),
(22, 23, '20247777', '2025-09-14', '', '2025-09-14'),
(18, 20, '202490000', '2025-09-12', '', '2025-09-12'),
(20, 21, '56780', '2025-09-12', '', '2025-09-10'),
(21, 22, '20230000', '2025-09-01', '', '2025-09-14');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `Payment_ID` int NOT NULL AUTO_INCREMENT,
  `Bill_ID` int DEFAULT NULL,
  `Payment_Date` date DEFAULT NULL,
  `Amount_Paid` decimal(10,2) DEFAULT NULL,
  `Method` enum('Cash','Bank','Mobile Money','Online') DEFAULT NULL,
  PRIMARY KEY (`Payment_ID`),
  KEY `Bill_ID` (`Bill_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`Payment_ID`, `Bill_ID`, `Payment_Date`, `Amount_Paid`, `Method`) VALUES
(18, 6, '2025-09-13', 1690.00, 'Mobile Money'),
(20, 7, '2025-09-09', 1434.00, 'Mobile Money'),
(19, 6, '2025-09-13', 1690.00, 'Mobile Money'),
(21, 7, '2025-09-09', 1434.00, 'Mobile Money'),
(22, 7, '2025-09-17', 2456.00, 'Mobile Money'),
(23, 7, '2025-09-17', 2456.00, 'Mobile Money');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `Staff_ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) DEFAULT NULL,
  `Department` enum('Meter Reading','Billing','Customer Care','Technical') DEFAULT NULL,
  `Position` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Staff_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`Staff_ID`, `Name`, `Department`, `Position`) VALUES
(7, 'Yakho Moyo', 'Customer Care', 'Manager'),
(6, 'maya', 'Customer Care', 'customer care'),
(5, 'chiso', 'Customer Care', 'Customer Care'),
(8, 'Jonasi Moyo', 'Meter Reading', 'Meter Reader'),
(9, 'Chimwemwe John', 'Billing', 'Billing Officer');

-- --------------------------------------------------------

--
-- Table structure for table `tariffs`
--

DROP TABLE IF EXISTS `tariffs`;
CREATE TABLE IF NOT EXISTS `tariffs` (
  `Tariff_ID` int NOT NULL AUTO_INCREMENT,
  `Tariff_Name` varchar(100) NOT NULL,
  `Rate` decimal(10,2) NOT NULL,
  PRIMARY KEY (`Tariff_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
