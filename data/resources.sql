/*
Navicat MySQL Data Transfer

Source Server         : CentOS Server
Source Server Version : 50161
Source Host           : 192.168.10.48:3306
Source Database       : greport

Target Server Type    : MYSQL
Target Server Version : 50161
File Encoding         : 65001

Date: 2013-03-08 13:51:23
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `resources`
-- ----------------------------
DROP TABLE IF EXISTS `resources`;
CREATE TABLE `resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `role` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=174 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of resources
-- ----------------------------
INSERT INTO `resources` VALUES ('1', 'index', 'index', '0');
INSERT INTO `resources` VALUES ('4', 'index', 'login', '-1');
INSERT INTO `resources` VALUES ('5', 'index', 'logout', '-1');
INSERT INTO `resources` VALUES ('6', 'index', 'authenticate', '-1');
INSERT INTO `resources` VALUES ('7', 'error', 'index', '-1');
INSERT INTO `resources` VALUES ('8', 'menu', 'index', '0');
INSERT INTO `resources` VALUES ('9', 'menu', 'pdflink-vuri', '0');
INSERT INTO `resources` VALUES ('10', 'menu', 'pdflink-huri', '0');
INSERT INTO `resources` VALUES ('11', 'index', 'makepdf', '0');
INSERT INTO `resources` VALUES ('12', 'index', 'list', '200');
INSERT INTO `resources` VALUES ('13', 'index', 'create', '200');
INSERT INTO `resources` VALUES ('14', 'index', 'update', '200');
INSERT INTO `resources` VALUES ('15', 'index', 'delete', '200');
INSERT INTO `resources` VALUES ('16', 'search', 'index', '-1');
INSERT INTO `resources` VALUES ('17', 'instructor', 'index', '100');
INSERT INTO `resources` VALUES ('18', 'instructor', 'create', '100');
INSERT INTO `resources` VALUES ('19', 'instructor', 'list', '100');
INSERT INTO `resources` VALUES ('20', 'instructor', 'edit', '100');
INSERT INTO `resources` VALUES ('21', 'instructor', 'assign-add', '50');
INSERT INTO `resources` VALUES ('22', 'instructor', 'assign-form', '50');
INSERT INTO `resources` VALUES ('23', 'instructor', 'delete-subject', '100');
INSERT INTO `resources` VALUES ('24', 'instructor', 'deactivate', '100');
INSERT INTO `resources` VALUES ('25', 'instructor', 'activate', '100');
INSERT INTO `resources` VALUES ('26', 'instructor', 'update-pass', '40');
INSERT INTO `resources` VALUES ('27', 'instructor', 'inst-subject2', '50');
INSERT INTO `resources` VALUES ('28', 'instructor', 'inst-subject', '50');
INSERT INTO `resources` VALUES ('29', 'instructor', 'inst-subject-classes', '50');
INSERT INTO `resources` VALUES ('30', 'instructor', 'assignment-stats', '50');
INSERT INTO `resources` VALUES ('31', 'instructor', 'enter-comments', '50');
INSERT INTO `resources` VALUES ('32', 'instructor', 'ct-comments', '50');
INSERT INTO `resources` VALUES ('33', 'instructor', 'allowed-grades', '50');
INSERT INTO `resources` VALUES ('34', 'index', 'validateform', '-1');
INSERT INTO `resources` VALUES ('35', 'instructor', 'delete-subject-class', '50');
INSERT INTO `resources` VALUES ('36', 'instructor', 'available-classes', '50');
INSERT INTO `resources` VALUES ('37', 'instructor', 'reset-password', '100');
INSERT INTO `resources` VALUES ('38', 'instructor', 'allgradelevels', '50');
INSERT INTO `resources` VALUES ('39', 'instructor', 'update-details', '100');
INSERT INTO `resources` VALUES ('40', 'instructor', 'allsubjects-assigned', '100');
INSERT INTO `resources` VALUES ('41', 'instructor', 'dtballassignsource', '100');
INSERT INTO `resources` VALUES ('42', 'course', 'index', '50');
INSERT INTO `resources` VALUES ('43', 'course', 'create', '100');
INSERT INTO `resources` VALUES ('44', 'course', 'list', '50');
INSERT INTO `resources` VALUES ('45', 'course', 'delete', '100');
INSERT INTO `resources` VALUES ('46', 'course', 'update', '100');
INSERT INTO `resources` VALUES ('47', 'course', 'listpanel', '50');
INSERT INTO `resources` VALUES ('48', 'course', 'content', '50');
INSERT INTO `resources` VALUES ('49', 'course', 'content-load', '50');
INSERT INTO `resources` VALUES ('50', 'course', 'content-post', '50');
INSERT INTO `resources` VALUES ('51', 'course', 'delete-class', '100');
INSERT INTO `resources` VALUES ('52', 'school', 'index', '100');
INSERT INTO `resources` VALUES ('53', 'school', 'setup-term', '100');
INSERT INTO `resources` VALUES ('54', 'school', 'list-term-dates', '100');
INSERT INTO `resources` VALUES ('55', 'school', 'school-config', '100');
INSERT INTO `resources` VALUES ('57', 'student', 'listcurrentstudents', '40');
INSERT INTO `resources` VALUES ('56', 'student', 'index', '40');
INSERT INTO `resources` VALUES ('58', 'student', 'current-students', '40');
INSERT INTO `resources` VALUES ('59', 'student', 'listpaststudents', '40');
INSERT INTO `resources` VALUES ('60', 'student', 'past-students', '40');
INSERT INTO `resources` VALUES ('61', 'student', 'toggle-status', '40');
INSERT INTO `resources` VALUES ('62', 'student', 'record-update', '40');
INSERT INTO `resources` VALUES ('63', 'student', 'old-students', '40');
INSERT INTO `resources` VALUES ('64', 'student', 'listoldstudents', '40');
INSERT INTO `resources` VALUES ('65', 'store', 'index', '110');
INSERT INTO `resources` VALUES ('66', 'store', 'list-store-items', '110');
INSERT INTO `resources` VALUES ('67', 'store', 'list-store-tranx', '110');
INSERT INTO `resources` VALUES ('69', 'store', 'list-store-source', '110');
INSERT INTO `resources` VALUES ('70', 'store', 'update-store', '110');
INSERT INTO `resources` VALUES ('71', 'accounts', 'index', '110');
INSERT INTO `resources` VALUES ('72', 'accounts', 'bill-groups-list', '110');
INSERT INTO `resources` VALUES ('73', 'accounts', 'group-update', '110');
INSERT INTO `resources` VALUES ('74', 'accounts', 'group-list-source', '110');
INSERT INTO `resources` VALUES ('75', 'accounts', 'term-bill', '110');
INSERT INTO `resources` VALUES ('76', 'accounts', 'term-bill-source', '110');
INSERT INTO `resources` VALUES ('77', 'accounts', 'term-bill-update', '110');
INSERT INTO `resources` VALUES ('78', 'accounts', 'account-summary', '110');
INSERT INTO `resources` VALUES ('79', 'accounts', 'check-bill-students', '110');
INSERT INTO `resources` VALUES ('80', 'accounts', 'bill-students', '-1');
INSERT INTO `resources` VALUES ('81', 'accounts', 'initiate-auto-billing', '110');
INSERT INTO `resources` VALUES ('82', 'accounts', 'process-account-balances', '-1');
INSERT INTO `resources` VALUES ('83', 'accounts', 'student-debtors', '110');
INSERT INTO `resources` VALUES ('84', 'accounts', 'student-creditors', '110');
INSERT INTO `resources` VALUES ('85', 'accounts', 'student-bal-source', '110');
INSERT INTO `resources` VALUES ('86', 'accounts', 'offer-discount', '110');
INSERT INTO `resources` VALUES ('87', 'accounts', 'init-compute-balances', '110');
INSERT INTO `resources` VALUES ('88', 'accounts', 'student-discount', '110');
INSERT INTO `resources` VALUES ('89', 'accounts', 'view-bills', '110');
INSERT INTO `resources` VALUES ('90', 'index', 'about', '-1');
INSERT INTO `resources` VALUES ('91', 'accounts', 'show-transaction', '110');
INSERT INTO `resources` VALUES ('92', 'accounts', 'show-trans-source', '110');
INSERT INTO `resources` VALUES ('93', 'accounts', 'view-bill-source', '110');
INSERT INTO `resources` VALUES ('94', 'assignments', 'index', '50');
INSERT INTO `resources` VALUES ('95', 'assignments', 'assign-summary', '50');
INSERT INTO `resources` VALUES ('96', 'assignments', 'assessments', '50');
INSERT INTO `resources` VALUES ('97', 'assignments', 'assessmentlist', '50');
INSERT INTO `resources` VALUES ('98', 'assignments', 'assessment-scores', '50');
INSERT INTO `resources` VALUES ('99', 'error', 'noauth', '-1');
INSERT INTO `resources` VALUES ('100', 'assignments', 'post-marks', '50');
INSERT INTO `resources` VALUES ('101', 'assignments', 'exempt-from-assessment', '50');
INSERT INTO `resources` VALUES ('102', 'assignments', 'ungraded-assessments', '50');
INSERT INTO `resources` VALUES ('103', 'assignments', 'ungraded-assess-source', '50');
INSERT INTO `resources` VALUES ('104', 'assignments', 'zerograded-assessments', '50');
INSERT INTO `resources` VALUES ('105', 'assignments', 'new', '50');
INSERT INTO `resources` VALUES ('106', 'course', 'grade-course', '50');
INSERT INTO `resources` VALUES ('107', 'assignments', 'topicnumber', '50');
INSERT INTO `resources` VALUES ('108', 'assignments', 'delete', '50');
INSERT INTO `resources` VALUES ('109', 'assignments', 'maxmark', '50');
INSERT INTO `resources` VALUES ('110', 'exams', 'index', '50');
INSERT INTO `resources` VALUES ('111', 'exams', 'examsummary', '50');
INSERT INTO `resources` VALUES ('112', 'exams', 'enter-marks', '50');
INSERT INTO `resources` VALUES ('113', 'exams', 'examslist', '50');
INSERT INTO `resources` VALUES ('114', 'exams', 'add-exam-mark', '50');
INSERT INTO `resources` VALUES ('115', 'exams', 'exempt-student', '50');
INSERT INTO `resources` VALUES ('116', 'exams', 'mark-over', '50');
INSERT INTO `resources` VALUES ('117', 'comments', 'index', '50');
INSERT INTO `resources` VALUES ('118', 'exams', 'reset-marks', '50');
INSERT INTO `resources` VALUES ('119', 'error', 'no-term', '-1');
INSERT INTO `resources` VALUES ('120', 'school', 'set-period-context', '0');
INSERT INTO `resources` VALUES ('121', 'comments', 'comment-source', '50');
INSERT INTO `resources` VALUES ('122', 'comments', 'load-comment', '50');
INSERT INTO `resources` VALUES ('123', 'comments', 'update-comment', '50');
INSERT INTO `resources` VALUES ('124', 'assignments', 'outlook', '50');
INSERT INTO `resources` VALUES ('125', 'assignments', 'get-course-report', '50');
INSERT INTO `resources` VALUES ('126', 'assignments', 'show-summary', '50');
INSERT INTO `resources` VALUES ('127', 'reports', 'index', '50');
INSERT INTO `resources` VALUES ('128', 'reports', 'cum-report', '50');
INSERT INTO `resources` VALUES ('129', 'reports', 'report-students', '50');
INSERT INTO `resources` VALUES ('130', 'reports', 'term-report', '50');
INSERT INTO `resources` VALUES ('131', 'reports', 'progress-report', '50');
INSERT INTO `resources` VALUES ('132', 'reports', 'comment-report', '50');
INSERT INTO `resources` VALUES ('133', 'assignments', 'student-details', '50');
INSERT INTO `resources` VALUES ('134', 'assignments', 'monitor-assessments', '100');
INSERT INTO `resources` VALUES ('135', 'student', 'index-alternate', '50');
INSERT INTO `resources` VALUES ('136', 'instructor', 'student-list', '50');
INSERT INTO `resources` VALUES ('2', 'index', 'keep-alive', '0');
INSERT INTO `resources` VALUES ('138', 'accounts', 'pay-fees', '110');
INSERT INTO `resources` VALUES ('139', 'accounts', 'print-receipt', '110');
INSERT INTO `resources` VALUES ('3', 'search', 'build-index', '0');
INSERT INTO `resources` VALUES ('140', 'search', 'search-index', '0');
INSERT INTO `resources` VALUES ('141', 'accounts', 'accounts-statement', '110');
INSERT INTO `resources` VALUES ('142', 'accounts', 'adjust-account', '110');
INSERT INTO `resources` VALUES ('143', 'school', 'setup-classes', '100');
INSERT INTO `resources` VALUES ('144', 'school', 'get-classes-list', '100');
INSERT INTO `resources` VALUES ('145', 'school', 'new-gradelevel', '100');
INSERT INTO `resources` VALUES ('146', 'instructor', 'generatepwd', '0');
INSERT INTO `resources` VALUES ('147', 'school', 'mark-grading', '-1');
INSERT INTO `resources` VALUES ('148', 'instructor', 'profile', '50');
INSERT INTO `resources` VALUES ('149', 'course', 'course-syllabus', '50');
INSERT INTO `resources` VALUES ('150', 'course', 'update-syllabus', '50');
INSERT INTO `resources` VALUES ('151', 'course', 'syllabi-source', '50');
INSERT INTO `resources` VALUES ('152', 'course', 'syllabi-summary', '50');
INSERT INTO `resources` VALUES ('153', 'student', 'academic-profile', '50');
INSERT INTO `resources` VALUES ('154', 'student', 'students-list', '50');
INSERT INTO `resources` VALUES ('155', 'student', 'book-attendance', '40');
INSERT INTO `resources` VALUES ('156', 'student', 'attendance-history', '40');
INSERT INTO `resources` VALUES ('157', 'student', 'attendance-detail', '40');
INSERT INTO `resources` VALUES ('158', 'student', 'academic-profile-chart', '40');
INSERT INTO `resources` VALUES ('159', 'index', 'download', '0');
INSERT INTO `resources` VALUES ('160', 'reports', 'cum-json', '50');
INSERT INTO `resources` VALUES ('161', 'reports', 'term-json', '50');
INSERT INTO `resources` VALUES ('162', 'reports', 'progress-json', '50');
INSERT INTO `resources` VALUES ('163', 'reports', 'comment-json', '50');
INSERT INTO `resources` VALUES ('164', 'reports', 'batch-print', '50');
INSERT INTO `resources` VALUES ('165', 'student', 'class-students-list-json', '50');
INSERT INTO `resources` VALUES ('166', 'index', 'dashboard', '50');
INSERT INTO `resources` VALUES ('167', 'instructor', 'mailbox', '50');
INSERT INTO `resources` VALUES ('168', 'school', 'uploadlogo', '100');
INSERT INTO `resources` VALUES ('170', 'comments', 'load-class-comments', '50');
INSERT INTO `resources` VALUES ('171', 'assignments', 'get-recent-student-assignment', '50');
INSERT INTO `resources` VALUES ('172', 'index', 'available-reports', '50');
INSERT INTO `resources` VALUES ('173', 'index', 'schedule-compile-reports', '50');
