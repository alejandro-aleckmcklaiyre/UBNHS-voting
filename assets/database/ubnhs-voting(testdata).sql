-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 05, 2025 at 06:57 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u798703225_ubnhs`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_sessions`
--

CREATE TABLE `active_sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('admin','student') NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` datetime NOT NULL DEFAULT current_timestamp(),
  `last_activity` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'alekx', 'aleck123');

-- --------------------------------------------------------

--
-- Table structure for table `candidate`
--

CREATE TABLE `candidate` (
  `id` int(11) NOT NULL,
  `committee` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `partylist_name` varchar(100) DEFAULT NULL,
  `picture` varchar(255) NOT NULL,
  `votes` int(11) DEFAULT 0,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate`
--

INSERT INTO `candidate` (`id`, `committee`, `name`, `partylist_name`, `picture`, `votes`, `deleted`) VALUES
(2, 'PRESIDENT', 'Dyanna Pineda', NULL, 'assets/ubnhs-candidates/cand_68811072c01c36.44088684.jpg', 2, 0),
(3, 'PRESIDENT', 'John Doe', NULL, 'assets/ubnhs-candidates/cand_6881108199f8e3.80239686.jpg', 0, 0),
(4, 'VICE PRESIDENT', 'Jane Dee', NULL, 'assets/ubnhs-candidates/cand_688118c43837c6.66617026.jpg', 2, 0),
(5, 'VICE PRESIDENT', 'meow', NULL, 'assets/ubnhs-candidates/cand_688394763e9021.03636469.jpg', 1, 0),
(201, 'PRESIDENT', 'Candidate A (PRESIDENT)', NULL, 'assets/candidate_a_president.jpg', 23, 0),
(202, 'PRESIDENT', 'Candidate B (PRESIDENT)', NULL, 'assets/candidate_b_president.jpg', 18, 0),
(203, 'PRESIDENT', 'Candidate C (PRESIDENT)', NULL, 'assets/candidate_c_president.jpg', 14, 0),
(204, 'VICE PRESIDENT', 'Candidate A (VP FINANCE)', NULL, 'assets/candidate_a_vp_finance.jpg', 20, 0),
(205, 'VICE PRESIDENT', 'Candidate B (VP FINANCE)', NULL, 'assets/candidate_b_vp_finance.jpg', 16, 0),
(206, 'VICE PRESIDENT', 'Candidate C (VP FINANCE)', NULL, 'assets/candidate_c_vp_finance.jpg', 19, 0),
(207, 'VICE PRESIDENT', 'Candidate A (VP AUDIT)', NULL, 'assets/candidate_a_vp_audit.jpg', 20, 0),
(208, 'VICE PRESIDENT', 'Candidate B (VP AUDIT)', NULL, 'assets/candidate_b_vp_audit.jpg', 20, 0),
(209, 'VICE PRESIDENT', 'Candidate C (VP AUDIT)', NULL, 'assets/candidate_c_vp_audit.jpg', 15, 0),
(210, 'SECRETARY', 'Candidate A (SECRETARY)', NULL, 'assets/candidate_a_secretary.jpg', 24, 0),
(211, 'SECRETARY', 'Candidate B (SECRETARY)', NULL, 'assets/candidate_b_secretary.jpg', 19, 0),
(212, 'SECRETARY', 'Candidate C (SECRETARY)', NULL, 'assets/candidate_c_secretary.jpg', 12, 0),
(213, 'TREASURER', 'Candidate A (TREASURER)', NULL, 'assets/candidate_a_treasurer.jpg', 21, 0),
(214, 'TREASURER', 'Candidate B (TREASURER)', NULL, 'assets/candidate_b_treasurer.jpg', 18, 0),
(215, 'TREASURER', 'Candidate C (TREASURER)', NULL, 'assets/candidate_c_treasurer.jpg', 16, 0),
(216, 'AUDITOR', 'Candidate A (AUDITOR)', NULL, 'assets/candidate_a_auditor.jpg', 22, 0),
(217, 'AUDITOR', 'Candidate B (AUDITOR)', NULL, 'assets/candidate_b_auditor.jpg', 17, 0),
(218, 'AUDITOR', 'Candidate C (AUDITOR)', NULL, 'assets/candidate_c_auditor.jpg', 19, 0),
(219, 'PIO', 'Candidate A (PIO)', NULL, 'assets/candidate_a_pio.jpg', 24, 0),
(220, 'PIO', 'Candidate B (PIO)', NULL, 'assets/candidate_b_pio.jpg', 17, 0),
(221, 'PIO', 'Candidate C (PIO)', NULL, 'assets/candidate_c_pio.jpg', 16, 0),
(222, 'PO', 'Candidate A (PO)', NULL, 'assets/candidate_a_po.jpg', 25, 0),
(223, 'PO', 'Candidate B (PO)', NULL, 'assets/candidate_b_po.jpg', 18, 0),
(224, 'PO', 'Candidate C (PO)', NULL, 'assets/candidate_c_po.jpg', 12, 0),
(225, 'GR 12 REPRESENTATIVE', 'Candidate A (GR 12 REPRESENTATIVE)', NULL, 'assets/candidate_a_gr_12_representative.jpg', 20, 0),
(226, 'GR 12 REPRESENTATIVE', 'Candidate B (GR 12 REPRESENTATIVE)', NULL, 'assets/candidate_b_gr_12_representative.jpg', 20, 0),
(227, 'GR 12 REPRESENTATIVE', 'Candidate C (GR 12 REPRESENTATIVE)', NULL, 'assets/candidate_c_gr_12_representative.jpg', 15, 0),
(228, 'GR 11 REPRESENTATIVE', 'Candidate A (GR 11 REPRESENTATIVE)', NULL, 'assets/candidate_a_gr_11_representative.jpg', 24, 0),
(229, 'GR 11 REPRESENTATIVE', 'Candidate B (GR 11 REPRESENTATIVE)', NULL, 'assets/candidate_b_gr_11_representative.jpg', 18, 0),
(230, 'GR 11 REPRESENTATIVE', 'Candidate C (GR 11 REPRESENTATIVE)', NULL, 'assets/candidate_c_gr_11_representative.jpg', 13, 0),
(231, 'GR 10 REPRESENTATIVE', 'Candidate A (GR 10 REPRESENTATIVE)', NULL, 'assets/candidate_a_gr_10_representative.jpg', 23, 0),
(232, 'GR 10 REPRESENTATIVE', 'Candidate B (GR 10 REPRESENTATIVE)', NULL, 'assets/candidate_b_gr_10_representative.jpg', 15, 0),
(233, 'GR 10 REPRESENTATIVE', 'Candidate C (GR 10 REPRESENTATIVE)', NULL, 'assets/candidate_c_gr_10_representative.jpg', 17, 0),
(234, 'GR 9 REPRESENTATIVE', 'Candidate A (GR 9 REPRESENTATIVE)', NULL, 'assets/candidate_a_gr_9_representative.jpg', 25, 0),
(235, 'GR 9 REPRESENTATIVE', 'Candidate B (GR 9 REPRESENTATIVE)', NULL, 'assets/candidate_b_gr_9_representative.jpg', 19, 0),
(236, 'GR 9 REPRESENTATIVE', 'Candidate C (GR 9 REPRESENTATIVE)', NULL, 'assets/candidate_c_gr_9_representative.jpg', 11, 0),
(237, 'GR 8 REPRESENTATIVE', 'Candidate A (GR 8 REPRESENTATIVE)', NULL, 'assets/candidate_a_gr_8_representative.jpg', 20, 0),
(238, 'GR 8 REPRESENTATIVE', 'Candidate B (GR 8 REPRESENTATIVE)', NULL, 'assets/candidate_b_gr_8_representative.jpg', 19, 0),
(239, 'GR 8 REPRESENTATIVE', 'Candidate C (GR 8 REPRESENTATIVE)', NULL, 'assets/candidate_c_gr_8_representative.jpg', 16, 0),
(240, 'PRESIDENT', 'Alex Cruz', NULL, 'assets/ubnhs-candidates/alex.jpg', 21, 0),
(241, 'PRESIDENT', 'Bea Lim', NULL, 'assets/ubnhs-candidates/bea.jpg', 14, 0),
(242, 'PRESIDENT', 'Carlo Reyes', NULL, 'assets/ubnhs-candidates/carlo.jpg', 4, 0),
(243, 'VICE PRESIDENT', 'Diane Flores', NULL, 'assets/ubnhs-candidates/diane.jpg', 15, 0),
(244, 'VICE PRESIDENT', 'Eli Tan', NULL, 'assets/ubnhs-candidates/eli.jpg', 21, 0),
(245, 'VICE PRESIDENT', 'Faith Yulo', NULL, 'assets/ubnhs-candidates/faith.jpg', 4, 0),
(246, 'VICE PRESIDENT', 'George Salas', NULL, 'assets/ubnhs-candidates/george.jpg', 15, 0),
(247, 'VICE PRESIDENT', 'Hanna Uy', NULL, 'assets/ubnhs-candidates/hanna.jpg', 3, 0),
(248, 'VICE PRESIDENT', 'Ian Villanueva', NULL, 'assets/ubnhs-candidates/ian.jpg', 2, 0),
(249, 'SECRETARY', 'Jackie Chan', NULL, 'assets/ubnhs-candidates/jackie.jpg', 17, 0),
(250, 'SECRETARY', 'Kim Domingo', NULL, 'assets/ubnhs-candidates/kim.jpg', 3, 0),
(251, 'SECRETARY', 'Luis Manzano', NULL, 'assets/ubnhs-candidates/luis.jpg', 3, 0),
(252, 'TREASURER', 'Mara Lopez', NULL, 'assets/ubnhs-candidates/mara.jpg', 5, 0),
(253, 'TREASURER', 'Noel Perez', NULL, 'assets/ubnhs-candidates/noel.jpg', 4, 0),
(254, 'TREASURER', 'Omar Sy', NULL, 'assets/ubnhs-candidates/omar.jpg', 14, 0);

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
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `user_type` enum('admin','student') NOT NULL,
  `attempt_time` datetime NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qr_scan_logs`
--

CREATE TABLE `qr_scan_logs` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `unique_code` varchar(255) NOT NULL,
  `scan_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qr_scan_logs`
--

INSERT INTO `qr_scan_logs` (`id`, `student_id`, `unique_code`, `scan_time`) VALUES
(1, 23, 'Millerd27edbace97c25f8', '2025-07-23 00:37:48'),
(2, 24, 'Rodriguezd3071269cb16e702', '2025-07-23 00:50:03'),
(3, 27, 'Riviad91a195e59624cf8', '2025-07-24 01:05:15'),
(4, 26, 'Martinezc4bc8465baa20fb3', '2025-07-24 01:41:15'),
(5, 30, 'Alejandrocdbd9000edc0ab1e', '2025-07-25 22:29:29');

-- --------------------------------------------------------

--
-- Table structure for table `session_logs`
--

CREATE TABLE `session_logs` (
  `id` int(11) NOT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_type` enum('admin','student') DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `success` tinyint(1) NOT NULL,
  `error_message` text DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session_logs`
--

INSERT INTO `session_logs` (`id`, `session_id`, `user_id`, `user_type`, `username`, `action`, `ip_address`, `user_agent`, `success`, `error_message`, `timestamp`) VALUES
(1, 'hti66am8o36ml65kf5r8ds1lqu', 1, 'admin', 'alekx', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-07-22 19:31:30'),
(2, 'hti66am8o36ml65kf5r8ds1lqu', 28, 'student', '123456789013', 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', 1, NULL, '2025-07-22 23:56:44'),
(3, 'ac522s6t4pdbvtcmjl2n5n1fe0', 23, 'student', '202412345010', 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', 1, NULL, '2025-07-23 00:37:48'),
(4, 'ac522s6t4pdbvtcmjl2n5n1fe0', 24, 'student', '202412345001', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-07-23 00:50:03'),
(5, 'ho2n34qreimm4o4ecnf0mr6gln', 1, 'admin', 'alekx', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-07-23 20:49:41'),
(6, 'ho2n34qreimm4o4ecnf0mr6gln', 27, 'student', '123456789111', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-07-24 01:05:15'),
(7, 'ho2n34qreimm4o4ecnf0mr6gln', 26, 'student', '202412345004', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-07-24 01:41:15'),
(8, 'sejk4j6c6m4ifludm120irlcua', NULL, 'admin', 'alekx', 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 0, 'Invalid credentials', '2025-07-25 21:50:11'),
(9, 'sejk4j6c6m4ifludm120irlcua', 1, 'admin', 'alekx', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-07-25 21:50:15'),
(10, 'sejk4j6c6m4ifludm120irlcua', 30, 'student', '202512345671', 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-07-25 22:29:29'),
(11, '4pqoq2u59plh1hd5mrnk513he5', 1, 'admin', 'alekx', 'login', '110.54.178.98', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-08-03 21:32:00'),
(12, '4pqoq2u59plh1hd5mrnk513he5', 1, 'admin', 'alekx', 'login', '110.54.178.98', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-08-03 21:35:48'),
(13, 'rvan81nfoip3t2h8kfafiak50r', NULL, 'admin', 'alekx', 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 0, 'Invalid credentials', '2025-08-03 21:36:01'),
(14, 'rl9qgcbju3dhfrem8gs5sp37nj', 1, 'admin', 'alekx', 'login', '34.92.2.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-08-04 17:31:01'),
(15, 'ptjej8dal27g9pri5sl283mjfi', 1, 'admin', 'alekx', 'login', '110.54.144.224', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', 1, NULL, '2025-08-05 06:20:35'),
(16, 'lveg19ju0gvnn5ojtd0rff5kia', 1, 'admin', 'alekx', 'login', '110.54.144.224', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-08-05 06:21:06'),
(17, 'qaa2cqbhngkbnrktk58a193dt5', 1, 'admin', 'alekx', 'login', '110.54.147.232', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 1, NULL, '2025-08-05 08:49:56');

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
  `status_id` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `qr_email_sent` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `student_number`, `first_name`, `middle_name`, `last_name`, `suffix`, `email`, `unique_code`, `has_voted`, `class_group_id`, `status_id`, `deleted`, `qr_email_sent`) VALUES
(7, '202412345008', 'Robert', 'James', 'Wilson', 'Sr.', 'robert.wilson@university.edu', 'Wilson38863a561dbfa128', 0, 8, 1, 0, 0),
(8, '202412345007', 'Jennifer', 'Marie', 'Davis', NULL, 'jennifer.davis@university.edu', 'Davisc06ead22d5f9e8c7', 0, 2, 1, 0, 0),
(9, '202412345006', 'Michael', 'David', 'Brown', 'III', 'michael.brown@university.edu', 'Brown349958db02726b1f', 0, 26, 1, 0, 0),
(10, '202412345002', 'John', 'Michael', 'Thompson', 'Jr.', 'john.thompson@university.edu', 'Thompsonf30efbc98a4fb9e5', 0, 7, 1, 0, 0),
(22, '202412345009', 'Lisa', 'Ann', 'Garcia', NULL, 'lisa.garcia@university.edu', 'Garcia9119a7e7d5207b20', 0, 14, 1, 0, 0),
(23, '202412345010', 'Christopher', 'Paul', 'Miller', NULL, 'christopher.miller@university.edu', 'Millerd27edbace97c25f8', 0, 20, 2, 1, 0),
(24, '202412345001', 'Maria', 'Santos', 'Rodriguez', NULL, 'maria.rodriguez@university.edu', 'Rodriguezd3071269cb16e702', 0, 1, 2, 1, 0),
(25, '202412345003', 'Anna', 'Grace', 'Williams', NULL, 'anna.williams@university.edu', 'Williamsec99bebc472a15fa', 0, 13, 1, 0, 0),
(26, '202412345004', 'Carlos', 'Jose', 'Martinez', NULL, 'carlos.martinez@university.edu', 'Martinezc4bc8465baa20fb3', 0, 19, 2, 0, 0),
(27, '123456789111', 'Geralt', NULL, 'Rivia', NULL, 'geralt.rivia@gmail.com', 'Riviad91a195e59624cf8', 0, 21, 2, 1, 0),
(28, '123456789013', 'Adie', NULL, 'Rivera', NULL, 'adie.test@gmail.com', 'Rivera8fc6e710d870c686', 0, 26, 2, 1, 0),
(30, '202512345671', 'Aleck', NULL, 'Alejandro', NULL, 'aleck.alejandro04@gmail.com', 'Alejandrocdbd9000edc0ab1e', 0, 26, 2, 0, 1),
(31, '202512345001', 'Student', 'Test1', 'Alpha', NULL, 'alpha@example.com', 'AlphaCode1', 0, 1, 1, 0, 0),
(32, '202512345002', 'Student', 'Test2', 'Bravo', NULL, 'bravo@example.com', 'BravoCode2', 0, 2, 1, 0, 0),
(33, '202512345003', 'Student', 'Test3', 'Charlie', NULL, 'charlie@example.com', 'CharlieCode3', 0, 3, 1, 0, 0),
(34, '202512345004', 'Student', 'Test4', 'Delta', NULL, 'delta@example.com', 'DeltaCode4', 0, 4, 1, 0, 0),
(35, '202512345005', 'Student', 'Test5', 'Echo', NULL, 'echo@example.com', 'EchoCode5', 0, 5, 1, 0, 0),
(36, '202512345006', 'Student', 'Test6', 'Foxtrot', NULL, 'foxtrot@example.com', 'FoxtrotCode6', 0, 6, 1, 0, 0),
(37, '202512345007', 'Student', 'Test7', 'Golf', NULL, 'golf@example.com', 'GolfCode7', 0, 7, 1, 0, 0),
(38, '202512345008', 'Student', 'Test8', 'Hotel', NULL, 'hotel@example.com', 'HotelCode8', 0, 8, 1, 0, 0),
(39, '202512345009', 'Student', 'Test9', 'India', NULL, 'india@example.com', 'IndiaCode9', 0, 9, 1, 0, 0),
(40, '202512345010', 'Student', 'Test10', 'Juliet', NULL, 'juliet@example.com', 'JulietCode10', 0, 10, 1, 0, 0);

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
(1, 'Active'),
(2, 'Used');

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
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `committee` varchar(100) NOT NULL,
  `year_level` varchar(10) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `student_id`, `candidate_id`, `committee`, `year_level`, `timestamp`) VALUES
(776, 31, 240, 'PRESIDENT', '10', '2025-08-05 16:14:30'),
(777, 31, 243, 'VP FINANCE', '10', '2025-08-05 16:14:30'),
(778, 32, 241, 'PRESIDENT', '10', '2025-08-05 16:14:30'),
(779, 32, 244, 'VP FINANCE', '10', '2025-08-05 16:14:30'),
(780, 33, 242, 'PRESIDENT', '10', '2025-08-05 16:14:30'),
(781, 33, 245, 'VP FINANCE', '10', '2025-08-05 16:14:30'),
(782, 34, 240, 'PRESIDENT', '10', '2025-08-05 16:14:30'),
(783, 34, 245, 'VP FINANCE', '10', '2025-08-05 16:14:30'),
(784, 35, 241, 'PRESIDENT', '10', '2025-08-05 16:14:30'),
(785, 35, 243, 'VP FINANCE', '10', '2025-08-05 16:14:30'),
(786, 36, 242, 'PRESIDENT', '10', '2025-08-05 16:14:30'),
(787, 36, 244, 'VP FINANCE', '10', '2025-08-05 16:14:30'),
(788, 37, 240, 'PRESIDENT', '10', '2025-08-05 16:14:30'),
(789, 37, 244, 'VP FINANCE', '10', '2025-08-05 16:14:30'),
(790, 38, 241, 'PRESIDENT', '10', '2025-08-05 16:14:30'),
(791, 38, 243, 'VP FINANCE', '10', '2025-08-05 16:14:30'),
(792, 39, 242, 'PRESIDENT', '10', '2025-08-05 16:14:30'),
(793, 39, 245, 'VP FINANCE', '10', '2025-08-05 16:14:30'),
(794, 40, 240, 'PRESIDENT', '10', '2025-08-05 16:14:30'),
(795, 40, 243, 'VP FINANCE', '10', '2025-08-05 16:14:30'),
(796, 31, 240, 'PRESIDENT', '10', '2025-08-05 17:45:28'),
(797, 32, 241, 'PRESIDENT', '10', '2025-08-05 17:45:28'),
(798, 33, 242, 'PRESIDENT', '10', '2025-08-05 17:45:28'),
(799, 34, 243, 'VP FINANCE', '10', '2025-08-05 17:45:28'),
(800, 35, 244, 'VP FINANCE', '10', '2025-08-05 17:45:28'),
(801, 36, 245, 'VP FINANCE', '10', '2025-08-05 17:45:28'),
(802, 37, 246, 'VP AUDIT', '10', '2025-08-05 17:45:28'),
(803, 38, 247, 'VP AUDIT', '10', '2025-08-05 17:45:28'),
(804, 39, 248, 'VP AUDIT', '10', '2025-08-05 17:45:28'),
(805, 40, 249, 'SECRETARY', '10', '2025-08-05 17:45:28'),
(806, 31, 250, 'SECRETARY', '10', '2025-08-05 17:45:28'),
(807, 32, 251, 'SECRETARY', '10', '2025-08-05 17:45:28'),
(808, 33, 252, 'TREASURER', '10', '2025-08-05 17:45:28'),
(809, 34, 253, 'TREASURER', '10', '2025-08-05 17:45:28'),
(810, 35, 254, 'TREASURER', '10', '2025-08-05 17:45:28'),
(811, 36, 216, 'AUDITOR', '9', '2025-08-05 17:45:28'),
(812, 37, 217, 'AUDITOR', '9', '2025-08-05 17:45:28'),
(813, 38, 218, 'AUDITOR', '9', '2025-08-05 17:45:28'),
(814, 39, 219, 'PIO', '9', '2025-08-05 17:45:28'),
(815, 40, 220, 'PIO', '9', '2025-08-05 17:45:28');

--
-- Triggers `votes`
--
DELIMITER $$
CREATE TRIGGER `update_candidate_votes_after_insert` AFTER INSERT ON `votes` FOR EACH ROW BEGIN
  UPDATE candidate
  SET votes = votes + 1
  WHERE id = NEW.candidate_id;
END
$$
DELIMITER ;

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
-- Indexes for table `active_sessions`
--
ALTER TABLE `active_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `user_lookup` (`user_id`,`user_type`),
  ADD KEY `cleanup_index` (`is_active`,`last_activity`);

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
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `security_index` (`ip_address`,`attempt_time`),
  ADD KEY `username_index` (`username`,`attempt_time`);

--
-- Indexes for table `qr_scan_logs`
--
ALTER TABLE `qr_scan_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `session_logs`
--
ALTER TABLE `session_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_lookup` (`user_id`,`user_type`),
  ADD KEY `session_lookup` (`session_id`),
  ADD KEY `activity_index` (`action`,`timestamp`),
  ADD KEY `ip_index` (`ip_address`,`timestamp`);

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
-- Indexes for table `votes`
--
ALTER TABLE `votes`
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
-- AUTO_INCREMENT for table `active_sessions`
--
ALTER TABLE `active_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `candidate`
--
ALTER TABLE `candidate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=255;

--
-- AUTO_INCREMENT for table `class_group`
--
ALTER TABLE `class_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qr_scan_logs`
--
ALTER TABLE `qr_scan_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `session_logs`
--
ALTER TABLE `session_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `student_status`
--
ALTER TABLE `student_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `votelog`
--
ALTER TABLE `votelog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=816;

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

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`),
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidate` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
