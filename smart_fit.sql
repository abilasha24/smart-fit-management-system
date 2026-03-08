-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 08, 2026 at 04:49 PM
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
-- Database: `smart_fit`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_recommendation_logs`
--

CREATE TABLE `ai_recommendation_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `bmi_category` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `target_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bmi_records`
--

CREATE TABLE `bmi_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `height_cm` decimal(5,2) NOT NULL,
  `weight_kg` decimal(5,2) NOT NULL,
  `bmi_value` decimal(5,2) NOT NULL,
  `category` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bmi_records`
--

INSERT INTO `bmi_records` (`id`, `user_id`, `height_cm`, `weight_kg`, `bmi_value`, `category`, `created_at`) VALUES
(4, 6, 155.00, 40.00, 16.65, 'Underweight', '2026-02-18 11:08:30'),
(5, 6, 155.00, 50.00, 20.81, 'Overweight', '2026-02-18 11:27:25'),
(6, 11, 168.00, 54.00, 19.13, 'Normal', '2026-02-18 18:22:52'),
(7, 11, 168.00, 54.00, 19.13, 'Normal', '2026-02-18 18:22:55'),
(8, 14, 165.00, 55.00, 20.20, 'Normal', '2026-02-24 15:18:19'),
(9, 14, 160.00, 85.00, 33.20, 'Obese', '2026-02-24 15:19:17'),
(10, 14, 175.00, 35.00, 11.43, 'Underweight', '2026-02-24 15:20:09'),
(11, 14, 150.00, 75.00, 33.33, 'Obese', '2026-02-24 15:20:46'),
(12, 14, 150.00, 45.00, 20.00, 'Normal', '2026-02-24 15:20:55'),
(13, 14, 180.00, 50.00, 15.43, 'Underweight', '2026-02-24 17:16:09'),
(14, 14, 165.00, 85.00, 31.22, 'Obese', '2026-02-24 17:21:02'),
(15, 14, 155.00, 38.00, 15.82, 'Underweight', '2026-02-24 17:21:42'),
(16, 14, 130.00, 75.00, 44.38, 'Obese', '2026-02-24 17:22:11'),
(18, 13, 168.00, 65.00, 23.03, 'Normal', '2026-02-24 17:54:26'),
(21, 37, 169.00, 60.00, 21.01, 'Normal', '2026-02-26 16:57:46'),
(22, 38, 150.00, 75.00, 33.33, 'Obese', '2026-02-27 05:44:04'),
(23, 38, 150.00, 65.00, 28.89, 'Overweight', '2026-02-27 05:44:12'),
(24, 3, 165.00, 65.00, 23.88, 'Normal', '2026-02-28 17:52:39'),
(25, 4, 165.00, 57.00, 20.94, 'Normal', '2026-03-01 09:08:41'),
(26, 4, 150.00, 75.00, 33.33, 'Obese', '2026-03-01 09:26:06'),
(27, 14, 155.00, 38.00, 15.82, 'Underweight', '2026-03-01 09:36:15'),
(28, 4, 175.00, 50.00, 16.33, 'Underweight', '2026-03-01 12:31:08'),
(29, 5, 145.00, 50.00, 23.78, 'Normal', '2026-03-01 12:44:52'),
(30, 39, 165.00, 50.00, 18.37, 'Underweight', '2026-03-01 12:52:39'),
(31, 37, 170.00, 55.00, 19.03, 'Normal', '2026-03-01 13:22:11'),
(32, 37, 140.00, 70.00, 35.71, 'Obese', '2026-03-01 13:22:24'),
(33, 39, 170.00, 40.00, 13.84, 'Underweight', '2026-03-01 13:28:33'),
(34, 39, 160.00, 85.00, 33.20, 'Obese', '2026-03-01 14:21:41'),
(35, 39, 150.00, 40.00, 17.78, 'Underweight', '2026-03-01 14:37:30'),
(36, 39, 145.00, 75.00, 35.67, 'Obese', '2026-03-01 14:55:45'),
(37, 39, 164.00, 55.00, 20.45, 'Normal', '2026-03-01 15:07:54'),
(38, 39, 155.00, 48.00, 19.98, 'Normal', '2026-03-01 15:09:10'),
(39, 39, 148.00, 60.00, 27.39, 'Overweight', '2026-03-01 15:09:45'),
(40, 39, 145.00, 59.00, 28.06, 'Overweight', '2026-03-01 15:12:53'),
(41, 39, 175.00, 60.00, 19.59, 'Normal', '2026-03-01 15:19:03'),
(42, 39, 155.00, 40.00, 16.65, 'Underweight', '2026-03-01 15:22:40'),
(43, 39, 172.00, 59.00, 19.94, 'Normal', '2026-03-01 15:31:26'),
(44, 40, 172.00, 65.00, 21.97, 'Normal', '2026-03-01 21:28:08'),
(45, 40, 150.00, 85.00, 37.78, 'Obese', '2026-03-01 21:28:26'),
(46, 3, 168.00, 72.00, 25.51, 'Overweight', '2026-03-07 19:38:09'),
(47, 3, 168.00, 75.00, 26.57, 'Overweight', '2026-03-07 19:42:48'),
(48, 38, 152.00, 69.00, 29.86, 'Overweight', '2026-03-08 06:23:43'),
(49, 38, 177.00, 95.00, 30.32, 'Obese', '2026-03-08 10:38:57'),
(50, 38, 155.00, 70.00, 29.14, 'Overweight', '2026-03-08 10:40:18');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `subject`, `message`, `rating`, `created_at`, `is_read`) VALUES
(1, 3, 'body build', 'good training', 4, '2026-02-18 07:40:09', 1),
(2, 3, 'body gain', 'best fit', 4, '2026-02-18 07:44:08', 1),
(3, 5, 'full body massage', 'best hides', 5, '2026-02-18 08:15:23', 1),
(4, 6, 'full body gain', 'best training', 5, '2026-02-18 09:01:46', 1),
(6, 13, 'body gain', 'best train', 4, '2026-02-24 18:09:16', 1),
(7, 37, 'body gain', 'best all', 4, '2026-02-26 16:57:25', 0);

-- --------------------------------------------------------

