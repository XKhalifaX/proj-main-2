-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 07:52 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zututors`
--

-- --------------------------------------------------------

--
-- Table structure for table `boards`
--

CREATE TABLE `boards` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_role` varchar(50) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boards`
--

INSERT INTO `boards` (`id`, `title`, `content`, `user_id`, `user_role`, `tutor_id`, `parent_id`, `created_at`) VALUES
(1, 'Calculus 101', 'Intro to derivatives and integrals', 1, 'tutor', NULL, NULL, '2025-11-15 11:17:59'),
(2, 'Physics Mechanics', 'Newtonian mechanics basics', 2, 'tutor', NULL, NULL, '2025-11-15 11:17:59'),
(3, 'Organic Chemistry Basics', 'Structure and reactions overview', 3, 'tutor', NULL, NULL, '2025-11-15 11:17:59');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'student',
  `settings` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `email`, `password`, `role`, `settings`, `created_at`) VALUES
(202013986, 'Rashed Al Aleeli', '202013986@zu.ac.ae', 'rashoodie2000', 'student', 'computer science', '2025-11-15 11:05:56'),
(202013987, 'Ahmed', '202112345@zu.ac.ae', '$2y$10$BDQxrPKU/dCn1JJZ5kLgXORuuejlMRAN.TbsHLtFqxduyUOKT1See', 'student', '{}', '2025-11-15 14:31:22');

-- --------------------------------------------------------

--
-- Table structure for table `tutors`
--

CREATE TABLE `tutors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `subjects` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `settings` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tutors`
--

INSERT INTO `tutors` (`id`, `name`, `email`, `password`, `subjects`, `bio`, `settings`, `created_at`) VALUES
(1, 'Lina Ahmed', 'lina.ahmed@example.com', 'password123', 'Mathematics, Calculus', 'Experienced calculus tutor focusing on problem solving and exam prep.', '{}', '2025-11-15 11:17:46'),
(2, 'Omar Khalid', 'omar.khalid@example.com', 'password123', 'Physics, Mechanics', 'Physics tutor with hands-on lab experience and conceptual teaching style.', '{}', '2025-11-15 11:17:46'),
(3, 'Sara Al-Masri', 'sara.almasri@example.com', 'password123', 'Chemistry, Organic Chemistry', 'Organic chemistry tutor specializing in reaction mechanisms and synthesis.', '{}', '2025-11-15 11:17:46'),
(4, 'David Park', 'david.park@example.com', 'password123', 'Computer Science, Algorithms', 'CS tutor with background in algorithms, data structures and coding interviews.', '{}', '2025-11-15 11:17:46'),
(5, 'Maya Singh', 'maya.singh@example.com', 'password123', 'Biology, Genetics', 'Biology tutor focusing on genetics, molecular biology and exam strategies.', '{}', '2025-11-15 11:17:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `boards`
--
ALTER TABLE `boards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tutors`
--
ALTER TABLE `tutors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `boards`
--
ALTER TABLE `boards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202013988;

--
-- AUTO_INCREMENT for table `tutors`
--
ALTER TABLE `tutors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
