-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2025 at 10:33 AM
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
-- Database: `task_management_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `recipient` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `message`, `recipient`, `type`, `date`, `is_read`) VALUES
(1, '\'Customer Feedback Survey Analysis\' has been assigned to you. Please review and start working on it.', 7, 'New Task Assigned', '2024-09-05', 1),
(2, '\'test task\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '0000-00-00', 1),
(3, '\'Example task 2\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2006-09-24', 1),
(4, '\'test\' has been assigned to you. Please review and start working on it', 8, 'New Task Assigned', '2009-06-24', 0),
(5, '\'test task 3\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 1),
(6, '\'Prepare monthly sales report\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 1),
(7, '\'Update client database\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 1),
(8, '\'Fix server downtime issue\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0),
(9, '\'Plan annual marketing strategy\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0),
(10, '\'Onboard new employees\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 0),
(11, '\'Design new company website\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0),
(12, '\'Conduct software testing\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 0),
(13, '\'Schedule team meeting\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0),
(14, '\'Prepare budget for Q4\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 0),
(15, '\'Write blog post on industry trend\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 0),
(16, '\'Renew software license\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `due_date`, `status`, `created_at`) VALUES
(1, 'Task 1', 'Task Description', 7, NULL, 'completed', '2024-08-29 16:47:37'),
(4, 'Monthly Financial Report Preparation', 'Prepare and review the monthly financial report, including profit and loss statements, balance sheets, and cash flow analysis.', 7, '2024-09-01', 'completed', '2024-08-31 10:50:20'),
(5, 'Customer Feedback Survey Analysis', 'Collect and analyze data from the latest customer feedback survey to identify areas for improvement in customer service.', 7, '2024-09-03', 'in_progress', '2024-08-31 10:50:47'),
(6, 'Website Maintenance and Update', 'Perform regular maintenance on the company website, update content, and ensure all security patches are applied.', 7, '2024-09-03', 'pending', '2024-08-31 10:51:12'),
(7, 'Quarterly Inventory Audit', 'Conduct a thorough audit of inventory levels across all warehouses and update the inventory management system accordingly.', 2, '2024-09-03', 'completed', '2024-08-31 10:51:45'),
(8, 'Employee Training Program Development', 'Develop and implement a new training program focused on enhancing employee skills in project management and teamwork.', 2, '2024-09-01', 'pending', '2024-08-31 10:52:11'),
(17, 'Prepare monthly sales report', 'Compile and analyze sales data for the previous month', 7, '2024-09-06', 'pending', '2024-09-06 08:01:48'),
(18, 'Update client database', 'Ensure all client information is current and complete', 7, '2024-09-07', 'pending', '2024-09-06 08:02:27'),
(19, 'Fix server downtime issue', 'Investigate and resolve the cause of recent server downtimes', 2, '2024-09-07', 'pending', '2024-09-06 08:02:59'),
(20, 'Plan annual marketing strategy', 'Develop a comprehensive marketing strategy for the next year', 2, '2024-09-04', 'pending', '2024-09-06 08:03:21'),
(21, 'Onboard new employees', 'Complete HR onboarding tasks for the new hires', 7, '2024-09-07', 'pending', '2024-09-06 08:03:44'),
(22, 'Design new company website', 'Create wireframes and mockups for the new website design', 2, '2024-09-06', 'pending', '2024-09-06 08:04:20'),
(23, 'Conduct software testing', 'Run tests on the latest software release to identify bugs', 7, '2024-09-07', 'pending', '2024-09-06 08:04:39'),
(24, 'Schedule team meeting', 'Organize a meeting to discuss project updates', 2, '2024-09-07', 'pending', '2024-09-06 08:04:57'),
(25, 'Prepare budget for Q4', 'Create and review the budget for the upcoming quarter', 7, '2024-09-07', 'pending', '2024-09-06 08:05:21'),
(26, 'Write blog post on industry trend', 'Draft and publish a blog post about current industry trend', 100, '2024-09-07', 'pending', '2024-09-06 08:10:50'),
(27, 'Renew software license', 'Ensure all software licenses are renewed and up to date', 100, '2024-09-06', 'pending', '2024-09-06 08:11:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password`, `role`, `created_at`, `profile_pic`) VALUES
(1, 'anushka bhattarai', 'Admin', NULL, '$2y$10$3ACOwmhecbKPU8NE7Kl3mexsF96ly/xisCOtyeE4AG1pA.S.4v.4m', 'admin', '2024-08-28 07:10:04', NULL),
(2, 'Elias A.', 'elias', NULL, '$2y$10$CiV/f.jO5vIsSi0Fp1Xe7ubWG9v8uKfC.VfzQr/sjb5/gypWNdlBW', 'employee', '2024-08-28 07:10:40', NULL),
(3, 'John', 'john', NULL, '$2y$10$CiV/f.jO5vIsSi0Fp1Xe7ubWG9v8uKfC.VfzQr/sjb5/gypWNdlBW', 'employee', '2024-08-29 17:11:21', NULL),
(4, 'Oliver', 'oliver', NULL, '$2y$10$E9Xx8UCsFcw44lfXxiq/5OJtloW381YJnu5lkn6q6uzIPdL5yH3PO', 'employee', '2024-08-29 17:11:34', NULL),
(5, 'Darshan Shrestha', 'Darshan', NULL, '$2y$10$bthiO1dWrtwxJyEq8PhsEO0URBnedLlinVfu9ukJudwuo2D6sLAUC', 'employee', '2025-04-04 08:28:05', NULL),
(6, 'Slisha Devkota', 'Slisha', NULL, '$2y$10$kPZ/nKUYt4uHKG8.K9q6GOE7zzmLr5xK3rFnevj8QMm9U90bbb87G', 'employee', '2025-04-04 08:31:26', NULL),
(7, 'Samprada Shrestha', 'Samprada', NULL, '$2y$10$8Kpuu57v/XjnehMKtqVClu9oiq0k3yITS0icmHwOi8O5JzOiYLolO', 'employee', '2025-04-04 08:32:24', NULL);

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('not-started','pending','completed') DEFAULT 'not-started',
  `pinned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`title`, `content`, `user_id`, `status`, `pinned`, `created_at`) VALUES
('Welcome Note', 'Welcome to your personal notes! This is a sample note to help you get started.', 1, 'completed', 1, NOW()),
('Project Ideas', 'List of potential project ideas:\n- Task management system\n- Personal blog\n- E-commerce platform', 2, 'pending', 0, NOW()),
('Meeting Notes', 'Key points from team meeting:\n- Discussed project timeline\n- Assigned tasks\n- Set next meeting date', 7, 'not-started', 0, NOW());

--
-- Table structure for table `note_shares`
--

CREATE TABLE `note_shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `shared_by` int(11) NOT NULL,
  `shared_with` int(11) NOT NULL,
  `can_edit` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_share` (`note_id`, `shared_with`),
  KEY `shared_by` (`shared_by`),
  KEY `shared_with` (`shared_with`),
  CONSTRAINT `note_shares_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `note_shares_ibfk_2` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id`),
  CONSTRAINT `note_shares_ibfk_3` FOREIGN KEY (`shared_with`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