--
-- Table structure for table `meal_follow_log`
--

CREATE TABLE `meal_follow_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `log_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meal_follow_log`
--

INSERT INTO `meal_follow_log` (`id`, `user_id`, `log_date`, `created_at`) VALUES
(1, 14, '2026-02-22', '2026-02-22 17:08:01'),
(4, 14, '2026-02-23', '2026-02-22 19:51:53'),
(5, 14, '2026-02-24', '2026-02-23 19:12:06'),
(14, 13, '2026-02-24', '2026-02-24 17:53:16'),
(18, 13, '2026-02-25', '2026-02-24 19:04:36'),
(28, 11, '2026-02-25', '2026-02-25 12:42:03'),
(42, 5, '2026-02-25', '2026-02-25 13:21:13'),
(93, 14, '2026-02-25', '2026-02-25 14:42:22'),
(98, 14, '2026-02-26', '2026-02-26 11:44:06'),
(99, 30, '2026-02-26', '2026-02-26 11:48:04'),
(100, 37, '2026-02-26', '2026-02-26 16:56:45'),
(102, 4, '2026-02-27', '2026-02-26 18:41:15'),
(103, 13, '2026-02-27', '2026-02-26 18:54:59'),
(104, 14, '2026-02-27', '2026-02-26 18:55:37'),
(105, 38, '2026-02-27', '2026-02-26 18:56:14'),
(109, 39, '2026-02-27', '2026-02-27 06:05:28'),
(110, 11, '2026-02-28', '2026-02-28 05:41:31'),
(112, 38, '2026-02-28', '2026-02-28 14:24:37'),
(113, 14, '2026-02-28', '2026-02-28 17:35:23'),
(114, 3, '2026-02-28', '2026-02-28 17:39:45'),
(115, 3, '2026-03-01', '2026-02-28 18:43:40'),
(119, 4, '2026-03-01', '2026-03-01 09:22:48'),
(120, 39, '2026-03-01', '2026-03-01 16:18:31'),
(123, 14, '2026-03-01', '2026-03-01 17:26:17'),
(124, 37, '2026-03-02', '2026-03-01 21:21:56'),
(125, 40, '2026-03-02', '2026-03-01 21:27:01'),
(126, 3, '2026-03-04', '2026-03-03 20:00:19'),
(127, 38, '2026-03-04', '2026-03-04 06:21:17'),
(128, 3, '2026-03-06', '2026-03-06 12:20:24'),
(129, 38, '2026-03-08', '2026-03-08 04:01:51');

-- --------------------------------------------------------

--
-- Table structure for table `meal_plans`
--

CREATE TABLE `meal_plans` (
  `id` int(11) NOT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `plan_type` enum('basic','premium','pro') NOT NULL DEFAULT 'basic'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meal_plans`
--

INSERT INTO `meal_plans` (`id`, `trainer_id`, `title`, `content`, `created_at`, `plan_type`) VALUES
(3, 9, 'Fat loss', 'Goal: Fat loss (small calorie deficit)\nBreakfast: Eggs + fruit\nLunch: Rice (small) + dhal + chicken/fish + salad\nSnack: Yogurt / nuts\nDinner: Vegetables + protein\nWater: 2–3L • Walk: 8k steps • Sleep: 7–8h', '2026-02-22 13:07:51', 'basic'),
(4, 9, 'Maintain weight & energy', 'Goal: Maintain weight & energy\nBreakfast: Eggs/oats + fruit\nLunch: 1/2 plate veggies + 1/4 protein + 1/4 carbs\nSnack: Fruit / yogurt / nuts\nDinner: Light meal + protein\nWater: 2L • Steps: 6k–8k • Sleep: 7h', '2026-02-22 19:10:12', 'basic'),
(5, 9, 'Weight Loss Meal Plan (Beginner)', 'Goal: Fat loss (small calorie deficit)\n\nBreakfast: 2 eggs + fruit / oats + curd\n\nLunch: 1/2 plate veggies + 1/4 protein + 1/4 rice\n\nSnack: Green tea + nuts (small) / yogurt\n\nDinner: Veggies + grilled fish/chicken/dhal\n\nWater: 2–3L | Steps: 8k | Sleep: 7–8h', '2026-02-23 18:40:58', 'basic'),
(6, 9, 'Weight Loss (Low Carb Night)', 'Breakfast: Omelette + salad\n\nLunch: Rice (small) + dhal + chicken/fish + salad\n\nSnack: Fruit / peanuts\n\nDinner: Soup + protein (no rice)\n\nNotes: Avoid sugar drinks + late snacks', '2026-02-23 18:41:36', 'premium'),
(7, 9, 'Muscle Gain Meal Plan (Beginner)', 'Goal: slight surplus\n\nBreakfast: Oats + milk + banana + nuts\n\nLunch: Rice + dhal + chicken/fish + veggies\n\nSnack: Peanut butter toast / curd\n\nDinner: Protein + carbs + veggies\n\nWater: 2–3L | Strength: 4 days/week', '2026-02-23 18:41:57', 'basic'),
(8, 9, 'Lean Bulk (Intermediate)', 'Breakfast: Eggs + oats\n\nSnack: Protein smoothie (milk + banana + peanut)\n\nLunch: Rice + dhal + chicken + veggies\n\nSnack: Boiled chickpeas / yogurt\n\nDinner: Fish + potato/sweet potato + salad', '2026-02-23 18:42:18', 'pro'),
(9, 9, 'Maintenance (Busy Worker)', 'Breakfast: Eggs/oats + fruit\n\nLunch: Balanced plate rule\n\nSnack: Nuts / yogurt\n\nDinner: Light meal + protein\n\nSteps: 6k–8k', '2026-02-23 18:42:47', 'pro'),
(10, 9, 'High Protein (Fat Loss + Gym)', 'Protein target: weight×1.6g\n\n3–4 meals protein include\n\nBreakfast: eggs + curd\n\nLunch: chicken/fish + dhal + salad\n\nDinner: fish/chicken + veggies\n\nSnack: peanuts / yogurt', '2026-02-23 18:43:10', 'pro'),
(11, 9, 'Vegetarian Fat Loss', 'Breakfast: Oats + banana / string hoppers + sambol (small)\n\nLunch: Red rice (small) + dhal + gotukola + veggies\n\nSnack: peanuts / fruit\n\nDinner: Soup + tofu/egg/dhal\n\nNotes: Add curd for protein', '2026-02-23 18:43:33', 'basic'),
(12, 9, 'Vegetarian Muscle Gain', 'Breakfast: Oats + milk + nuts\n\nLunch: Rice + dhal + chickpeas + veggies\n\nSnack: Peanut butter sandwich\n\nDinner: Egg/tofu + carbs + veggies', '2026-02-23 18:43:57', 'basic'),
(13, 9, 'Diabetes-Friendly Plan (General)', 'Low sugar, high fiber\n\nBreakfast: oats + nuts (no sugar)\n\nLunch: brown/red rice (small) + veggies + fish\n\nSnack: guava/apple\n\nDinner: veggies + protein\n\nNote: consult doctor if needed', '2026-02-23 18:44:18', 'premium'),
(14, 9, 'PCOS-friendly Balanced Plan', 'Focus: low GI carbs + protein\n\nBreakfast: eggs + salad\n\nLunch: rice small + dhal + veggies\n\nSnack: nuts / yogurt\n\nDinner: soup + fish/chicken\n\nWalk daily 30 mins', '2026-02-23 18:44:42', 'premium'),
(15, 9, 'Endurance/Cardio Support', 'Breakfast: carbs + protein (oats + milk)\n\nPre-workout: banana\n\nPost-workout: curd/egg\n\nLunch: rice + fish + veggies\n\nDinner: light carbs + protein', '2026-02-23 18:45:01', 'premium'),
(16, 9, 'Strength Day Meal Plan', 'Pre: banana + coffee\n\nPost: milk + oats / eggs\n\nLunch: rice + chicken + veggies\n\nSnack: peanuts/curd\n\nDinner: fish + salad + sweet potato', '2026-02-23 18:45:24', 'pro'),
(17, 9, 'Underweight Weight Gain (Safe)', 'Add healthy calories\n\nBreakfast: oats + milk + banana + nuts\n\nLunch: rice + dhal + chicken/fish + veggies\n\nSnack: smoothie + peanut\n\nDinner: egg + carbs + veggies\n\nStrength training recommended', '2026-02-23 18:45:56', 'basic'),
(18, 9, 'Sri Lankan Simple Plan (Budget)', 'Breakfast: string hoppers/roti + dhal\n\nLunch: red rice + dhal + vegetable curry + egg/fish\n\nSnack: fruit\n\nDinner: soup + egg/fish + veggies', '2026-02-23 18:46:17', 'basic'),
(19, 9, 'Weekend Reset (2 days)', 'Hydration 3L\n\nNo sugar drinks\n\n2 meals balanced + 1 light meal\n\nAdd fruits + salads\n\nWalk 10k steps/day', '2026-02-23 18:46:36', 'basic'),
(20, 9, 'Vegetarian Muscle Gain', 'Breakfast: Oats + milk + nuts\n\nLunch: Rice + dhal + chickpeas + veggies\n\nSnack: Peanut butter sandwich\n\nDinner: Egg/tofu + carbs + veggies', '2026-02-26 17:11:39', 'basic');

