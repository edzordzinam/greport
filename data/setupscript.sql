/*
Navicat MySQL Data Transfer

Source Server         : CentOS Server
Source Server Version : 50161
Source Host           : 192.168.10.48:3306
Source Database       : greport

Target Server Type    : MYSQL
Target Server Version : 50161
File Encoding         : 65001

Date: 2013-02-24 20:49:28
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Records of menu
-- ----------------------------
INSERT INTO `menu` VALUES ('13', '100', 'Manage Students', 'managestudents', 'icon-book');
INSERT INTO `menu` VALUES ('2', '200', 'Manage Users', 'listuser', 'icon-user');
INSERT INTO `menu` VALUES ('4', '200', 'Manage Resources', 'listresources', 'icon-leaf');
INSERT INTO `menu` VALUES ('5', '200', 'Manage Roles', 'listroles', 'icon-briefcase');
INSERT INTO `menu` VALUES ('6', '100', 'Manage Instructors', 'manageinstructors', 'icon-user');
INSERT INTO `menu` VALUES ('7', '100', 'Manage Subjects', 'managesubjects', 'icon-book');
INSERT INTO `menu` VALUES ('8', '100', 'Manage School', 'manageschool', 'icon-wrench');
INSERT INTO `menu` VALUES ('9', '100', 'Monitor Assignments', 'monitorassessments', 'icon-briefcase');
INSERT INTO `menu` VALUES ('10', '110', 'Students List', 'viewstudents', 'icon-book');
INSERT INTO `menu` VALUES ('11', '110', 'Manage Stores', 'managestore', 'icon-barcode');
INSERT INTO `menu` VALUES ('12', '110', 'Manage Accounts', 'manageaccounts', 'icon-tags');
INSERT INTO `menu` VALUES ('14', '50', 'Manage Assignments', 'manageassignments', 'icon-book');
INSERT INTO `menu` VALUES ('15', '50', 'Manage Comments', 'managecomments', 'icon-pencil');
INSERT INTO `menu` VALUES ('16', '50', 'Students List', 'viewstudents', 'icon-user');
INSERT INTO `menu` VALUES ('17', '50', 'Manage Examinations', 'manageexams', 'icon-list');
INSERT INTO `menu` VALUES ('18', '50', 'Academic Reports', 'viewreports', 'icon-retweet');
INSERT INTO `menu` VALUES ('19', '100', 'Academic Reports', 'viewreports', 'icon-retweet');
INSERT INTO `menu` VALUES ('20', '50', 'Course Syllabus', 'syllabisummary', 'icon-edit');
INSERT INTO `menu` VALUES ('21', '50', 'Performance Trends', 'academicprofile', 'icon-eye-open');
INSERT INTO `menu` VALUES ('23', '40', 'Student Attendance', 'bookattendance', 'icon-edit');
INSERT INTO `menu` VALUES ('24', '50', 'Attendance Reporting', 'attendancereports', 'icon-barcode');
INSERT INTO `menu` VALUES ('26', '100', 'Attendance Reporting', 'attendancereports', 'icon-barcode');
INSERT INTO `menu` VALUES ('27', '40', 'Attendance Reporting', 'attendancereports', 'icon-barcode');
INSERT INTO `menu` VALUES ('28', '100', 'Performance Trends', 'academicprofile', 'icon-eye-open');
INSERT INTO `menu` VALUES ('29', '100', 'Report Printing', 'batchprint', 'icon-book');


INSERT INTO `instructors` VALUES ('1','Administrator', 'School', '100', null, null, '-1', 'administrator', '7215ee9c7d9dc229d2921a40e899ec5f', 'info@greport.com', '0246359711', null, '1');
INSERT INTO `instructors` VALUES ('2','Report', 'Report', '500', '0', null, '-1', 'reportviewer', 'ff2a849b8951c269b6cd827eaf032933', 'info@greport.com', '0246359711', null, '1');


INSERT INTO `options` VALUES ('1', 'schoolname', '');
INSERT INTO `options` VALUES ('2', 'schoollogo', '');
INSERT INTO `options` VALUES ('3', 'schooltel', '');
INSERT INTO `options` VALUES ('4', 'schoolemail', '');
INSERT INTO `options` VALUES ('5', 'examallocate', '60');
INSERT INTO `options` VALUES ('6', 'classallocate', '40');
INSERT INTO `options` VALUES ('7', 'licensestart', '2012-09-12');
INSERT INTO `options` VALUES ('8', 'licenseend', '2012-12-30');
INSERT INTO `options` VALUES ('9', 'licensekey', 'ASDFS2514122ER');
INSERT INTO `options` VALUES ('10', 'cambridge', '0');
