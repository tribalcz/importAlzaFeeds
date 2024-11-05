SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';


CREATE TABLE IF NOT EXISTS `user` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(32) NOT NULL,
    `password` varchar(255) NOT NULL,
    `role` varchar(16) NOT NULL DEFAULT 'user',
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Heslo pro oba testovací účty je 'kakhewsAks5'
INSERT INTO `user` (`id`, `username`, `password`, `role`) VALUES
(1,	'admin',	'$2y$10$Sr2vyFYvF1FdrpYUuAHKLOILkJno9X9PJLOeEsHvhNU1eZl31auBG',	'admin'),
(2,	'user',	'$2y$10$Sr2vyFYvF1FdrpYUuAHKLOILkJno9X9PJLOeEsHvhNU1eZl31auBG',	'user');