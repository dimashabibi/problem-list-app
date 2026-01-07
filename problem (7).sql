-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 01, 2026 at 03:05 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

--
-- Database: `problem`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
    `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `expiration` int NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO
    `cache` (`key`, `value`, `expiration`)
VALUES (
        'laravel-cache-5c785c036466adea360111aa28563bfd556b5fba',
        'i:1;',
        1767277061
    ),
    (
        'laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer',
        'i:1767277061;',
        1767277061
    );

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
    `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `expiration` int NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
    `id_item` int NOT NULL,
    `item_name` varchar(20) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO
    `items` (
        `id_item`,
        `item_name`,
        `created_at`,
        `updated_at`,
        `deleted_at`
    )
VALUES (
        1,
        '21',
        '2025-12-29 00:29:51',
        '2025-12-29 00:29:51',
        NULL
    ),
    (
        2,
        '71',
        '2025-12-29 00:53:12',
        '2025-12-29 00:53:12',
        NULL
    );

-- --------------------------------------------------------

--
-- Table structure for table `kanbans`
--

CREATE TABLE `kanbans` (
    `id_kanban` int NOT NULL,
    `project_id` int DEFAULT NULL,
    `kanban_name` varchar(20) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `kanbans`
--

INSERT INTO
    `kanbans` (
        `id_kanban`,
        `project_id`,
        `kanban_name`,
        `created_at`,
        `updated_at`,
        `deleted_at`
    )
VALUES (
        7,
        2,
        'KMD01',
        '2025-12-15 20:19:23',
        '2025-12-15 20:19:23',
        '2025-12-16 03:19:23'
    ),
    (
        8,
        2,
        'KMD02',
        '2025-12-15 20:19:23',
        '2025-12-15 20:19:23',
        '2025-12-16 03:19:23'
    ),
    (
        9,
        2,
        'KMD03',
        '2025-12-15 20:19:23',
        '2025-12-15 20:19:23',
        '2025-12-16 03:19:23'
    ),
    (
        10,
        29,
        'MTD01',
        '2025-12-19 17:04:25',
        '2025-12-19 17:04:25',
        '2025-12-20 00:04:25'
    ),
    (
        11,
        29,
        'MTD02',
        '2025-12-19 17:04:25',
        '2025-12-19 17:04:25',
        '2025-12-20 00:04:25'
    ),
    (
        12,
        29,
        'MTD03',
        '2025-12-19 17:04:25',
        '2025-12-19 17:04:25',
        '2025-12-20 00:04:25'
    ),
    (
        13,
        31,
        'MTD01',
        '2025-12-19 20:13:30',
        '2025-12-19 20:13:30',
        '2025-12-20 03:13:30'
    ),
    (
        14,
        31,
        'MTD02',
        '2025-12-19 20:14:22',
        '2025-12-19 20:14:22',
        '2025-12-20 03:14:22'
    ),
    (
        15,
        27,
        'SBD01',
        '2025-12-25 17:39:35',
        '2025-12-25 17:39:35',
        '2025-12-26 00:39:35'
    ),
    (
        16,
        41,
        'DSS01',
        '2025-12-29 00:53:12',
        '2025-12-29 00:53:12',
        '2025-12-29 07:53:12'
    );

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
    `id_location` int NOT NULL,
    `location_name` varchar(20) DEFAULT NULL,
    `description` text,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO
    `locations` (
        `id_location`,
        `location_name`,
        `description`,
        `created_at`,
        `updated_at`,
        `deleted_at`
    )
VALUES (
        1,
        'Machining',
        '-',
        '2025-12-15 20:29:52',
        '2025-12-15 20:29:52',
        NULL
    ),
    (
        2,
        'Polymodel',
        NULL,
        '2025-12-15 20:33:36',
        '2025-12-15 20:33:36',
        NULL
    ),
    (
        4,
        'Design',
        NULL,
        '2025-12-15 20:33:36',
        '2025-12-15 20:33:36',
        NULL
    ),
    (
        5,
        'Die Face',
        NULL,
        '2025-12-15 20:33:36',
        '2025-12-15 20:33:36',
        NULL
    ),
    (
        6,
        'Assembly',
        NULL,
        '2025-12-15 20:34:10',
        '2025-12-15 20:34:10',
        NULL
    );

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
    `id` int UNSIGNED NOT NULL,
    `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `batch` int NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO
    `migrations` (`id`, `migration`, `batch`)
VALUES (
        1,
        '2025_12_16_000100_fix_kanbans_project_fk',
        1
    ),
    (
        2,
        '2025_12_16_000210_add_username_to_users',
        2
    ),
    (
        3,
        '0001_01_01_000001_create_cache_table',
        3
    ),
    (
        4,
        '0001_01_01_000000_create_users_table',
        4
    ),
    (
        5,
        '0001_01_01_000002_create_jobs_table',
        4
    ),
    (
        6,
        '2025_12_17_155440_add_type_to_problems_table',
        5
    ),
    (
        7,
        '2025_12_20_105714_add_status_to_problems_table',
        6
    ),
    (
        8,
        '2025_12_23_000000_modify_type_enum_in_problems_table',
        7
    ),
    (
        9,
        '2025_12_29_000000_create_problem_attachments_table',
        8
    );

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
    `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `problems`
--

CREATE TABLE `problems` (
    `id_problem` int NOT NULL,
    `id_project` int DEFAULT NULL,
    `id_kanban` int DEFAULT NULL,
    `id_item` int NOT NULL,
    `id_location` int DEFAULT NULL,
    `problem` varchar(50) DEFAULT NULL,
    `cause` text,
    `curative` text,
    `attachment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    `status` enum(
        'dispatched',
        'in_progress',
        'closed'
    ) NOT NULL DEFAULT 'dispatched',
    `id_user` int DEFAULT NULL,
    `type` enum(
        'manufacturing',
        'ks',
        'kd',
        'sk',
        'kentokai',
        'buyoff'
    ) NOT NULL DEFAULT 'manufacturing',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `problems`
--

INSERT INTO
    `problems` (
        `id_problem`,
        `id_project`,
        `id_kanban`,
        `id_item`,
        `id_location`,
        `problem`,
        `cause`,
        `curative`,
        `attachment`,
        `status`,
        `id_user`,
        `type`,
        `created_at`,
        `updated_at`
    )
VALUES (
        26,
        27,
        15,
        2,
        6,
        's',
        's',
        's',
        'attachments/Nflxkp7Phw3qe6uQpi9z16jcyhzlAIV00Io19zw1.png',
        'dispatched',
        1,
        'manufacturing',
        '2025-12-29 01:53:23',
        '2025-12-29 01:53:23'
    ),
    (
        27,
        41,
        16,
        1,
        6,
        'asd',
        'asd',
        'asd',
        'attachments/pm3F7gT9Aj6GX6t04L829uSzHitehikOnM6G1lhy.png',
        'dispatched',
        1,
        'kd',
        '2026-01-01 07:50:30',
        '2026-01-01 07:50:30'
    );

-- --------------------------------------------------------

--
-- Table structure for table `problem_attachments`
--

CREATE TABLE `problem_attachments` (
    `id` bigint UNSIGNED NOT NULL,
    `problem_id` int NOT NULL,
    `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `problem_attachments`
--

INSERT INTO
    `problem_attachments` (
        `id`,
        `problem_id`,
        `file_path`,
        `created_at`,
        `updated_at`
    )
VALUES (
        1,
        26,
        'attachments/Nflxkp7Phw3qe6uQpi9z16jcyhzlAIV00Io19zw1.png',
        '2025-12-29 01:53:23',
        '2025-12-29 01:53:23'
    ),
    (
        2,
        26,
        'attachments/7LJVDPv1RPIr9miQRoR65LzkKzUImdfbmN23u2GX.png',
        '2025-12-29 01:53:23',
        '2025-12-29 01:53:23'
    ),
    (
        3,
        27,
        'attachments/pm3F7gT9Aj6GX6t04L829uSzHitehikOnM6G1lhy.png',
        '2026-01-01 07:50:30',
        '2026-01-01 07:50:30'
    );

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
    `id_project` int NOT NULL,
    `project_name` varchar(20) NOT NULL,
    `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO
    `projects` (
        `id_project`,
        `project_name`,
        `description`,
        `created_at`,
        `updated_at`,
        `deleted_at`
    )
VALUES (
        2,
        '840D-TKM',
        '-',
        '2025-12-15 15:52:34',
        '2025-12-15 15:52:34',
        '2025-12-15 22:52:34'
    ),
    (
        27,
        '02DD-pmsb',
        '-',
        '2025-12-19 16:37:05',
        '2025-12-19 16:37:05',
        '2025-12-19 23:37:05'
    ),
    (
        29,
        '781D-TMT',
        '-',
        '2025-12-19 16:37:13',
        '2025-12-19 16:37:13',
        '2025-12-19 23:37:13'
    ),
    (
        31,
        '737D',
        'Created via Problem',
        '2025-12-19 20:13:30',
        '2025-12-19 20:13:30',
        '2025-12-20 03:13:30'
    ),
    (
        41,
        '230S-PDS',
        'Created via Problem',
        '2025-12-29 00:53:12',
        '2025-12-29 00:53:12',
        '2025-12-29 07:53:12'
    );

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
    `id_user` int NOT NULL,
    `username` varchar(20) DEFAULT NULL,
    `fullname` varchar(100) DEFAULT NULL,
    `email` varchar(100) DEFAULT NULL,
    `password` varchar(255) DEFAULT NULL,
    `status` enum('admin', 'user') DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO
    `users` (
        `id_user`,
        `username`,
        `fullname`,
        `email`,
        `password`,
        `status`,
        `created_at`,
        `updated_at`,
        `deleted_at`
    )
VALUES (
        1,
        'dimas',
        'Dimas Habibi H',
        'dimsshidayat28@gmail.com',
        '$2y$12$7.F.e2/cib9pUMqMxNCt4.xRdqX1js1Dah5JTFI5V7Zia1JWGnVVG',
        'admin',
        '2025-12-15 20:53:19',
        '2025-12-15 20:53:19',
        '2025-12-16 03:53:19'
    ),
    (
        2,
        'user',
        'magang',
        'petd.magang99@gmail.com',
        '$2y$12$yYZCalnMHjtny6bdVqHK0eLbZDc/J6hLSYosZS5WBqWQO1LuWsqJq',
        'user',
        '2025-12-15 20:57:45',
        '2025-12-15 20:57:45',
        '2025-12-16 03:57:45'
    );

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache` ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks` ADD PRIMARY KEY (`key`);

--
-- Indexes for table `items`
--
ALTER TABLE `items` ADD PRIMARY KEY (`id_item`);

--
-- Indexes for table `kanbans`
--
ALTER TABLE `kanbans`
ADD PRIMARY KEY (`id_kanban`),
ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations` ADD PRIMARY KEY (`id_location`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations` ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens` ADD PRIMARY KEY (`email`);

--
-- Indexes for table `problems`
--
ALTER TABLE `problems`
ADD PRIMARY KEY (`id_problem`),
ADD KEY `id_project` (`id_project`),
ADD KEY `id_kanban` (`id_kanban`),
ADD KEY `id_location` (`id_location`),
ADD KEY `id_user` (`id_user`),
ADD KEY `fk_item_id_pr` (`id_item`);

--
-- Indexes for table `problem_attachments`
--
ALTER TABLE `problem_attachments`
ADD PRIMARY KEY (`id`),
ADD KEY `problem_attachments_problem_id_foreign` (`problem_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects` ADD PRIMARY KEY (`id_project`);

--
-- Indexes for table `users`
--
ALTER TABLE `users` ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
MODIFY `id_item` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 3;

--
-- AUTO_INCREMENT for table `kanbans`
--
ALTER TABLE `kanbans`
MODIFY `id_kanban` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 17;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
MODIFY `id_location` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 7;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 10;

--
-- AUTO_INCREMENT for table `problems`
--
ALTER TABLE `problems`
MODIFY `id_problem` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 28;

--
-- AUTO_INCREMENT for table `problem_attachments`
--
ALTER TABLE `problem_attachments`
MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
MODIFY `id_project` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `id_user` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kanbans`
--
ALTER TABLE `kanbans`
ADD CONSTRAINT `kanbans_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id_project`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `problems`
--
ALTER TABLE `problems`
ADD CONSTRAINT `fk_item_id_pr` FOREIGN KEY (`id_item`) REFERENCES `items` (`id_item`) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT `fk_kanban_id_pr` FOREIGN KEY (`id_kanban`) REFERENCES `kanbans` (`id_kanban`) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT `fk_location_id_pr` FOREIGN KEY (`id_location`) REFERENCES `locations` (`id_location`) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT `fk_project_id_pr` FOREIGN KEY (`id_project`) REFERENCES `projects` (`id_project`) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT `fk_user_id_pr` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `problem_attachments`
--
ALTER TABLE `problem_attachments`
ADD CONSTRAINT `problem_attachments_problem_id_foreign` FOREIGN KEY (`problem_id`) REFERENCES `problems` (`id_problem`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;