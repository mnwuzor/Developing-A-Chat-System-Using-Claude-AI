-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 04, 2025 at 12:26 AM
-- Server version: 5.7.40
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `userchatdb2`
--
CREATE DATABASE IF NOT EXISTS `userchatdb2` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `userchatdb2`;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_chats`
--

DROP TABLE IF EXISTS `tbl_chats`;
CREATE TABLE IF NOT EXISTS `tbl_chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) NOT NULL,
  `receiver` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `receiver` (`receiver`),
  KEY `idx_chat_users` (`sender`,`receiver`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_chats`
--

INSERT INTO `tbl_chats` (`id`, `sender`, `receiver`, `message`, `is_read`, `sent_at`) VALUES
(1, 4, 3, 'Hi Dede', 1, '2025-05-04 00:17:06'),
(2, 3, 4, 'I am good Officer.\n\nHow are you doing sir', 1, '2025-05-04 00:17:36');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

DROP TABLE IF EXISTS `tbl_users`;
CREATE TABLE IF NOT EXISTS `tbl_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT 'default.jpg',
  `status` enum('online','offline') DEFAULT 'offline',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `username`, `password`, `email`, `full_name`, `profile_image`, `status`, `created_at`, `last_login`) VALUES
(1, 'john', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNiDA.s1teoXuLjLyG', 'john@example.com', 'John Doe', 'default.jpg', 'offline', '2025-05-04 00:04:34', NULL),
(2, 'jane', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNiDA.s1teoXuLjLyG', 'jane@example.com', 'Jane Smith', 'default.jpg', 'offline', '2025-05-04 00:04:34', NULL),
(3, 'dedeenyi97', '$2y$10$XL6AW/6GF49cpLQLpBQjYuGwb3FvGkshbJW470MZKXPnTAwctRFwi', 'dedeenyi97@gmail.com', 'Dede Enyi', 'default.jpg', 'online', '2025-05-04 00:14:43', '2025-05-04 00:15:01'),
(4, 'deskofficer', '$2y$10$jKexXdNwQEIePDDuDncsMeKR10F7LvIEniQzTGn4Wp7mayGrsi4Ly', 'deskofficer@finaccountss.com', 'desk officer', 'default.jpg', 'offline', '2025-05-04 00:16:35', '2025-05-04 00:19:42');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