-- --------------------------------------------------------

--
-- Table structure for table `meal_plan_logs`
--

CREATE TABLE `meal_plan_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `log_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_plan_logs`
--

INSERT INTO `meal_plan_logs` (`id`, `user_id`, `plan_id`, `log_date`, `created_at`) VALUES
(1, 14, 3, '2026-02-22', '2026-02-22 19:33:00');

-- --------------------------------------------------------

--
-- Table structure for table `member_profiles`
--

CREATE TABLE `member_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `height_cm` int(11) DEFAULT NULL,
  `weight_kg` int(11) DEFAULT NULL,
  `fitness_level` varchar(30) DEFAULT NULL,
  `goal` varchar(40) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `member_profiles`
--

INSERT INTO `member_profiles` (`id`, `user_id`, `dob`, `gender`, `height_cm`, `weight_kg`, `fitness_level`, `goal`, `updated_at`) VALUES
(2, 3, '2007-09-18', 'female', 168, 75, '0', 'flexibility', '2026-03-07 19:42:48'),
(3, 4, '2015-05-18', 'male', 170, 60, '0', 'muscle_gain', '2026-02-18 07:34:13'),
(4, 5, '2005-11-18', 'male', 160, 45, '0', 'muscle_gain', '2026-02-18 08:13:39'),
(5, 6, '2000-01-18', 'female', 156, 50, '0', 'endurance', '2026-02-18 08:59:56'),
(7, 11, '2010-03-18', 'female', 168, 54, '0', 'flexibility', '2026-02-18 18:21:05'),
(9, 13, '2026-02-22', 'male', 167, 60, '0', 'weight_loss', '2026-02-22 13:17:34'),
(21, 30, '2010-03-26', 'female', 175, 68, '0', 'flexibility', '2026-02-26 10:34:50'),
(28, 37, '1999-09-26', 'female', 169, 60, '0', 'general_fitness', '2026-02-26 16:52:27'),
(29, 38, '1994-01-26', 'female', 155, 70, '0', 'endurance', '2026-03-08 10:40:18'),
(30, 39, '1990-05-27', 'female', 172, 59, '0', 'weight_loss', '2026-03-01 15:31:26'),
(31, 40, '2000-07-02', 'female', 150, 85, '0', 'general_fitness', '2026-03-01 21:28:26'),
(32, 41, '2000-05-08', 'female', 167, 60, '0', 'weight_loss', '2026-03-07 19:22:42'),
(33, 42, '1999-02-08', 'female', 175, 60, '0', 'general_fitness', '2026-03-08 09:38:05');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(2, 3, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 1, '2026-02-18 05:58:51'),
(3, 4, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 1, '2026-02-18 07:34:13'),
(4, 5, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 1, '2026-02-18 08:13:39'),
(5, 6, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 1, '2026-02-18 08:59:56'),
(7, 11, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 1, '2026-02-18 18:21:05'),
(9, 13, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 1, '2026-02-22 13:17:34'),
(10, 11, 'New workout assigned', 'Trainer assigned: Yoga Stretch. Please start today.', 1, '2026-02-25 04:44:03'),
(11, 3, 'New workout assigned', 'Trainer assigned: Yoga Stretch. Please start today.', 1, '2026-02-25 05:11:31'),
(12, 3, 'New workout assigned', 'Trainer assigned: Full Body Beginner. Please start today.', 1, '2026-02-25 05:11:44'),
(13, 4, 'New schedule added', 'Trainer scheduled: weekly check in (2026-02-25 16:31)', 1, '2026-02-25 07:01:22'),
(14, 4, 'New schedule added', 'Trainer scheduled: weekly check in (2026-02-25 16:31)', 1, '2026-02-25 07:05:12'),
(15, 9, 'Schedule completed', 'Member #4 completed: weekly check-in (2026-02-28 15:20)', 1, '2026-02-25 07:19:55'),
(16, 9, 'Schedule completed', 'Member #4 completed: weekly check in (2026-02-25 16:31)', 1, '2026-02-25 09:44:15'),
(17, 9, 'Schedule completed', 'Member #4 completed: weekly check in (2026-02-25 16:31)', 1, '2026-02-25 09:51:48'),
(29, 30, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 0, '2026-02-26 10:34:50'),
(36, 30, 'New workout assigned', 'Trainer assigned: Upper Body Strength. Please start today.', 0, '2026-02-26 11:46:16'),
(37, 37, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 1, '2026-02-26 16:52:27'),
(38, 37, 'New workout assigned', 'Trainer assigned: Endurance Circuit. Please start today.', 1, '2026-02-26 17:13:32'),
(40, 38, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 1, '2026-02-26 18:10:10'),
(41, 13, 'New schedule added', 'Trainer scheduled: weekly check in (2026-02-27 16:00)', 1, '2026-02-26 18:33:31'),
(42, 13, 'New schedule added', 'Trainer scheduled: weekly check in (2026-02-27 16:00)', 1, '2026-02-26 18:33:56'),
(43, 9, 'Schedule completed', 'Member #13 completed: weekly check in (2026-02-27 16:00)', 1, '2026-02-26 18:36:12'),
(44, 39, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 1, '2026-02-27 05:46:57'),
(45, 6, 'New workout assigned', 'Trainer assigned: Leg Day Power. Please start today.', 1, '2026-02-28 17:16:25'),
(46, 39, 'New workout assigned', 'Trainer assigned: Endurance Circuit. Please start today.', 1, '2026-02-28 17:25:21'),
(47, 39, 'New workout assigned', 'Trainer assigned: HIIT Fat Loss. Please start today.', 0, '2026-02-28 17:25:55'),
(48, 40, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 0, '2026-03-01 21:25:48'),
(49, 41, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 0, '2026-03-07 19:22:42'),
(50, 42, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 0, '2026-03-08 09:38:05');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_email` varchar(120) NOT NULL,
  `plan` enum('basic','premium','pro') NOT NULL,
  `billing_cycle` enum('monthly','yearly') NOT NULL DEFAULT 'monthly',
  `payment_method` enum('card','bank','mobile','free') NOT NULL DEFAULT 'card',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `user_email`, `plan`, `billing_cycle`, `payment_method`, `amount`, `created_at`) VALUES
(2, 3, 'akshi@gmail.com', 'basic', 'monthly', 'free', 0.00, '2026-02-18 05:58:51'),
(3, 4, 'akshay@gmail.com', 'basic', 'monthly', 'free', 0.00, '2026-02-18 07:34:13'),
(4, 5, 'aathi@gmail.com', 'pro', 'monthly', 'card', 5000.00, '2026-02-18 08:13:39'),
(5, 6, 'vasu@gmail.com', 'pro', 'monthly', 'card', 5000.00, '2026-02-18 08:59:56'),
(7, 11, 'asha@gmail.com', 'basic', 'monthly', 'free', 0.00, '2026-02-18 18:21:05'),
(9, 13, 'aathi2@gmail.com', 'pro', 'monthly', 'card', 5000.00, '2026-02-22 13:17:34'),
(22, 30, 'sa@gmail.com', 'premium', 'monthly', 'card', 2500.00, '2026-02-26 10:34:50'),
(31, 37, 'pr@gmail.com', 'premium', 'monthly', 'card', 2500.00, '2026-02-26 16:52:27'),
(32, 37, 'pr@gmail.com', 'premium', 'monthly', '', 2500.00, '2026-02-26 16:54:35'),
(33, 38, 'aabhi@gmail.com', 'pro', 'monthly', 'card', 5000.00, '2026-02-26 18:10:10'),
(34, 38, 'aabhi@gmail.com', 'pro', 'monthly', '', 5000.00, '2026-02-26 18:10:30'),
(35, 39, 'nila@gmail.com', 'premium', 'monthly', 'card', 2500.00, '2026-02-27 05:46:57'),
(36, 39, 'nila@gmail.com', 'premium', 'monthly', '', 2500.00, '2026-02-27 06:03:49'),
(37, 40, 'jj@gmail.com', 'pro', 'monthly', 'card', 5000.00, '2026-03-01 21:25:48'),
(38, 40, 'jj@gmail.com', 'pro', 'monthly', '', 5000.00, '2026-03-01 21:26:26'),
(39, 41, 'jj1@gmail.com', 'pro', 'monthly', 'card', 5000.00, '2026-03-07 19:22:42'),
(40, 41, 'jj1@gmail.com', 'pro', 'monthly', '', 5000.00, '2026-03-07 19:25:49'),
(41, 42, 'aabi@gmail.com', 'premium', 'monthly', 'card', 2500.00, '2026-03-08 09:38:05');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `code` enum('basic','premium','pro') NOT NULL,
  `monthly_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duration_days` int(11) NOT NULL DEFAULT 30,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `code`, `monthly_price`, `duration_days`, `status`) VALUES
(1, 'basic', 0.00, 30, 'active'),
(2, 'premium', 2500.00, 30, 'active'),
(3, 'pro', 5000.00, 30, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `title` varchar(120) NOT NULL,
  `schedule_date` date NOT NULL,
  `schedule_time` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by_role` enum('trainer','member') NOT NULL DEFAULT 'trainer',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `user_id`, `trainer_id`, `title`, `schedule_date`, `schedule_time`, `notes`, `created_by_role`, `created_at`, `status`, `completed_at`) VALUES
