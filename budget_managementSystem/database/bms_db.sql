-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2024 at 06:49 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `authentication`
--

CREATE TABLE `authentication` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_activity`
--

CREATE TABLE `tbl_activity` (
  `user_id` varchar(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `date_activity` datetime NOT NULL,
  `id` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_budget`
--

CREATE TABLE `tbl_budget` (
  `id` int(100) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  `office` varchar(255) NOT NULL,
  `acc_name` varchar(255) NOT NULL,
  `acc_code` varchar(255) NOT NULL,
  `budget` varchar(255) NOT NULL,
  `supplemental` varchar(255) NOT NULL,
  `realignment` varchar(255) NOT NULL,
  `reprogram` varchar(255) NOT NULL,
  `expense` varchar(255) NOT NULL,
  `balance` varchar(255) NOT NULL,
  `aro` varchar(255) NOT NULL,
  `release` varchar(255) NOT NULL,
  `status` varchar(250) NOT NULL,
  `reason` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_departments`
--

CREATE TABLE `tbl_departments` (
  `id` int(11) NOT NULL,
  `identifier` varchar(250) NOT NULL,
  `department_name` varchar(250) NOT NULL,
  `budget` int(250) NOT NULL,
  `balance` int(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_departments`
--

INSERT INTO `tbl_departments` (`id`, `identifier`, `department_name`, `budget`, `balance`) VALUES
(1, 'MO', 'Mayor Office', 0, 0),
(2, 'SBO', 'Sangguniang Bayan Office', 0, 0),
(3, 'MPDC', 'MPDC', 0, 0),
(4, 'MBO', 'Municipal Budget Office', 0, 0),
(5, 'MACO', 'Municipal Accounting Office', 0, 0),
(6, 'MSWDO', 'MSWDO', 0, 0),
(7, 'MEO', 'Municipal Engineering Office', 0, 0),
(8, 'MASO', 'Municipal Assessor Office', 0, 0),
(9, 'MCRO', 'Municipal Civil Registry Office', 0, 0),
(10, 'MAGO', 'Municipal Agriculture Office', 0, 0),
(11, 'MHO', 'Municipal Health Office', 0, 0),
(12, 'LWS', 'Local Water System', 0, 0),
(13, 'SH', 'Slaughterhouse', 0, 0),
(14, 'PMO', 'Public Market Office', 0, 0),
(15, 'MVO', 'Municipal Veterinary Office', 0, 0),
(16, 'SPA', 'Special Purpose Appropriation', 0, 0),
(17, 'DF', '20% Development Funds', 0, 0),
(18, 'DRRMF', '5% DRRM Funds', 0, 0),
(19, 'CP', '1% Child Protection', 0, 0),
(20, 'GAD', '5% GAD', 0, 0),
(21, 'SCF', 'Senior Citizen Funds', 0, 0),
(22, 'PWDF', 'PWD Funds', 0, 0),
(23, 'PNP', 'PNP', 0, 0),
(24, 'BFP', 'BFP', 0, 0),
(25, 'AFP', 'AFP', 0, 0),
(26, 'SEF', 'Special Education Fund', 0, 0),
(27, 'MTO', 'Municipal Treasury Office', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_list`
--

CREATE TABLE `tbl_list` (
  `id` int(11) NOT NULL,
  `acc_code` varchar(250) NOT NULL,
  `acc_title` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_list`
--

INSERT INTO `tbl_list` (`id`, `acc_code`, `acc_title`) VALUES
(1, '501 01 010', 'Salaries and Wages - Regular Plantilla'),
(2, '501 02 010', 'Personnel Economic Relief Allowance (PERA)'),
(3, '501 02 020', 'Representation Allowance (RA)'),
(4, '501 02 030', 'Transportation Allowance (TA)'),
(5, '501 02 040', 'Clothing and Uniform Allowance'),
(6, '000 00 000', 'Subsistence Allowance'),
(7, '000 00 000', 'Laundry Allowance'),
(8, '501 02 080', 'Productivity/Incentive Benefits & PEI'),
(9, '501 02 110', 'Extra Hazard Premium'),
(10, '501 02 150', 'Cash Gift'),
(11, '501 02 140', 'Mid Year Bonus & Year End Bonus'),
(12, '501 03 010', 'Life and Retirement Insurance Contribution (GSIS)'),
(13, '501 03 020', 'PAG-IBIG Contributions'),
(14, '501 03 030', 'PHILHEALTH Contributions'),
(15, '501 03 040', 'ECC Contributions'),
(16, '501 04 990', 'Other Personnel Benefits'),
(17, '000 00 000', 'Other Personnel Benefits-Loyalty Award'),
(18, '000 00 000', 'Overtime Pay & Overnight Pay'),
(19, '000 00 000', 'Service Recognition Incentive'),
(20, '502 01 010', 'Traveling Expenses - Local'),
(21, '502 02 010', 'Training Expenses'),
(22, '502 03 010', 'Office Supplies Expenses'),
(23, '501 03 090', 'Fuel, Oil and Lubricants Expenses'),
(24, '501 03 990', 'Other Supplies Expenses'),
(25, '000 00 000', 'Medical Supplies and Laboratory Supplies'),
(26, '000 00 000', 'Electricity Expenses'),
(27, '502 05 010', 'Postage & Deliveries'),
(28, '502 05 020', 'Telephone Expenses-Mobile'),
(29, '502 05 030', 'Internet Subscription Expenses'),
(30, '502 99 010', 'Advertising Expenses'),
(31, '502 99 030', 'Representation Expenses'),
(32, '000 00 000', 'Auditing Services'),
(33, '502 11 010', 'Legal Services'),
(34, '502 11 030', 'Consultancy Services'),
(35, '502 12 020', 'Janitorial Services'),
(36, '502 12 030', 'Security Services'),
(37, '502 12 990', 'Other General Services'),
(38, '502 13 040', 'Repairs & Maintenance-Buildings & Other Structure'),
(39, '502 13 050', 'Repairs & Maintenance-Machinery & Equipment'),
(40, '502 13 060', 'Repair & Maintenance-Motor Vehicle'),
(41, '502 13 070', 'Repairs & Maintenance-Furniture & Fixtures'),
(42, '000 00 000', 'Repair and Maintenance-Office Equipment'),
(43, '000 00 000', 'Repair and Maintenance-Infrastructure Assets'),
(44, '000 00 000', 'Repair and Maintenance - Transportation Equipment'),
(45, '000 00 000', 'Pumping Station & Conduits'),
(46, '502 99 080', 'Donations'),
(47, '502 16 010', 'Taxes, Duties and Licenses'),
(48, '000 00 000', 'Insurance Premium'),
(49, '502 99 990', 'Other Maintenance & Operating Expenses'),
(50, '000 00 000', 'Local Agriculture Banner Program'),
(51, '000 00 000', 'Market Code Revision'),
(52, '000 00 000', 'Records Reconstruction'),
(53, '000 00 000', 'Tax Collection Campaign'),
(54, '000 00 000', 'General Revision'),
(55, '000 00 000', 'Tax Mapping'),
(56, '000 00 000', 'Rabies vaccine'),
(57, '502 99 020', 'Printing & Binding Expenses'),
(58, '502 99 060', 'Membership dues and contribution to organization'),
(59, '000 00 000', '2% Discretionary Fund'),
(60, '502 10 020', 'Intelligence Funds'),
(61, '000 00 000', 'Cultural & Athletics Expenses'),
(62, '000 00 000', 'Motorpool tools and equipment'),
(63, '107 06 010', 'Transportation Equipment - Motor Vehicle'),
(64, '107 07 010', 'Furniture and Fixtures'),
(65, '107 05 030', 'ICT Equipment'),
(66, '000 00 000', 'Capital Outlay - Service Vehicle'),
(67, '000 00 000', 'Office Equipment'),
(68, '000 00 000', 'Furniture & Fixtures, and Equipment Outlay'),
(69, '000 00 000', 'Slaughterhouse Improvement and Capital Outlay'),
(70, '000 00 000', 'Building(Dog Pound)'),
(71, '000 00 000', 'Tax Software & Hardware'),
(72, '000 00 000', 'Submersible Motor & Pump');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_office`
--

CREATE TABLE `tbl_office` (
  `identifier` varchar(250) NOT NULL,
  `date_start` datetime NOT NULL,
  `obr_no` int(100) NOT NULL,
  `payee` varchar(250) NOT NULL,
  `office` varchar(250) NOT NULL,
  `acc_name` varchar(250) NOT NULL,
  `acc_code` varchar(250) NOT NULL,
  `details` varchar(250) NOT NULL,
  `amount` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_transfer`
--

CREATE TABLE `tbl_transfer` (
  `id` int(11) NOT NULL,
  `office_from` varchar(255) NOT NULL,
  `office_to` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `date_transfer` datetime NOT NULL,
  `type_of_transfer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `email` varchar(250) NOT NULL,
  `status` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `fullname`, `user_id`, `email`, `status`) VALUES
(1, 'username', '$2y$10$QwbsWOO1tRc.tz6UBy5gueM7pf5o81EvYWD2sNDxp7FrNSuwXmWfy', 'administrator', 'Juan Dela Cruz', 'BMS-L01', 'bernzbauat8@gmail.com', 'active'),
(2, 'roj', '$2y$10$c/STQi8R.JQHzEiULxjjdegG3TwOJF7ZNVJWV/ZPEZZZqAZ4kjTwO', 'Administrator', 'Roj Ortiz', 'BMS-L02', 'bernzbauat8@gmail.com', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authentication`
--
ALTER TABLE `authentication`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_activity`
--
ALTER TABLE `tbl_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_budget`
--
ALTER TABLE `tbl_budget`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_departments`
--
ALTER TABLE `tbl_departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_list`
--
ALTER TABLE `tbl_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_transfer`
--
ALTER TABLE `tbl_transfer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authentication`
--
ALTER TABLE `authentication`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `tbl_activity`
--
ALTER TABLE `tbl_activity`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `tbl_budget`
--
ALTER TABLE `tbl_budget`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_departments`
--
ALTER TABLE `tbl_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `tbl_list`
--
ALTER TABLE `tbl_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `tbl_transfer`
--
ALTER TABLE `tbl_transfer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
