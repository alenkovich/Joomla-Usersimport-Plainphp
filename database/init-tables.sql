-- Testdata to be imported into dabase
-- Based on actual export from Joomla 3.4.x
-- Adminer 4.2.1 MySQL dump

-- Password for user admin equals "sprenkel"

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `j_usergroups`;
CREATE TABLE `j_usergroups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Adjacency List Reference Id',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `title` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_usergroup_parent_title_lookup` (`parent_id`,`title`),
  KEY `idx_usergroup_title_lookup` (`title`),
  KEY `idx_usergroup_adjacency_lookup` (`parent_id`),
  KEY `idx_usergroup_nested_set_lookup` (`lft`,`rgt`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `j_usergroups` (`id`, `parent_id`, `lft`, `rgt`, `title`) VALUES
(1,	0,	1,	18,	'Public'),
(2,	1,	8,	15,	'Registered'),
(3,	2,	9,	14,	'Author'),
(4,	3,	10,	13,	'Editor'),
(5,	4,	11,	12,	'Publisher'),
(6,	1,	4,	7,	'Manager'),
(7,	6,	5,	6,	'Administrator'),
(8,	1,	16,	17,	'Super Users'),
(9,	1,	2,	3,	'Guest');

DROP TABLE IF EXISTS `j_users`;
CREATE TABLE `j_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(150) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(100) NOT NULL DEFAULT '',
  `block` tinyint(4) NOT NULL DEFAULT '0',
  `sendEmail` tinyint(4) DEFAULT '0',
  `registerDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastvisitDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activation` varchar(100) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `lastResetTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date of last password reset',
  `resetCount` int(11) NOT NULL DEFAULT '0' COMMENT 'Count of password resets since lastResetTime',
  `otpKey` varchar(1000) NOT NULL DEFAULT '' COMMENT 'Two factor authentication encrypted keys',
  `otep` varchar(1000) NOT NULL DEFAULT '' COMMENT 'One time emergency passwords',
  `requireReset` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Require user to reset password on next login',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_block` (`block`),
  KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `j_users` (`id`, `name`, `username`, `email`, `password`, `block`, `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`, `lastResetTime`, `resetCount`, `otpKey`, `otep`, `requireReset`) VALUES
(951,	'Super User',	'admin',	'admin@example.com',	'$2y$10$w/PtiELxCi/KDTizSHREj.dz6ejoeNC5M.rbdk/ECr4EubDjhysR6',	0,	1,	'2013-07-24 09:07:43',	'2015-05-12 16:13:30',	'0',	'{\"admin_style\":\"\",\"admin_language\":\"\",\"language\":\"\",\"editor\":\"\",\"helpsite\":\"\",\"timezone\":\"\"}',	'0000-00-00 00:00:00',	0,	'',	'',	0),
(952,	'User',	'user',	'user@example.com',	'931d334de664be1135bed97fd9bb7b62:ZzvicSTnh9dr1Ln36G3MgkC9WSa9J4PW',	0,	0,	'2013-07-24 09:23:03',	'0000-00-00 00:00:00',	'',	'{\"admin_style\":\"\",\"admin_language\":\"\",\"language\":\"\",\"editor\":\"\",\"helpsite\":\"\",\"timezone\":\"\"}',	'0000-00-00 00:00:00',	0,	'',	'',	0),
(953,	'Manager',	'manager',	'manager@example.com',	'e0f025cc620a663e172c8b25911e5c4e:44wqdHQWhDPcrRg5koGsWJ9Zlhr9WC5x',	0,	0,	'2013-07-24 10:53:59',	'0000-00-00 00:00:00',	'',	'{\"admin_style\":\"\",\"admin_language\":\"\",\"language\":\"\",\"editor\":\"\",\"helpsite\":\"\",\"timezone\":\"\"}',	'0000-00-00 00:00:00',	0,	'',	'',	0);

DROP TABLE IF EXISTS `j_user_usergroup_map`;
CREATE TABLE `j_user_usergroup_map` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to j_users.id',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to j_usergroups.id',
  PRIMARY KEY (`user_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `j_user_usergroup_map` (`user_id`, `group_id`) VALUES
(951,	8),
(952,	2),
(953,	6);

-- 2015-05-19 10:09:21
