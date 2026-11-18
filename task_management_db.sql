-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 18, 2026 at 11:49 AM
-- Server version: 8.4.7
-- PHP Version: 8.5.0

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
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
CREATE TABLE IF NOT EXISTS `department` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `name`, `created_at`) VALUES
(1, 'Engineering', '2026-11-18 01:18:24'),
(2, 'Human Resources', '2026-11-18 01:18:24'),
(3, 'Marketing', '2026-11-18 01:18:24'),
(4, 'Finance', '2026-11-18 01:18:24'),
(5, 'Customer Support', '2026-11-18 01:18:24'),
(6, 'Engineering', '2026-11-18 01:20:59'),
(7, 'Human Resources', '2026-11-18 01:20:59'),
(8, 'Marketing', '2026-11-18 01:20:59'),
(9, 'Finance', '2026-11-18 01:20:59'),
(10, 'Customer Support', '2026-11-18 01:20:59');

-- --------------------------------------------------------

--
-- Table structure for table `departmentmembers`
--

DROP TABLE IF EXISTS `departmentmembers`;
CREATE TABLE IF NOT EXISTS `departmentmembers` (
  `department_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` enum('admin','employee') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`department_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departmentmembers`
--

INSERT INTO `departmentmembers` (`department_id`, `user_id`, `role`) VALUES
(1, 2, 'employee'),
(1, 3, 'employee'),
(1, 11, 'employee'),
(2, 4, 'admin'),
(2, 5, 'employee'),
(3, 7, 'admin'),
(3, 6, 'employee'),
(4, 8, 'employee'),
(4, 12, 'employee'),
(5, 9, 'employee'),
(5, 10, 'employee');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `message`, `recipient`, `type`, `date`, `is_read`) VALUES
(1, 'You have been assigned a new task: Fix login bug', 2, 'task_assigned', '2026-11-18 01:20:59', 0),
(2, 'You have been assigned a new task: Design homepage banner', 6, 'task_assigned', '2026-11-18 01:20:59', 1),
(3, 'Task \"Code review PR #142\" is due in 2 days', 3, 'deadline_reminder', '2026-11-18 01:20:59', 0),
(4, 'New employee David Brown added to HR department', 4, 'department_update', '2026-11-18 01:20:59', 1),
(5, 'Task \"Monthly expense report\" status changed to in_progress', 8, 'status_update', '2026-11-18 01:20:59', 0),
(6, 'You have been assigned a new task: Respond to support tickets', 9, 'task_assigned', '2026-11-18 01:20:59', 0),
(7, 'Task \"SEO keyword research\" has been completed', 6, 'task_completed', '2026-11-18 01:20:59', 1),
(8, 'You have been assigned a new task: Client feedback survey', 10, 'task_assigned', '2026-11-18 01:20:59', 0),
(9, 'Weekly backup verification starting soon', 11, 'system_alert', '2026-11-18 01:20:59', 1),
(10, 'Task \"Database backup verification\" is overdue', 11, 'overdue_alert', '2026-11-18 01:20:59', 0),
(11, 'You have been assigned a new task: Write blog post: AI tools', 6, 'task_assigned', '2026-11-18 01:20:59', 0),
(12, 'Task \"Social media calendar\" has been completed', 7, 'task_completed', '2026-11-18 01:20:59', 1),
(13, 'Performance review cycle has started', 4, 'system_alert', '2026-11-18 01:20:59', 0),
(14, 'You have been assigned a new task: Budget forecast Q1 2027', 8, 'task_assigned', '2026-11-18 01:20:59', 0),
(15, 'Task \"Onboard new support agent\" has been completed', 9, 'task_completed', '2026-11-18 01:20:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `assigned_to` int DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `due_date`, `status`, `created_at`) VALUES
(1, 'Fix login bug', 'Users cannot reset password on mobile.', 2, '2026-12-15', 'pending', '2026-11-18 01:20:59'),
(2, 'Design homepage banner', 'Create 3 banner variations for Q1 campaign.', 6, '2026-12-10', 'in_progress', '2026-11-18 01:20:59'),
(3, 'Update employee handbook', 'Add new remote work policy section.', 5, '2026-11-30', 'pending', '2026-11-18 01:20:59'),
(4, 'Monthly expense report', 'Compile November expenses for all departments.', 8, '2026-12-05', 'in_progress', '2026-11-18 01:20:59'),
(5, 'Respond to support tickets', 'Clear backlog of unresolved customer tickets.', 9, '2026-12-01', 'pending', '2026-11-18 01:20:59'),
(6, 'Code review PR #142', 'Review authentication refactor before merge.', 3, '2026-12-03', 'completed', '2026-11-18 01:20:59'),
(7, 'Prepare onboarding docs', 'Document workflow for new hires in HR.', 4, '2026-12-20', 'pending', '2026-11-18 01:20:59'),
(8, 'SEO keyword research', 'Analyze top 20 keywords for next blog post.', 6, '2026-12-08', 'completed', '2026-11-18 01:20:59'),
(9, 'Database backup verification', 'Verify weekly backup integrity and restore test.', 11, '2026-12-04', 'in_progress', '2026-11-18 01:20:59'),
(10, 'Client feedback survey', 'Draft survey for Q4 customer satisfaction.', 10, '2026-12-12', 'pending', '2026-11-18 01:20:59'),
(11, 'Fix payment gateway error', 'Intermittent 500 error on checkout.', 2, '2026-12-06', 'pending', '2026-11-18 01:20:59'),
(12, 'Quarterly tax filing prep', 'Gather receipts and invoices for Q4.', 8, '2026-12-25', 'pending', '2026-11-18 01:20:59'),
(13, 'API documentation update', 'Add new endpoints to developer docs.', 3, '2026-12-09', 'in_progress', '2026-11-18 01:20:59'),
(14, 'Hiring plan 2027', 'Draft headcount plan for all departments.', 4, '2026-12-18', 'pending', '2026-11-18 01:20:59'),
(15, 'Social media calendar', 'Schedule posts for December and January.', 7, '2026-12-07', 'completed', '2026-11-18 01:20:59'),
(16, 'Performance review cycle', 'Send review templates to all managers.', 4, '2026-12-11', 'in_progress', '2026-11-18 01:20:59'),
(17, 'Refactor notification service', 'Improve delivery reliability for emails.', 11, '2026-12-14', 'pending', '2026-11-18 01:20:59'),
(18, 'Onboard new support agent', 'Set up accounts and training schedule.', 9, '2026-12-02', 'completed', '2026-11-18 01:20:59'),
(19, 'Budget forecast Q1 2027', 'Create forecast spreadsheet for Finance.', 8, '2026-12-22', 'pending', '2026-11-18 01:20:59'),
(20, 'Write blog post: AI tools', 'Draft 1200-word article for company blog.', 6, '2026-12-13', 'pending', '2026-11-18 01:20:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','employee') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `contact` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department` (`department`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `password`, `role`, `created_at`, `contact`, `department`) VALUES
(1, 'kemzy wil', 'kemzy', '$2y$12$tOnmcH1YLbthk9aAXSOURu/ITMTWqAVAGHYaqXEnn78wx6ZqTsTg.', 'admin', '2026-11-18 01:13:44', '653426838', NULL),
(2, 'Alice Johnson', 'alice', '$2y$10$JCp30cpY3Cc2cl2Y6aefOe.1HTW9ZYRT1iQ2GTuSpoBww0e.l2bxy', 'employee', '2026-11-18 01:20:59', '712345001', 1),
(3, 'Bob Smith', 'bob', '$2y$12$tOnmcH1YLbthk9aAXSOURu/ITMTWqAVAGHYaqXEnn78wx6ZqTsTg.', 'employee', '2026-11-18 01:20:59', '712345002', 1),
(4, 'Carol White', 'carol', '$2y$12$tOnmcH1YLbthk9aAXSOURu/ITMTWqAVAGHYaqXEnn78wx6ZqTsTg.', 'admin', '2026-11-18 01:20:59', '712345003', 2),
(5, 'David Brown', 'david', '$2y$10$JCp30cpY3Cc2cl2Y6aefOe.1HTW9ZYRT1iQ2GTuSpoBww0e.l2bxy', 'employee', '2026-11-18 01:20:59', '712345004', 2),
(6, 'Emma Davis', 'emma', '$2y$10$JCp30cpY3Cc2cl2Y6aefOe.1HTW9ZYRT1iQ2GTuSpoBww0e.l2bxy', 'employee', '2026-11-18 01:20:59', '712345005', 3),
(7, 'Frank Miller', 'frank', '$2y$10$JCp30cpY3Cc2cl2Y6aefOe.1HTW9ZYRT1iQ2GTuSpoBww0e.l2bxy', 'admin', '2026-11-18 01:20:59', '712345006', 3),
(8, 'Grace Lee', 'grace', '$2y$10$JCp30cpY3Cc2cl2Y6aefOe.1HTW9ZYRT1iQ2GTuSpoBww0e.l2bxy', 'employee', '2026-11-18 01:20:59', '712345007', 4),
(9, 'Henry Wilson', 'henry', '$2y$10$JCp30cpY3Cc2cl2Y6aefOe.1HTW9ZYRT1iQ2GTuSpoBww0e.l2bxy', 'employee', '2026-11-18 01:20:59', '712345008', 5),
(10, 'Ivy Martinez', 'ivy', '$2y$10$JCp30cpY3Cc2cl2Y6aefOe.1HTW9ZYRT1iQ2GTuSpoBww0e.l2bxy', 'employee', '2026-11-18 01:20:59', '712345009', 5),
(11, 'Jack Taylor', 'jack', '$2y$10$JCp30cpY3Cc2cl2Y6aefOe.1HTW9ZYRT1iQ2GTuSpoBww0e.l2bxy', 'employee', '2026-11-18 01:20:59', '712345010', 1),
(12, 'Kate Anderson', 'kate', '$2y$10$JCp30cpY3Cc2cl2Y6aefOe.1HTW9ZYRT1iQ2GTuSpoBww0e.l2bxy', 'employee', '2026-11-18 01:20:59', '712345011', 4);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
