-- =============================================================================
-- Diagram Name: pack32
-- Created on: 8/28/2013 9:41:10 PM
-- Diagram Version:
-- =============================================================================
DROP DATABASE IF EXISTS `pack32`;

CREATE DATABASE IF NOT EXISTS `pack32`
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

USE `pack32`;

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `content` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `user_id` int(11) UNSIGNED NOT NULL,
  `posted_on` int(11) UNSIGNED NOT NULL,
  `content_type` varchar(50),
  `due_on` int(11) UNSIGNED DEFAULT null,
  `event_on` int(11) UNSIGNED DEFAULT null,
  `has_rsvp` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY(`id`),
  UNIQUE INDEX `idx_type_slug`(`content_type`, `slug`),
  INDEX `idx_dated`(`event_on`, `posted_on`, `due_on`)
)
  ENGINE=MYISAM
  CHARACTER SET utf8
  COLLATE utf8_general_ci ;

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(100),
  `email` varchar(100),
  `auth_token` varchar(50),
  `last_on` int(11) UNSIGNED DEFAULT null,
  `primary_group_id` int(11) UNSIGNED DEFAULT null,
  `admin_level` int(11) UNSIGNED DEFAULT '0',
  PRIMARY KEY(`id`),
  INDEX `idx_mail`(`username`, `email`, `auth_token`)
)
  ENGINE=MYISAM
  CHARACTER SET utf8
  COLLATE utf8_general_ci ;

CREATE TABLE `groups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  `is_global` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY(`id`),
  UNIQUE INDEX `idx_groupname`(`name`)
)
  ENGINE=MYISAM
  CHARACTER SET utf8
  COLLATE utf8_general_ci ;

CREATE TABLE `checklist` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` int(11) UNSIGNED NOT NULL,
  `item` varchar(255),
  `user_id` int(11) UNSIGNED DEFAULT null,
  `is_custom` int(11) UNSIGNED DEFAULT '0',
  PRIMARY KEY(`id`),
  INDEX `idx_checklist`(`event_id`, `user_id`)
)
  ENGINE=MYISAM
  CHARACTER SET utf8
  COLLATE utf8_general_ci ;

CREATE TABLE `responses` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `content_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `content` text,
  PRIMARY KEY(`id`),
  INDEX `idx_response`(`content_id`, `user_id`)
)
  ENGINE=MYISAM
  CHARACTER SET utf8
  COLLATE utf8_general_ci ;

CREATE TABLE `rsvps` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` int(11) UNSIGNED NOT NULL,
  `usergroup_id` int(11) UNSIGNED NOT NULL,
  `paid` decimal(10,2) NOT NULL DEFAULT '0',
  `is_rsvp` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY(`id`),
  INDEX `idx_rsvp`(`event_id`, `usergroup_id`)
)
  ENGINE=MYISAM
  CHARACTER SET utf8
  COLLATE utf8_general_ci ;

CREATE TABLE `eventgroup` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` int(11) UNSIGNED NOT NULL,
  `group_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE INDEX `idx_eventgroup`(`event_id`, `group_id`)
)
  ENGINE=MYISAM
  CHARACTER SET utf8
  COLLATE utf8_general_ci ;

CREATE TABLE `usergroup` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `group_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE INDEX `idx_usergroup`(`user_id`, `group_id`, `name`)
)
  ENGINE=MYISAM
  CHARACTER SET utf8
  COLLATE utf8_general_ci ;

SET FOREIGN_KEY_CHECKS=1;
