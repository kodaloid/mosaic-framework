SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`username` varchar(48) NOT NULL UNIQUE,
	`email` varchar(64) NOT NULL,
	`pass_hash` text NOT NULL,
	`otp_secret` text NOT NULL,
	`user_roles` text NOT NULL,
	`date_created` datetime NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;