CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL
) ENGINE='InnoDB' COLLATE 'utf8_general_ci';

CREATE TABLE IF NOT EXISTS `tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL
) ENGINE='InnoDB' COLLATE 'utf8_general_ci';

CREATE TABLE IF NOT EXISTS `tests_users` (
  `test_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`test_id`, `user_id`),
  FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE='InnoDB' COLLATE 'utf8_general_ci';