(2, 4, 9, 'weekly check-in', '2026-02-28', '15:20:00', 'yoga', 'trainer', '2026-02-25 12:20:28', 'done', '2026-02-25 12:49:55'),
(3, 4, 9, 'weekly check in', '2026-02-25', '16:31:00', 'akshay', 'trainer', '2026-02-25 12:31:22', 'done', '2026-02-25 15:21:48'),
(4, 4, 9, 'weekly check in', '2026-02-25', '16:31:00', 'akshay', 'trainer', '2026-02-25 12:35:12', 'done', '2026-02-25 15:14:15'),
(5, 13, 9, 'weekly check in', '2026-02-27', '16:00:00', 'work pending', 'trainer', '2026-02-27 00:03:31', 'pending', NULL),
(6, 13, 9, 'weekly check in', '2026-02-27', '16:00:00', 'work pending', 'trainer', '2026-02-27 00:03:56', 'done', '2026-02-27 00:06:12'),
(7, 13, NULL, 'water reminder', '2026-02-28', '03:03:00', 'sleep', 'member', '2026-02-27 00:07:56', 'done', '2026-02-27 00:08:09');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `order_id` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_code` varchar(20) NOT NULL,
  `billing_cycle` varchar(20) NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `order_id`, `user_id`, `plan_code`, `billing_cycle`, `payment_method`, `total`, `created_at`) VALUES
(6, 'SF-20260226-92E2F908', 37, 'premium', 'monthly', 'stripe', 2500.00, '2026-02-26 22:22:27'),
(7, 'SF-20260226-D393BF5F', 38, 'pro', 'monthly', 'stripe', 5000.00, '2026-02-26 23:40:10'),
(8, 'SF-20260227-9FF91773', 39, 'premium', 'monthly', 'stripe', 2500.00, '2026-02-27 11:16:57'),
(9, 'SF-20260301-2A0EE080', 40, 'pro', 'monthly', 'stripe', 5000.00, '2026-03-02 02:55:48'),
(10, 'SF-20260307-CF799772', 41, 'pro', 'monthly', 'stripe', 5000.00, '2026-03-08 00:52:42'),
(11, 'SF-20260308-3B97A5DB', 42, 'premium', 'monthly', 'stripe', 2500.00, '2026-03-08 15:08:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('member','trainer','admin') NOT NULL DEFAULT 'member',
  `plan` enum('basic','premium','pro') NOT NULL DEFAULT 'premium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','blocked') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `role`, `plan`, `created_at`, `status`) VALUES
(3, 'akshi', 'ashok', 'akshi@gmail.com', '+94715698425', '$2y$10$NN37qhH2JeOyleEfI3bof.EcDUUFXxkwwUb1kP4raLhjLrqSZGlp2', 'member', 'basic', '2026-02-18 05:58:51', 'active'),
(4, 'akshay', 'ashok', 'akshay@gmail.com', '+94795478516', '$2y$10$.WZh4N4oR6xV7WXACCIjrelVqRp1SEwnl5J7XBahqimdVxcoK5nlK', 'member', 'basic', '2026-02-18 07:34:13', 'active'),
(5, 'aathi', 'gowtham', 'aathi@gmail.com', '+94765412589', '$2y$10$txcFwxIqnZZhUsISDGTOtODrryycnOl7nHcM4mJwuwA0SSnEe3T7.', 'member', 'pro', '2026-02-18 08:13:39', 'active'),
(6, 'vasu', 'siva', 'vasu@gmail.com', '+94784563215', '$2y$10$fwj7g9o84L1l9aEPMWIJsONTVnidzXNzSmN/E1QsQBEYIkJ6Sl4jG', 'member', 'pro', '2026-02-18 08:59:56', 'active'),
(8, 'Admin', 'User', 'admin@smartfit.lk', '+94000000000', '$2y$10$2ZlVA4oxdsVQ0CS7/JdQOezWHLL5N4uCXAO1XM57AKSuQOZlhnkPW', 'admin', '', '2026-02-18 12:14:11', 'active'),
(9, 'Trainer', 'User', 'trainer@smartfit.lk', '+94000000001', '$2y$10$M5xIKLuRfmYhITqyLONXOOohenGXRIG/8p2FFUZK94R4szkCxjtsy', 'trainer', '', '2026-02-18 12:14:11', 'active'),
(11, 'asha', 'ravi', 'asha@gmail.com', '+94751236548', '$2y$10$dYEsXBrJJdI8DOFTCr5V1OnN4e/NKUhIqZCcnx7AwnQZtSldIW75O', 'member', 'basic', '2026-02-18 18:21:05', 'active'),
(13, 'ravi', 'siva', 'aathi2@gmail.com', '+94785432145', '$2y$10$MFM0kG1JUzPF/.W5RXjrbueGArbfakvEquurZkrQxgrxQhlKEmjBm', 'member', 'pro', '2026-02-22 13:17:34', 'active'),
(14, 'Demo', 'Member', 'member@smartfit.lk', '+94000000000', '$2y$10$u92mYI.WNeQubUX9QcVTpO440sqjPAEzfVQPXm0lfNY0rTETZJqze', 'member', 'pro', '2026-02-22 13:41:02', 'active'),
(30, 'shalu', 'ak', 'sa@gmail.com', '+94712345678', '$2y$10$7lmqh1egVga2xmRfnJGNQuHL8W3u3O2blAsSStdurYJDQVpZUk.4u', 'member', 'premium', '2026-02-26 10:34:50', 'active'),
(37, 'priya', 'raku', 'pr@gmail.com', '+94789654866', '$2y$10$gFi9HhpZVfKSgnJZaVwifOsmf1VTMC0dwQebYbrZkHS52E8Syled2', 'member', 'premium', '2026-02-26 16:52:27', 'active'),
(38, 'abi', 'ashok', 'aabhi@gmail.com', '+94782456325', '$2y$10$.S477WHO506Gz/9O6h3EJuFQxNWbFWggN7T0bLSbqu3AKYF7XHAWC', 'member', 'pro', '2026-02-26 18:10:10', 'active'),
(39, 'nila', 'kiri', 'nila@gmail.com', '+94756321456', '$2y$10$4Fd5QT.ybPOyOgFHB1GsKO.yvH4cFIS/C2v2szx6n2KlJw./lItEK', 'member', 'premium', '2026-02-27 05:46:57', 'active'),
(40, 'jeni', 'jhon', 'jj@gmail.com', '+94756321479', '$2y$10$QGJ9e0G6yjSLrKb8LDW7XuXQ.QRk5DAqbUhWPByucrydYT3CSyahu', 'member', 'pro', '2026-03-01 21:25:48', 'active'),
(41, 'janu', 'john', 'jj1@gmail.com', '+94776542398', '$2y$10$SjIjBscB0/kaf2U59Qs4XulG6F1OvBwSXaMGNvPCFYMfayPQgsrjq', 'member', 'pro', '2026-03-07 19:22:42', 'active'),
(42, 'kani', 'siva', 'aabi@gmail.com', '+94775341236', '$2y$10$Y9m7KtGDg6/bQ7gcUQzQ1.tWEAqzexdENZBKvut.ma/tk9ktNjRa.', 'member', 'premium', '2026-03-08 09:38:05', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_plans`
--

