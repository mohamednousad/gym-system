SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `membership_plan` varchar(50) DEFAULT NULL,
  `membership_status` enum('active','expired') DEFAULT 'active',
  `renewal_date` date DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `profile_image`, `membership_plan`, `membership_status`, `renewal_date`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2a$12$.pQJSb6sE19QghdexjYMeeuhqqLJzbExQImXTlVKpWvKZnjaXIbTu', '123456789', NULL, NULL, 'active', NULL, 'admin', '2025-04-09 08:34:49')

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);


ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

  CREATE TABLE `workout_plans` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `trainer_id` int(11) DEFAULT NULL,
    `plan_name` varchar(100) NOT NULL,
    `goal` varchar(100) DEFAULT NULL,
    `duration_weeks` int(11) DEFAULT NULL,
    `days_per_week` int(11) DEFAULT NULL,
    `difficulty` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
    `status` enum('active','completed','paused') DEFAULT 'active',
    `start_date` date DEFAULT NULL,
    `end_date` date DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


  CREATE TABLE `attendance` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `check_in` datetime NOT NULL,
    `check_out` datetime DEFAULT NULL,
    `duration_minutes` int(11) DEFAULT NULL,
    `status` enum('present','absent','late') DEFAULT 'present',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


  CREATE TABLE `trainers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `specialization` varchar(100) DEFAULT NULL,
    `experience_years` int(11) DEFAULT NULL,
    `profile_image` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


  CREATE TABLE `notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `title` varchar(150) NOT NULL,
    `message` text NOT NULL,
    `type` enum('payment','renewal','workout','attendance','general') DEFAULT 'general',
    `is_read` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `trainers` (`name`, `email`, `phone`, `specialization`, `experience_years`, `status`) VALUES
('John Smith',   'john@gym.com',  '0771234567', 'Weight Training',  5, 'active'),
('Sarah Lee',    'sarah@gym.com', '0779876543', 'Cardio & Yoga',    3, 'active'),
('Mike Johnson', 'mike@gym.com',  '0762345678', 'CrossFit',         7, 'active');


INSERT INTO `workout_plans` (`user_id`, `trainer_id`, `plan_name`, `goal`, `duration_weeks`, `days_per_week`, `difficulty`, `status`, `start_date`, `end_date`) VALUES
(15, 1, 'Strength Builder', 'Muscle Gain',    8, 4, 'intermediate', 'active',    '2025-09-01', '2025-10-27'),
(15, 2, 'Fat Burn Cardio',  'Weight Loss',    6, 5, 'beginner',     'completed', '2025-07-01', '2025-08-12');


INSERT INTO `attendance` (`user_id`, `check_in`, `check_out`, `duration_minutes`, `status`) VALUES
(15, '2025-09-10 08:00:00', '2025-09-10 09:30:00', 90,  'present'),
(15, '2025-09-11 08:15:00', '2025-09-11 09:45:00', 90,  'present'),
(15, '2025-09-12 09:00:00', '2025-09-12 10:00:00', 60,  'late'),
(15, '2025-09-13 08:00:00', '2025-09-13 09:30:00', 90,  'present');


INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `is_read`) VALUES
(15, 'Payment Due',       'Your membership renewal is due on 2025-12-13.',          'renewal',  0),
(15, 'Workout Updated',   'Trainer John updated your Strength Builder plan.',        'workout',  0),
(15, 'Payment Received',  'Payment of LKR 5000 received successfully.',              'payment',  1),
(15, 'Welcome!',          'Welcome to SmartGym! Your membership is now active.',     'general',  1);


INSERT INTO `payments` (`user_id`, `amount`, `payment_date`, `method`, `status`, `plan`, `reference`) VALUES
(15, 5000.00, '2025-09-13', 'cash',   'paid',    'basic',    'PAY-001'),
(15, 5000.00, '2025-08-13', 'card',   'paid',    'basic',    'PAY-002'),
(15, 5000.00, '2025-07-13', 'online', 'paid',    'basic',    'PAY-003');


