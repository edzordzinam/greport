/*
 Navicat Premium Data Transfer

 Source Server         : Prexition
 Source Server Type    : MySQL
 Source Server Version : 50637
 Source Host           : localhost
 Source Database       : backup_enas

 Target Server Type    : MySQL
 Target Server Version : 50637
 File Encoding         : utf-8

 Date: 12/20/2018 15:41:05 PM
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `roles`
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role` int(11) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `roles`
-- ----------------------------
BEGIN;
INSERT INTO `roles` VALUES ('-1', 'unauthorized'), ('0', 'user'), ('100', 'school administrator / principal'), ('200', 'superuser'), ('50', 'instructor'), ('40', 'frontdesk'), ('110', 'accountant'), ('500', 'reports'), ('41', 'data entry');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
