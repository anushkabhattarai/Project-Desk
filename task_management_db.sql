-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2025 at 09:17 AM
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
  `is_private` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `title`, `content`, `user_id`, `status`, `pinned`, `is_private`, `created_at`, `updated_at`) VALUES
(1, 'Welcome Note', 'Welcome to your personal notes! This is a sample note to help you get started.', 5, 'completed', 1, 0, '2025-04-04 08:39:04', '2025-04-04 08:40:48'),
(2, 'Project Ideas', 'List of potential project ideas:\n- Task management system\n- Personal blog\n- E-commerce platform', 2, 'pending', 0, 0, '2025-04-04 08:39:04', '2025-04-04 08:39:04'),
(3, 'Meeting Notes', 'Key points from team meeting:\n- Discussed project timeline\n- Assigned tasks\n- Set next meeting date', 7, 'completed', 0, 0, '2025-04-04 08:39:04', '2025-04-07 08:27:39'),
(9, 'ui ux principles', 'Following UI design best practices makes digital products easier for everyone to use, follow, and enjoy. The benefits of applying UI design principles are many, including:\r\n<div bis_skin_checked=\"1\">\r\n</div><div bis_skin_checked=\"1\">Enhances usability. "Think of a user as someone asking you directions. If you just showed them a map and expected them to memorize it, they\'ll probably get lost," Tom says. "But if you point them to a sign that says their destination is this way, they can follow the signs from there … That\'s a much better experience. UI design principles help you set up signs users can follow towards their goals—one click, scroll, or interaction at a time."\r\n</div><div bis_skin_checked=\"1\">Improves decision-making. Clear and consistent UI design principles give a structured framework for predicting user needs and making informed design choices.\r\n</div><div bis_skin_checked=\"1\">Increases efficiency. Aligning UI design principles at the start of projects lifts the cognitive load for designers, streamlining workflows and making product teams more efficient. Figma data analysts found that participants with access to a design system completed their design objective 34% faster than those without one.\r\n</div><div bis_skin_checked=\"1\">Reduces cognitive load. A well-designed interface can simplify tasks, reducing the mental effort required to complete user actions. Less cognitive load can help create a more intuitive and enjoyable experience.</div>', 1, 'completed', 1, 0, '2025-04-17 08:03:49', '2025-04-17 09:10:29'),
(10, 'hbbh', '<p id=\"bffe\" class=\"pw-post-body-paragraph abj abk wb abl b abm abn abo abp abq abr abs abt abu abv abw abx tw aby abz aca acb acc acd ace acf lf bw\" data-selectable-paragraph=\"\" style=\"box-sizing: inherit; margin: 2.14em 0px -0.46em; color: rgb(36, 36, 36); word-break: break-word; line-height: 32px; letter-spacing: -0.003em; font-family: source-serif-pro, Georgia, Cambria, &quot;Times New Roman&quot;, Times, serif; font-size: 20px;\"><span class=\"abl go\" style=\"box-sizing: inherit; font-weight: 700;\">Technologies we cannot live without:</span></p><ol class=\"\" style=\"box-sizing: inherit; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; padding: 0px; list-style: none none; color: rgba(0, 0, 0, 0.8); font-family: medium-content-sans-serif-font, -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Oxygen, Ubuntu, Cantarell, &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif; font-size: medium;\"><li id=\"ae05\" class=\"abj abk wb abl b abm abn abo abp abq abr abs abt abu abv abw abx tw aby abz aca acb acc acd ace acf akv akw akx bw\" data-selectable-paragraph=\"\" style=\"box-sizing: inherit; color: rgb(36, 36, 36); line-height: 32px; letter-spacing: -0.003em; font-family: source-serif-pro, Georgia, Cambria, &quot;Times New Roman&quot;, Times, serif; margin-bottom: -0.46em; list-style-type: decimal; margin-left: 30px; padding-left: 0px; font-size: 20px; margin-top: 2.14em;\"><span class=\"abl go\" style=\"box-sizing: inherit; font-weight: 700;\">Smartphone&nbsp;</span>— it was the phone that came first to us as an invention with the primary purpose of communication. But then it took up all other functions too, even entertainment related. So now our phone is our TV, music system, work device, communication gadget, camera, and it just doesn't end with all the new apps. So smartphones become one technology we cannot live without.</li><li id=\"4a70\" class=\"abj abk wb abl b abm aky abo abp abq akz abs abt abu ala abw abx tw alb abz aca acb alc acd ace acf akv akw akx bw\" data-selectable-paragraph=\"\" style=\"box-sizing: inherit; color: rgb(36, 36, 36); line-height: 32px; letter-spacing: -0.003em; font-family: source-serif-pro, Georgia, Cambria, &quot;Times New Roman&quot;, Times, serif; margin-bottom: -0.46em; list-style-type: decimal; margin-left: 30px; padding-left: 0px; font-size: 20px; margin-top: 1.14em;\"><span class=\"abl go\" style=\"box-sizing: inherit; font-weight: 700;\">Computer/laptop</span>&nbsp;— With all the aid of technology and its benefits, the whole world and the economy is now digitally performing. This means we need a computer/laptop for our work. All of the work happens on them, so we cannot live without them too. And just like the phone, laptops as well have multi-features that can aid us in different ways, although it primarily is used for only work, gaming becomes a very good example.</li><li id=\"16eb\" class=\"abj abk wb abl b abm aky abo abp abq akz abs abt abu ala abw abx tw alb abz aca acb alc acd ace acf akv akw akx bw\" data-selectable-paragraph=\"\" style=\"box-sizing: inherit; color: rgb(36, 36, 36); line-height: 32px; letter-spacing: -0.003em; font-family: source-serif-pro, Georgia, Cambria, &quot;Times New Roman&quot;, Times, serif; margin-bottom: -0.46em; list-style-type: decimal; margin-left: 30px; padding-left: 0px; font-size: 20px; margin-top: 1.14em;\"><span class=\"abl go\" style=\"box-sizing: inherit; font-weight: 700;\">Internet</span>&nbsp;— After having penned down the smartphone and laptop, I can shout from atop a terrace that this is another inevitable gadget for us. Internet technology is the instrument that takes us out anywhere in the world (with the aid of the above two gadgets). For instance, we could be sitting in a village and looking at Paris on our phone connected to the internet, and not just a video. You know it could be a 360° video too. And besides virtual teleporting, the internet gives us worldwide connectivity; we can talk to anyone with just a tap, thanks to social media.</li><li id=\"ea16\" class=\"abj abk wb abl b abm aky abo abp abq akz abs abt abu ala abw abx tw alb abz aca acb alc acd ace acf akv akw akx bw\" data-selectable-paragraph=\"\" style=\"box-sizing: inherit; color: rgb(36, 36, 36); line-height: 32px; letter-spacing: -0.003em; font-family: source-serif-pro, Georgia, Cambria, &quot;Times New Roman&quot;, Times, serif; margin-bottom: -0.46em; list-style-type: decimal; margin-left: 30px; padding-left: 0px; font-size: 20px; margin-top: 1.14em;\"><span class=\"abl go\" style=\"box-sizing: inherit; font-weight: 700;\">Electricity</span>&nbsp;— Electricity is the prime aspect that is linked to and brings us different other technologies such as the TV, phone, fridge, well practically almost everything. Almost all gadgets run on electricity, so hence it becomes unlivable without it. Electricity supports healthcare, too, just to mark its importance.</li><li id=\"5201\" class=\"abj abk wb abl b abm aky abo abp abq akz abs abt abu ala abw abx tw alb abz aca acb alc acd ace acf akv akw akx bw\" data-selectable-paragraph=\"\" style=\"box-sizing: inherit; color: rgb(36, 36, 36); line-height: 32px; letter-spacing: -0.003em; font-family: source-serif-pro, Georgia, Cambria, &quot;Times New Roman&quot;, Times, serif; margin-bottom: -0.46em; list-style-type: decimal; margin-left: 30px; padding-left: 0px; font-size: 20px; margin-top: 1.14em;\"><span class=\"abl go\" style=\"box-sizing: inherit; font-weight: 700;\">Vehicle</span>&nbsp;— not exactly like we cannot live without it, but then we need it. For long-distance traveling, you can't go walking right. And for emergencies, vehicles, aka ambulances, are lifesavers. Not to mention, it's a delivery-approached world, I mean, we order everything online now, and they're delivered to our location without us having to do anything more than just a tap (have to pay, though).</li><li id=\"1837\" class=\"abj abk wb abl b abm aky abo abp abq akz abs abt abu ala abw abx tw alb abz aca acb alc acd ace acf akv akw akx bw\" data-selectable-paragraph=\"\" style=\"box-sizing: inherit; color: rgb(36, 36, 36); line-height: 32px; letter-spacing: -0.003em; font-family: source-serif-pro, Georgia, Cambria, &quot;Times New Roman&quot;, Times, serif; margin-bottom: -0.46em; list-style-type: decimal; margin-left: 30px; padding-left: 0px; font-size: 20px; margin-top: 1.14em;\"><span class=\"abl go\" style=\"box-sizing: inherit; font-weight: 700;\">Google</span>&nbsp;— Supported combinedly by electricity, the internet, and the phone/computer, simple and straightforward to say, we cannot live without Google. Whatever it is, when it's a question/curiosity, we hop on to google. We get almost all our answers on google. Things we are not comfortable asking the humans. We just search for it on google and get our answers from it. Google answers us, gives us directions, books a restaurant, makes phone calls, finds a lost person, and there's a lot more, but you have to go now.</li></ol>', 7, 'not-started', 1, 0, '2025-05-01 07:15:53', '2025-05-01 07:15:53');

-- --------------------------------------------------------

--
-- Table structure for table `note_comments`
--

CREATE TABLE `note_comments` (
  `id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `note_comments`
--

INSERT INTO `note_comments` (`id`, `note_id`, `user_id`, `comment`, `created_at`) VALUES
(15, 10, 7, 'hey', '2025-05-01 07:16:08');

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
(1, 'Basic Plan', 'Basic plan with limited features. Share up to 5 notes.', 200.00, 10, 5, 5, 0, '2025-04-12 11:38:38'),
(2, 'Premium Plan', 'Premium plan with unlimited features. Unlimited note sharing.', 500.00, NULL, NULL, NULL, 1, '2025-04-12 11:38:38');

-- Update plans table to have clearer sharing limits
UPDATE plans SET 
    description = 'Basic plan with limited features. Share up to 5 notes.',
    note_limit = 10,
    share_limit = 5,
    is_unlimited = 0
WHERE name = 'Basic Plan';

UPDATE plans SET 
    description = 'Premium plan with unlimited features. Unlimited note sharing.',
    note_limit = NULL,
    share_limit = NULL,
    is_unlimited = 1
WHERE name = 'Premium Plan';

-- Add trigger to enforce share limits based on plan
DELIMITER //

CREATE TRIGGER before_note_share_insert
BEFORE INSERT ON note_shares
FOR EACH ROW
BEGIN
    DECLARE user_share_count INT;
    DECLARE user_share_limit INT;
    DECLARE is_unlimited BOOLEAN;
    
    -- Get the user's current share count
    SELECT COUNT(*) INTO user_share_count 
    FROM note_shares 
    WHERE shared_by = NEW.shared_by;
    
    -- Get the user's plan limits
    SELECT p.share_limit, p.is_unlimited INTO user_share_limit, is_unlimited
    FROM plans p 
    INNER JOIN subscriptions s ON p.id = s.plan_id 
    WHERE s.user_id = NEW.shared_by 
    AND s.status = 'active' 
    AND s.end_date >= CURRENT_DATE
    ORDER BY s.created_at DESC 
    LIMIT 1;
    
    -- If no active subscription, use Basic Plan limits
    IF user_share_limit IS NULL THEN
        SET user_share_limit = 5;
        SET is_unlimited = 0;
    END IF;
    
    -- Check if user has reached their share limit
    IF NOT is_unlimited AND user_share_count >= user_share_limit THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Share limit reached for your current plan';
    END IF;
END;
//

DELIMITER ;

-- Add an index to improve performance of share limit checking
CREATE INDEX idx_note_shares_shared_by ON note_shares(shared_by);

-- Add an index for active subscriptions
CREATE INDEX idx_active_subscriptions ON subscriptions(user_id, status, end_date);

-- Add view to easily check user's sharing stats
CREATE OR REPLACE VIEW user_sharing_stats AS
SELECT 
    u.id as user_id,
    u.full_name,
    COUNT(DISTINCT ns.note_id) as total_shares,
    COALESCE(p.share_limit, 5) as share_limit,
    COALESCE(p.is_unlimited, 0) as is_unlimited
FROM users u
LEFT JOIN note_shares ns ON u.id = ns.shared_by
LEFT JOIN subscriptions s ON u.id = s.user_id 
    AND s.status = 'active' 
    AND s.end_date >= CURRENT_DATE
LEFT JOIN plans p ON s.plan_id = p.id
GROUP BY u.id, u.full_name, p.share_limit, p.is_unlimited;

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

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan_id`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(2, 7, 1, '2025-05-01', '2025-05-31', 'active', '2025-05-01 07:10:54');

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

-- --------------------------------------------------------

--
-- Table structure for table `private_notes`
--

CREATE TABLE `private_notes` (
  `note_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`note_id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `note_comments`
--
ALTER TABLE `note_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  ADD CONSTRAINT `note_shares_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `note_shares_ibfk_2` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `note_shares_ibfk_3` FOREIGN KEY (`shared_with`) REFERENCES `users` (`id`);

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`);

--
-- Constraints for table `support_replies`
--
ALTER TABLE `support_replies`
  ADD CONSTRAINT `support_replies_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
-- Add after the existing tables but before the final COMMIT