CREATE TABLE `user_plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_code` varchar(30) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `starts_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_plans`
--

INSERT INTO `user_plans` (`id`, `user_id`, `plan_code`, `status`, `starts_at`, `expires_at`, `created_at`) VALUES
(1, 14, 'pro', 'inactive', '2026-02-28 20:44:47', '2026-03-30 20:44:47', '2026-02-28 20:44:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_workouts`
--

CREATE TABLE `user_workouts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `workout_id` int(11) NOT NULL,
  `status` enum('started','completed') NOT NULL DEFAULT 'started',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `source` enum('self','trainer') NOT NULL DEFAULT 'self'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_workouts`
--

INSERT INTO `user_workouts` (`id`, `user_id`, `trainer_id`, `assigned_at`, `started_at`, `workout_id`, `status`, `created_at`, `completed_at`, `source`) VALUES
(16, 13, 9, '2026-02-23 20:21:34', '2026-02-23 15:51:34', 9, 'completed', '2026-02-23 14:51:34', '2026-02-23 20:21:34', 'trainer'),
(17, 11, 9, '2026-02-23 20:30:10', '2026-02-23 16:00:10', 11, 'completed', '2026-02-23 15:00:10', '2026-02-23 20:30:10', 'trainer'),
(18, 13, 9, '2026-02-23 20:40:21', '2026-02-23 16:10:21', 15, 'started', '2026-02-23 15:10:21', NULL, 'trainer'),
(19, 5, 9, '2026-02-23 20:40:48', '2026-02-25 19:35:57', 11, 'completed', '2026-02-23 15:10:48', '2026-02-25 19:36:00', 'trainer'),
(20, 13, 9, '2026-02-23 20:44:39', NULL, 18, '', '2026-02-23 15:14:39', NULL, 'trainer'),
(21, 14, 9, '2026-02-23 22:29:18', '2026-02-24 23:10:24', 21, 'started', '2026-02-23 16:59:18', NULL, 'trainer'),
(23, 14, NULL, NULL, '2026-02-24 22:58:34', 18, 'completed', '2026-02-24 17:28:34', '2026-02-24 22:58:34', 'self'),
(27, 13, NULL, NULL, '2026-02-25 09:12:35', 12, 'completed', '2026-02-25 03:42:35', '2026-02-25 09:12:35', 'self'),
(28, 11, 9, '2026-02-25 10:14:03', '2026-02-25 10:34:57', 21, 'completed', '2026-02-25 04:44:03', '2026-02-25 10:36:15', 'trainer'),
(29, 3, 9, '2026-02-25 10:41:31', '2026-02-28 21:15:54', 21, 'completed', '2026-02-25 05:11:31', '2026-03-01 10:49:53', 'trainer'),
(30, 3, 9, '2026-02-25 10:41:44', '2026-02-25 10:42:21', 8, 'completed', '2026-02-25 05:11:44', '2026-02-28 21:15:33', 'trainer'),
(31, 4, NULL, NULL, '2026-02-25 12:38:04', 21, 'completed', '2026-02-25 07:08:04', '2026-02-25 12:38:04', 'self'),
(32, 4, NULL, NULL, '2026-02-25 12:49:28', 18, 'completed', '2026-02-25 07:19:28', '2026-02-25 12:49:28', 'self'),
(34, 30, 9, '2026-02-26 17:16:16', '2026-02-26 17:17:45', 13, 'started', '2026-02-26 11:46:16', NULL, 'trainer'),
(35, 37, NULL, NULL, '2026-02-26 22:26:27', 21, 'completed', '2026-02-26 16:56:27', '2026-02-26 22:26:27', 'self'),
(36, 37, 9, '2026-02-26 22:43:32', '2026-03-01 14:07:15', 15, 'completed', '2026-02-26 17:13:32', '2026-03-01 22:52:45', 'trainer'),
(37, 37, NULL, NULL, '2026-02-26 22:45:53', 19, 'completed', '2026-02-26 17:15:53', '2026-02-26 22:45:53', 'self'),
(38, 37, NULL, NULL, '2026-02-26 23:05:26', 13, 'completed', '2026-02-26 17:35:26', '2026-03-01 14:07:34', 'self'),
(40, 13, NULL, NULL, '2026-02-27 00:08:54', 17, 'started', '2026-02-26 18:38:54', NULL, 'self'),
(41, 38, NULL, NULL, '2026-02-27 00:28:17', 21, 'completed', '2026-02-26 18:58:17', '2026-02-27 00:28:34', 'self'),
(42, 38, NULL, NULL, '2026-02-27 00:28:40', 19, 'completed', '2026-02-26 18:58:40', '2026-02-28 19:56:27', 'self'),
(43, 38, NULL, NULL, '2026-02-27 00:28:54', 18, 'completed', '2026-02-26 18:58:54', '2026-02-28 20:08:31', 'self'),
(44, 38, NULL, NULL, '2026-02-27 11:12:51', 16, 'completed', '2026-02-27 05:42:51', '2026-02-28 20:08:32', 'self'),
(46, 38, NULL, NULL, '2026-02-28 20:08:11', 14, 'completed', '2026-02-28 14:38:11', '2026-02-28 20:08:13', 'self'),
(47, 38, NULL, NULL, '2026-02-28 20:08:12', 13, 'started', '2026-02-28 14:38:12', '2026-02-28 20:08:33', 'self'),
(48, 38, NULL, NULL, '2026-02-28 20:10:30', 15, 'completed', '2026-02-28 14:40:30', '2026-02-28 20:10:38', 'self'),
(49, 38, NULL, NULL, '2026-02-28 20:10:32', 12, 'completed', '2026-02-28 14:40:32', '2026-02-28 20:10:36', 'self'),
(50, 38, NULL, NULL, '2026-02-28 20:10:33', 9, 'completed', '2026-02-28 14:40:33', '2026-02-28 20:11:03', 'self'),
(51, 38, NULL, NULL, '2026-02-28 20:10:57', 10, 'completed', '2026-02-28 14:40:57', '2026-02-28 20:11:01', 'self'),
(53, 38, NULL, NULL, '2026-02-28 20:10:59', 11, 'completed', '2026-02-28 14:40:59', '2026-02-28 20:11:00', 'self'),
(56, 3, NULL, NULL, '2026-02-28 21:40:33', 16, 'completed', '2026-02-28 16:10:33', '2026-03-01 13:03:57', 'self'),
(57, 3, NULL, NULL, '2026-02-28 22:10:36', 9, 'completed', '2026-02-28 16:40:36', '2026-03-01 10:49:50', 'self'),
(58, 5, NULL, NULL, '2026-02-28 22:11:22', 8, 'completed', '2026-02-28 16:41:22', '2026-03-01 18:14:07', 'self'),
(59, 5, NULL, NULL, '2026-02-28 22:11:25', 9, 'completed', '2026-02-28 16:41:25', '2026-03-01 18:14:01', 'self'),
(60, 5, NULL, NULL, '2026-02-28 22:11:26', 10, 'completed', '2026-02-28 16:41:26', '2026-02-28 22:58:56', 'self'),
(64, 6, 9, '2026-02-28 22:46:25', '2026-03-01 15:07:52', 14, 'completed', '2026-02-28 17:16:25', '2026-03-01 15:43:32', 'trainer'),
(65, 39, 9, '2026-02-28 22:55:21', NULL, 15, '', '2026-02-28 17:25:21', NULL, 'trainer'),
(66, 39, 9, '2026-02-28 22:55:55', '2026-02-28 23:01:14', 12, 'completed', '2026-02-28 17:25:55', '2026-03-01 18:20:06', 'trainer'),
(67, 5, NULL, NULL, '2026-02-28 22:58:35', 21, 'completed', '2026-02-28 17:28:35', '2026-02-28 22:58:46', 'self'),
(68, 5, NULL, NULL, '2026-02-28 22:58:59', 18, 'completed', '2026-02-28 17:28:59', '2026-02-28 22:58:59', 'self'),
(70, 3, NULL, NULL, '2026-03-01 10:50:25', 10, 'completed', '2026-02-28 18:52:15', '2026-03-01 10:50:35', 'self'),
(71, 4, NULL, NULL, '2026-03-01 15:46:00', 9, 'completed', '2026-02-28 18:53:32', '2026-03-01 15:46:17', 'self'),
(74, 5, NULL, NULL, '2026-03-01 18:13:49', 14, 'completed', '2026-02-28 18:54:11', '2026-03-01 18:14:09', 'self'),
(75, 4, NULL, NULL, '2026-03-01 14:09:58', 8, 'completed', '2026-02-28 18:54:22', '2026-03-01 15:46:02', 'self'),
(77, 4, NULL, NULL, '2026-03-01 15:46:24', 10, 'completed', '2026-02-28 18:54:38', '2026-03-01 15:46:27', 'self'),
(78, 39, 9, '2026-03-01 00:27:29', NULL, 9, '', '2026-02-28 18:57:29', NULL, 'trainer'),
(85, 13, 9, '2026-03-01 00:48:43', NULL, 8, '', '2026-02-28 19:18:43', NULL, 'trainer'),
(88, 6, 9, '2026-03-01 00:49:46', '2026-03-01 15:07:43', 8, 'completed', '2026-02-28 19:19:46', '2026-03-01 15:07:46', 'trainer'),
(93, 37, 9, '2026-03-01 13:57:39', '2026-03-01 14:07:08', 10, 'completed', '2026-03-01 08:27:39', '2026-03-01 14:07:12', 'trainer'),
(94, 6, NULL, NULL, '2026-03-01 15:34:29', 21, 'completed', '2026-03-01 10:04:29', '2026-03-01 15:43:03', 'self'),
(96, 4, NULL, NULL, '2026-03-01 15:46:46', 15, 'completed', '2026-03-01 10:16:46', '2026-03-01 15:47:02', 'self'),
(97, 4, NULL, NULL, '2026-03-01 15:47:13', 14, 'started', '2026-03-01 10:17:13', NULL, 'self'),
(98, 30, 9, '2026-03-01 16:08:40', NULL, 21, '', '2026-03-01 10:38:40', NULL, 'trainer'),
(104, 11, 9, '2026-03-01 16:10:26', NULL, 17, '', '2026-03-01 10:40:26', NULL, 'trainer'),
(106, 4, 9, '2026-03-01 17:55:29', NULL, 19, '', '2026-03-01 12:25:29', NULL, 'trainer'),
(107, 39, NULL, NULL, '2026-03-01 18:19:39', 21, 'completed', '2026-03-01 12:49:39', '2026-03-01 21:33:29', 'self'),
(108, 40, NULL, NULL, '2026-03-02 02:59:14', 21, 'started', '2026-03-01 21:29:14', NULL, 'self'),
(109, 40, 9, '2026-03-02 03:01:28', NULL, 9, '', '2026-03-01 21:31:28', NULL, 'trainer'),
(116, 38, 9, '2026-03-04 11:44:44', '2026-03-08 16:02:35', 17, 'completed', '2026-03-04 06:14:44', '2026-03-08 16:05:06', 'trainer'),
(120, 3, 9, '2026-03-04 12:01:48', NULL, 13, '', '2026-03-04 06:31:48', NULL, 'trainer'),
(124, 42, 9, '2026-03-08 16:27:47', NULL, 21, '', '2026-03-08 10:57:47', NULL, 'trainer'),
(125, 41, 9, '2026-03-08 16:28:32', NULL, 10, '', '2026-03-08 10:58:32', NULL, 'trainer');

-- --------------------------------------------------------

--
-- Table structure for table `workouts`
--

CREATE TABLE `workouts` (
  `id` int(11) NOT NULL,
  `title` varchar(120) NOT NULL,
  `level` enum('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  `duration_min` int(11) NOT NULL DEFAULT 20,
  `calories` int(11) NOT NULL DEFAULT 150,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `youtube_url` varchar(255) DEFAULT NULL,
  `plan_type` enum('basic','premium','pro') DEFAULT 'basic'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workouts`
--

INSERT INTO `workouts` (`id`, `title`, `level`, `duration_min`, `calories`, `created_at`, `youtube_url`, `plan_type`) VALUES
(8, 'Full Body Beginner', 'beginner', 30, 250, '2026-02-23 14:24:16', 'https://youtu.be/UoC_O3HzsH0', 'basic'),
(9, 'Cardio Burn', 'beginner', 20, 200, '2026-02-23 14:24:16', 'https://youtu.be/ml6cT4AZdqI', 'basic'),
(10, 'Yoga Stretch Flow', 'beginner', 25, 120, '2026-02-23 14:24:16', 'https://youtu.be/v7AYKMP6rOE', 'basic'),
(11, 'Abs & Core Blast', 'intermediate', 25, 220, '2026-02-23 14:24:16', 'https://youtu.be/1919eTCoESo', 'premium'),
(12, 'HIIT Fat Loss', 'intermediate', 30, 350, '2026-02-23 14:24:16', 'https://youtu.be/cZnsLVArIt8', 'premium'),
(13, 'Upper Body Strength', 'intermediate', 35, 300, '2026-02-23 14:24:16', 'https://youtu.be/qEwKCR5JCog', 'premium'),
(14, 'Leg Day Power', 'intermediate', 40, 360, '2026-02-23 14:24:16', 'https://youtu.be/2SHsk9AzdjA', 'basic'),
(15, 'Endurance Circuit', 'intermediate', 45, 380, '2026-02-23 14:24:16', 'https://youtu.be/IODxDxX7oi4', 'basic'),
(16, 'Advanced HIIT Pro', 'advanced', 35, 420, '2026-02-23 14:24:16', 'https://youtu.be/VHyGqsPOUHs', 'pro'),
(17, 'Power Lifting Basics', 'advanced', 45, 400, '2026-02-23 14:24:16', 'https://youtu.be/1uDiW5--rAE', 'pro'),
(18, 'Advanced Strength Split', 'advanced', 60, 520, '2026-02-23 14:24:16', 'https://youtu.be/XxuRSjERm1o', 'pro'),
(19, 'Athlete Conditioning', 'advanced', 50, 480, '2026-02-23 14:24:16', 'https://youtu.be/ZiGE3-L4vyg', 'pro'),
(21, 'Yoga Stretch', 'intermediate', 10, 120, '2026-02-23 14:30:19', 'https://youtu.be/SvPKFsCiMsw?si=J4Fey4zoY82CF7Q_', 'basic');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_recommendation_logs`
--
ALTER TABLE `ai_recommendation_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `target_user_id` (`target_user_id`);

--
-- Indexes for table `bmi_records`
--
ALTER TABLE `bmi_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `meal_follow_log`
--
ALTER TABLE `meal_follow_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_date` (`user_id`,`log_date`);

--
-- Indexes for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meal_plan_logs`
--
ALTER TABLE `meal_plan_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_plan_day` (`user_id`,`plan_id`,`log_date`),
  ADD KEY `idx_user_day` (`user_id`,`log_date`),
  ADD KEY `idx_plan_day` (`plan_id`,`log_date`);

--
-- Indexes for table `member_profiles`
--
ALTER TABLE `member_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `schedule_date` (`schedule_date`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_plans`
--
ALTER TABLE `user_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_plan` (`user_id`,`plan_code`);

--
-- Indexes for table `user_workouts`
--
ALTER TABLE `user_workouts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_workout` (`user_id`,`workout_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `workout_id` (`workout_id`),
  ADD KEY `idx_user_workouts_trainer` (`trainer_id`),
  ADD KEY `idx_user_workouts_user` (`user_id`),
  ADD KEY `idx_user_workouts_status` (`status`);

--
-- Indexes for table `workouts`
--
ALTER TABLE `workouts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_recommendation_logs`
--
ALTER TABLE `ai_recommendation_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bmi_records`
--
ALTER TABLE `bmi_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `meal_follow_log`
--
ALTER TABLE `meal_follow_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `meal_plans`
--
ALTER TABLE `meal_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `meal_plan_logs`
--
ALTER TABLE `meal_plan_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `member_profiles`
--
ALTER TABLE `member_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `user_plans`
--
ALTER TABLE `user_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_workouts`
--
ALTER TABLE `user_workouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `workouts`
--
ALTER TABLE `workouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `audit_logs_ibfk_2` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bmi_records`
--
ALTER TABLE `bmi_records`
  ADD CONSTRAINT `fk_bmi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_follow_log`
--
ALTER TABLE `meal_follow_log`
  ADD CONSTRAINT `fk_follow_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_plan_logs`
--
ALTER TABLE `meal_plan_logs`
  ADD CONSTRAINT `fk_mpl_plan` FOREIGN KEY (`plan_id`) REFERENCES `meal_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mpl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `member_profiles`
--
ALTER TABLE `member_profiles`
  ADD CONSTRAINT `member_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_workouts`
--
ALTER TABLE `user_workouts`
  ADD CONSTRAINT `fk_user_workouts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_workouts_workout` FOREIGN KEY (`workout_id`) REFERENCES `workouts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
