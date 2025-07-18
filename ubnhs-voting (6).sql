-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 18, 2025 at 07:40 PM
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
-- Database: `ubnhs-voting`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'alekx', 'test123');

-- --------------------------------------------------------

--
-- Table structure for table `candidate`
--

CREATE TABLE `candidate` (
  `id` int(11) NOT NULL,
  `committee` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `votes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_group`
--

CREATE TABLE `class_group` (
  `id` int(11) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `section` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_group`
--

INSERT INTO `class_group` (`id`, `year_level`, `section`) VALUES
(16, '10', 'A'),
(17, '10', 'B'),
(18, '10', 'C'),
(19, '10', 'D'),
(20, '10', 'E'),
(21, '11', 'A'),
(22, '11', 'B'),
(23, '11', 'C'),
(24, '11', 'D'),
(25, '11', 'E'),
(26, '12', 'A'),
(27, '12', 'B'),
(28, '12', 'C'),
(29, '12', 'D'),
(30, '12', 'E'),
(1, '7', 'A'),
(2, '7', 'B'),
(3, '7', 'C'),
(4, '7', 'D'),
(5, '7', 'E'),
(6, '8', 'A'),
(7, '8', 'B'),
(8, '8', 'C'),
(9, '8', 'D'),
(10, '8', 'E'),
(11, '9', 'A'),
(12, '9', 'B'),
(13, '9', 'C'),
(14, '9', 'D'),
(15, '9', 'E');

-- --------------------------------------------------------

--
-- Table structure for table `qr_scan_logs`
--

CREATE TABLE `qr_scan_logs` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `scan_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `unique_code` varchar(255) NOT NULL,
  `has_voted` tinyint(1) NOT NULL DEFAULT 0,
  `class_group_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `student_number`, `first_name`, `middle_name`, `last_name`, `suffix`, `email`, `unique_code`, `has_voted`, `class_group_id`, `status_id`) VALUES
(1, '123456789012', 'Aleck', 'Rivera', 'Alejandro', NULL, 'aleck.alejandro04@gmail.com', '64c7c69e5d35b692', 0, 26, 1),
(2, '123456789011', 'Alekx', NULL, 'Alejandro', NULL, 'aleck.bizz@gmail.com', 'db19c54f112c3d9f', 0, 22, 1),
(7, '202412345008', 'Robert', 'James', 'Wilson', 'Sr.', 'robert.wilson@university.edu', 'Wilson38863a561dbfa128', 0, 8, 1),
(8, '202412345007', 'Jennifer', 'Marie', 'Davis', NULL, 'jennifer.davis@university.edu', 'Davisc06ead22d5f9e8c7', 0, 2, 1),
(9, '202412345006', 'Michael', 'David', 'Brown', 'III', 'michael.brown@university.edu', 'Brown349958db02726b1f', 0, 26, 1),
(10, '202412345002', 'John', 'Michael', 'Thompson', 'Jr.', 'john.thompson@university.edu', 'Thompsonf30efbc98a4fb9e5', 0, 7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_status`
--

CREATE TABLE `student_status` (
  `id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_status`
--

INSERT INTO `student_status` (`id`, `status_name`) VALUES
(1, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `votelog`
--

CREATE TABLE `votelog` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `votingtime`
--

CREATE TABLE `votingtime` (
  `id` int(11) NOT NULL,
  `voting_end_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `candidate`
--
ALTER TABLE `candidate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_group`
--
ALTER TABLE `class_group`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year_level` (`year_level`,`section`);

--
-- Indexes for table `qr_scan_logs`
--
ALTER TABLE `qr_scan_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_code` (`unique_code`),
  ADD KEY `fk_status_id` (`status_id`),
  ADD KEY `fk_class_group` (`class_group_id`);

--
-- Indexes for table `student_status`
--
ALTER TABLE `student_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `votelog`
--
ALTER TABLE `votelog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `votingtime`
--
ALTER TABLE `votingtime`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `candidate`
--
ALTER TABLE `candidate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_group`
--
ALTER TABLE `class_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `qr_scan_logs`
--
ALTER TABLE `qr_scan_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_status`
--
ALTER TABLE `student_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `votelog`
--
ALTER TABLE `votelog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `votingtime`
--
ALTER TABLE `votingtime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `qr_scan_logs`
--
ALTER TABLE `qr_scan_logs`
  ADD CONSTRAINT `qr_scan_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `fk_class_group` FOREIGN KEY (`class_group_id`) REFERENCES `class_group` (`id`),
  ADD CONSTRAINT `fk_status_id` FOREIGN KEY (`status_id`) REFERENCES `student_status` (`id`);

--
-- Constraints for table `votelog`
--
ALTER TABLE `votelog`
  ADD CONSTRAINT `votelog_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`),
  ADD CONSTRAINT `votelog_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidate` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
