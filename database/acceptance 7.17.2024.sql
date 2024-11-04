-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.28-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.4.0.6659
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for myprojectpos
DROP DATABASE IF EXISTS `myprojectpos`;
CREATE DATABASE IF NOT EXISTS `myprojectpos` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `myprojectpos`;

-- Dumping structure for table myprojectpos.customer
DROP TABLE IF EXISTS `customer`;
CREATE TABLE IF NOT EXISTS `customer` (
  `CustomerID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerName` varchar(255) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`CustomerID`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table myprojectpos.deliverytransaction
DROP TABLE IF EXISTS `deliverytransaction`;
CREATE TABLE IF NOT EXISTS `deliverytransaction` (
  `TransactionID` int(11) NOT NULL AUTO_INCREMENT,
  `OR_No` varchar(50) NOT NULL DEFAULT '0',
  `Total_Amount` decimal(10,2) NOT NULL,
  `Delivery_Date` date NOT NULL,
  `StoreID` int(11) DEFAULT NULL,
  PRIMARY KEY (`TransactionID`),
  KEY `StoreID` (`StoreID`),
  CONSTRAINT `deliverytransaction_ibfk_1` FOREIGN KEY (`StoreID`) REFERENCES `store` (`StoreID`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table myprojectpos.deliverytransactiondetails
DROP TABLE IF EXISTS `deliverytransactiondetails`;
CREATE TABLE IF NOT EXISTS `deliverytransactiondetails` (
  `DetailID` int(11) NOT NULL AUTO_INCREMENT,
  `TransactionID` int(11) DEFAULT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `Quantity` int(11) NOT NULL,
  `Cost` decimal(10,2) NOT NULL,
  `Total_Cost` decimal(10,2) NOT NULL,
  PRIMARY KEY (`DetailID`),
  KEY `TransactionID` (`TransactionID`),
  KEY `ProductID` (`ProductID`),
  CONSTRAINT `deliverytransactiondetails_ibfk_1` FOREIGN KEY (`TransactionID`) REFERENCES `deliverytransaction` (`TransactionID`) ON DELETE CASCADE,
  CONSTRAINT `deliverytransactiondetails_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table myprojectpos.delivery_or
DROP TABLE IF EXISTS `delivery_or`;
CREATE TABLE IF NOT EXISTS `delivery_or` (
  `OR_No` varchar(50) NOT NULL,
  `id` int(11) NOT NULL,
  `DateTime` datetime NOT NULL,
  PRIMARY KEY (`OR_No`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table myprojectpos.product
DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `ProductID` int(11) NOT NULL AUTO_INCREMENT,
  `Description` varchar(255) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Stock_Quantity` int(11) NOT NULL,
  PRIMARY KEY (`ProductID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table myprojectpos.salesreceipt
DROP TABLE IF EXISTS `salesreceipt`;
CREATE TABLE IF NOT EXISTS `salesreceipt` (
  `Receipt_No` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `DateTime` datetime NOT NULL,
  PRIMARY KEY (`Receipt_No`)
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table myprojectpos.salestransactiondetails
DROP TABLE IF EXISTS `salestransactiondetails`;
CREATE TABLE IF NOT EXISTS `salestransactiondetails` (
  `SaleDetailID` int(11) NOT NULL AUTO_INCREMENT,
  `SaleTransactionID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Total_Amt` decimal(10,2) NOT NULL,
  PRIMARY KEY (`SaleDetailID`),
  KEY `SaleTransactionID` (`SaleTransactionID`),
  KEY `ProductID` (`ProductID`)
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table myprojectpos.saletransaction
DROP TABLE IF EXISTS `saletransaction`;
CREATE TABLE IF NOT EXISTS `saletransaction` (
  `SaleTransactionID` int(11) NOT NULL AUTO_INCREMENT,
  `Sales_date` date NOT NULL,
  `SaleTotal_amount` decimal(10,2) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `Receipt_No` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  PRIMARY KEY (`SaleTransactionID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `Receipt_No` (`Receipt_No`)
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table myprojectpos.store
DROP TABLE IF EXISTS `store`;
CREATE TABLE IF NOT EXISTS `store` (
  `StoreID` int(11) NOT NULL AUTO_INCREMENT,
  `StoreName` varchar(255) NOT NULL,
  `Location` varchar(255) NOT NULL,
  PRIMARY KEY (`StoreID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table myprojectpos.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','cashier') NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
