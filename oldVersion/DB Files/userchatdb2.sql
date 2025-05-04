-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 04, 2025 at 12:03 AM
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
  `chat_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) NOT NULL,
  `receiver` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`chat_id`),
  KEY `sender` (`sender`),
  KEY `receiver` (`receiver`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_chats`
--

INSERT INTO `tbl_chats` (`chat_id`, `sender`, `receiver`, `message`, `is_read`, `sent_at`) VALUES
(1, 1, 2, 'Hello Jane, how are you?', 1, '2025-05-03 11:33:34'),
(2, 2, 1, 'Hi John, I am doing well, thanks for asking!', 1, '2025-05-03 11:33:34'),
(3, 1, 3, 'Hey Mike, are you available for a meeting tomorrow?', 0, '2025-05-03 11:33:34'),
(4, 3, 1, 'Yes, I am available after 2 PM.', 0, '2025-05-03 11:33:34');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

DROP TABLE IF EXISTS `tbl_users`;
CREATE TABLE IF NOT EXISTS `tbl_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profile_pic` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('online','offline','away') DEFAULT 'offline',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `username`, `fullname`, `password`, `email`, `profile_pic`, `created_at`, `last_login`, `status`) VALUES
(1, 'john_doe', 'John Doe', '$2y$10$aBcD1234.EfGhIjKlMnO5pQrSTuVwXyZ', 'john@example.com', 'default.jpg', '2025-05-03 11:33:34', '2025-05-03 11:52:02', 'offline'),
(2, 'jane_smith', 'Jane Smith', '$2y$10$aBcD1234.EfGhIjKlMnO5pQrSTuVwXyZ', 'jane@example.com', 'default.jpg', '2025-05-03 11:33:34', '2025-05-03 11:53:14', 'offline'),
(3, 'mike_jones', 'Mike Jones', '$2y$10$aBcD1234.EfGhIjKlMnO5pQrSTuVwXyZ', 'mike@example.com', 'default.jpg', '2025-05-03 11:33:34', NULL, 'offline'),
(4, 'deskofficer', 'desk officer', '123456', 'deskofficer@finaccountss.com', 'default.jpg', '2025-05-03 11:54:55', '2025-05-03 11:55:34', 'online');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
