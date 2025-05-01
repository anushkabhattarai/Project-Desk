-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 26, 2025 at 03:16 PM
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
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('not-started','pending','completed') DEFAULT 'not-started',
  `pinned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `title`, `content`, `user_id`, `status`, `pinned`, `created_at`, `updated_at`) VALUES
(1, 'Welcome Note', 'Welcome to your personal notes! This is a sample note to help you get started.', 5, 'completed', 1, '2025-04-04 08:39:04', '2025-04-04 08:40:48'),
(2, 'Project Ideas', 'List of potential project ideas:\n- Task management system\n- Personal blog\n- E-commerce platform', 2, 'pending', 0, '2025-04-04 08:39:04', '2025-04-04 08:39:04'),
(3, 'Meeting Notes', 'Key points from team meeting:\n- Discussed project timeline\n- Assigned tasks\n- Set next meeting date', 7, 'completed', 0, '2025-04-04 08:39:04', '2025-04-07 08:27:39'),
(8, 'arhuahwd', 'helo my name is bhjbwaj<div bis_skin_checked=\"1\"><br></div>', 6, 'pending', 0, '2025-04-04 09:11:33', '2025-04-05 06:26:39'),
(9, 'ui ux principles', 'Following UI design best practices makes digital products easier for everyone to use, follow, and enjoy. The benefits of applying UI design principles are many, including:\r\n<div bis_skin_checked=\"1\">\r\n</div><div bis_skin_checked=\"1\">Enhances usability. "Think of a user as someone asking you directions. If you just showed them a map and expected them to memorize it, they\'ll probably get lost," Tom says. "But if you point them to a sign that says their destination is this way, they can follow the signs from there … That\'s a much better experience. UI design principles help you set up signs users can follow towards their goals—one click, scroll, or interaction at a time."\r\n</div><div bis_skin_checked=\"1\">Improves decision-making. Clear and consistent UI design principles give a structured framework for predicting user needs and making informed design choices.\r\n</div><div bis_skin_checked=\"1\">Increases efficiency. Aligning UI design principles at the start of projects lifts the cognitive load for designers, streamlining workflows and making product teams more efficient. Figma data analysts found that participants with access to a design system completed their design objective 34% faster than those without one.\r\n</div><div bis_skin_checked=\"1\">Reduces cognitive load. A well-designed interface can simplify tasks, reducing the mental effort required to complete user actions. Less cognitive load can help create a more intuitive and enjoyable experience.</div>', 1, 'completed', 1, '2025-04-17 08:03:49', '2025-04-17 09:10:29');

--
-- Table structure for table `note_comments`
--

CREATE TABLE `note_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `note_id` (`note_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `note_comments_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `note_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `note_comments`
--

INSERT INTO `note_comments` (`note_id`, `user_id`, `comment`, `created_at`) VALUES
(8, 7, 'This is a great note!', '2025-05-01 11:24:22'),
(8, 5, 'Thanks for sharing!', '2025-05-01 11:24:26');

-- --------------------------------------------------------

--
-- Table structure for table `note_shares`
--

CREATE TABLE `note_shares` (
  `id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `shared_by` int(11) NOT NULL,
  `shared_with` int(11) NOT NULL,
  `can_edit` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `note_shares`
--

INSERT INTO `note_shares` (`id`, `note_id`, `shared_by`, `shared_with`, `can_edit`, `created_at`) VALUES
(2, 8, 6, 5, 1, '2025-04-04 10:59:18'),
(3, 8, 6, 7, 1, '2025-04-04 11:04:08'),
(4, 8, 6, 3, 1, '2025-04-04 11:24:01');

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
(10, '\'Onboard new employees\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 1),
(11, '\'Design new company website\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0),
(12, '\'Conduct software testing\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 1),
(13, '\'Schedule team meeting\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0),
(14, '\'Prepare budget for Q4\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 1),
(15, '\'Write blog post on industry trend\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 1),
(16, '\'Renew software license\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0),
(0, 'Note \'HOW TO BE HEALTHY\' has been shared with you with edit permissions.', 6, 'Note Shared', '2025-04-04', 1),
(0, 'A note titled \'hello\' has been shared with you.', 5, 'Note Shared', '2025-04-04', 0),
(0, 'A note titled \'hello\' has been shared with you.', 7, 'Note Shared', '2025-04-04', 1),
(0, 'A note titled \'hello\' has been shared with you.', 3, 'Note Shared', '2025-04-04', 0),
(0, 'You have successfully subscribed to the Premium plan.', 7, 'Payment Confirmation', '2025-04-09', 1),
(0, '\'fyp poster\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2025-04-17', 1),
(0, 'New support ticket (#3): \'I am not able to pay through khalti\' submitted by Samprada', 1, 'Support Ticket', '2025-04-25', 0),
(0, 'New support ticket (#3): \'I am not able to pay through khalti\' submitted by Samprada', 5, 'Support Ticket', '2025-04-25', 0),
(0, 'Task \'Prepare budget for Q4\' has been updated. Changes made to: due date', 7, 'Task Updated', '2025-04-25', 0);

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `note_limit` int(11) DEFAULT NULL,
  `private_note_limit` int(11) DEFAULT NULL,
  `share_limit` int(11) DEFAULT NULL,
  `is_unlimited` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `description`, `price`, `note_limit`, `private_note_limit`, `share_limit`, `is_unlimited`, `created_at`) VALUES
(1, 'Basic Plan', 'Basic plan with limited features', 200.00, 5, 5, 5, 0, '2025-04-12 11:38:38'),
(2, 'Premium Plan', 'Premium plan with unlimited features', 500.00, 50, 50, 50, 1, '2025-04-12 11:38:38');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_replies`
--

CREATE TABLE `support_replies` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `role` enum('user','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_replies`
--

INSERT INTO `support_replies` (`id`, `ticket_id`, `user_id`, `message`, `role`, `created_at`) VALUES
(1, 1, 7, 'I cannot access my tasks page. It shows an error when I try to load it.', 'user', '2025-04-24 06:42:41'),
(2, 2, 2, 'I am trying to assign a task to another user but the dropdown is empty.', 'user', '2025-04-22 06:42:41'),
(3, 2, 1, 'Please try refreshing the page. The dropdown should populate with available users. Let me know if the issue persists.', 'admin', '2025-04-23 06:42:41'),
(4, 2, 2, 'That worked! Thank you for the quick response.', 'user', '2025-04-23 06:42:41'),
(5, 1, 7, 'hi', 'user', '2025-04-24 06:43:06'),
(6, 1, 7, 'helo hpw r u', 'user', '2025-04-24 06:44:06'),
(7, 1, 1, 'hhhhheifvh', 'admin', '2025-04-24 06:45:06'),
(8, 3, 7, 'Khalti payment isnt working,', 'user', '2025-04-25 08:04:46');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('open','resolved') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `subject`, `status`, `created_at`, `updated_at`) VALUES
(1, 7, 'Cannot access my tasks', 'resolved', '2025-04-24 06:42:41', '2025-04-24 06:45:08'),
(2, 2, 'Need help with task assignment', 'resolved', '2025-04-22 06:42:41', '2025-04-24 06:42:41'),
(3, 7, 'I am not able to pay through khalti', 'open', '2025-04-25 08:04:46', '2025-04-25 08:04:46');

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
(1, 'Task2', 'Complete ui ux', 7, '2025-04-17', 'completed', '2024-08-29 16:47:37'),
(4, 'Monthly Financial Report Preparation', 'Prepare and review the monthly financial report, including profit and loss statements, balance sheets, and cash flow analysis.', 7, '2024-09-01', 'in_progress', '2024-08-31 10:50:20'),
(5, 'Customer Feedback Survey Analysis', 'Collect and analyze data from the latest customer feedback survey to identify areas for improvement in customer service.', 7, '2024-09-03', 'in_progress', '2024-08-31 10:50:47'),
(6, 'Website Maintenance and Update', 'Perform regular maintenance on the company website, update content, and ensure all security patches are applied.', 7, '2024-09-03', 'pending', '2024-08-31 10:51:12'),
(7, 'Quarterly Inventory Audit', 'Conduct a thorough audit of inventory levels across all warehouses and update the inventory management system accordingly.', 2, '2024-09-03', 'completed', '2024-08-31 10:51:45'),
(8, 'Employee Training Program Development', 'Develop and implement a new training program focused on enhancing employee skills in project management and teamwork.', 5, '2024-09-01', 'pending', '2024-08-31 10:52:11'),
(17, 'Prepare monthly sales report', 'Compile and analyze sales data for the previous month', 7, '2024-09-06', 'pending', '2024-09-06 08:01:48'),
(18, 'Update client database', 'Ensure all client information is current and complete', 7, '2024-09-07', 'completed', '2024-09-06 08:02:27'),
(19, 'Fix server downtime issue', 'Investigate and resolve the cause of recent server downtimes', 2, '2024-09-07', 'pending', '2024-09-06 08:02:59'),
(20, 'Plan annual marketing strategy', 'Develop a comprehensive marketing strategy for the next year', 5, '2025-04-17', 'pending', '2024-09-06 08:03:21'),
(21, 'Onboard new employees', 'Complete HR onboarding tasks for the new hires', 7, '2025-04-17', 'in_progress', '2024-09-06 08:03:44'),
(22, 'Design new company website', 'Create wireframes and mockups for the new website design', 2, '2024-09-06', 'pending', '2024-09-06 08:04:20'),
(23, 'Conduct software testing', 'Run tests on the latest software release to identify bugs', 7, '2024-09-07', 'completed', '2024-09-06 08:04:39'),
(24, 'Schedule team meeting', 'Organize a meeting to discuss project updates', 2, '2024-09-07', 'pending', '2024-09-06 08:04:57'),
(25, 'Prepare budget for Q4', 'Create and review the budget for the upcoming quarter', 7, '2025-04-08', 'completed', '2024-09-06 08:05:21'),
(26, 'Write blog post on industry trend', 'Draft and publish a blog post about current industry trend', 5, '2024-09-07', 'completed', '2024-09-06 08:10:50'),
(27, 'Renew software license', 'Ensure all software licenses are renewed and up to date', 5, '2024-09-06', 'in_progress', '2024-09-06 08:11:28'),
(0, 'fyp poster', 'complete fyp pster', 7, '2025-04-24', 'in_progress', '2025-04-17 09:11:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `phone`, `password`, `role`, `created_at`, `profile_pic`) VALUES
(1, 'anushka bhattarai', 'Admin', NULL, NULL, '$2y$10$UCwAzUVooHRP29gtnv5RSOzJwaJMtiyEpWw3YIgBnnV1/nnSHjd3.', 'admin', '2024-08-28 07:10:04', NULL),
(2, 'Elias A.', 'elias', NULL, NULL, '$2y$10$CiV/f.jO5vIsSi0Fp1Xe7ubWG9v8uKfC.VfzQr/sjb5/gypWNdlBW', 'employee', '2024-08-28 07:10:40', NULL),
(3, 'John', 'john', NULL, NULL, '$2y$10$CiV/f.jO5vIsSi0Fp1Xe7ubWG9v8uKfC.VfzQr/sjb5/gypWNdlBW', 'employee', '2024-08-29 17:11:21', NULL),
(4, 'Oliver', 'oliver', NULL, NULL, '$2y$10$E9Xx8UCsFcw44lfXxiq/5OJtloW381YJnu5lkn6q6uzIPdL5yH3PO', 'employee', '2024-08-29 17:11:34', NULL),
(5, 'Darshan Shrestha', 'Darshan', NULL, NULL, '$2y$10$6vxajtK7n4z/idP9lW96Me8Q3kOj7agF5hJ8V7dkH2O97g6k5gGee', 'admin', '2025-04-04 08:28:05', NULL),
(6, 'Slisha Devkota', 'Slisha', NULL, NULL, '$2y$10$kPZ/nKUYt4uHKG8.K9q6GOE7zzmLr5xK3rFnevj8QMm9U90bbb87G', 'employee', '2025-04-04 08:31:26', NULL),
(7, 'Samprada Shrestha', 'Samprada', 'sampradashrestha064@gmail.com', NULL, '$2y$10$8Kpuu57v/XjnehMKtqVClu9oiq0k3yITS0icmHwOi8O5JzOiYLolO', 'employee', '2025-04-04 08:32:24', NULL),
(8, 'Pratima Gautam', 'Pratima', 'anneushka017@gmail.com', NULL, '$2y$10$dzsbalROwqqtKOpLjo.nsOABWnM/ZQC2fJMNSoST6oRNODfUr2p/a', 'employee', '2025-04-19 14:20:34', NULL),
(9, 'Keshab Acharya', 'Keshab', 'anushkabhattarai017@gmail.com', NULL, '$2y$10$ri33WaP.O0Zjba8KWlhfbOlnDcbi5ckhBdzig27FJce3GP.ZFOUhq', 'employee', '2025-04-19 14:22:23', NULL),
(10, 'Hemant Gupta', 'Hemant', 'b.anushka10@gmail.com', NULL, '$2y$10$ZDJLAMEDaG4FapKEGmS1sujiO/.P3Lyq4g.Y.rVrpWJMvYgPhLG8u', 'employee', '2025-04-19 14:24:20', NULL),
(11, 'Darshan Shrestha', 'Daru', 'darshan@mail.com', NULL, '$2y$10$b5OAT.56TQ4bBVqWiAOHxuXjGFzGFip2zJUNv..lKYXI9Z.K/KJUy', 'employee', '2025-04-20 06:53:35', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `note_comments`
--
ALTER TABLE `note_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `note_id` (`note_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `note_shares`
--
ALTER TABLE `note_shares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_share` (`note_id`,`shared_with`),
  ADD KEY `shared_by` (`shared_by`),
  ADD KEY `shared_with` (`shared_with`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `support_replies`
--
ALTER TABLE `support_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `note_comments`
--
ALTER TABLE `note_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `note_shares`
--
ALTER TABLE `note_shares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_replies`
--
ALTER TABLE `support_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `note_comments`
--
ALTER TABLE `note_comments`
  ADD CONSTRAINT `note_comments_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `note_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `note_shares`
--
ALTER TABLE `note_shares`
  ADD CONSTRAINT `note_shares_ibfk_1` FOREIGN KEY (`