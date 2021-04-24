/*
Navicat MySQL Data Transfer

Source Server         : CentOS Server
Source Server Version : 50161
Source Host           : 192.168.10.48:3306
Source Database       : cent

Target Server Type    : MYSQL
Target Server Version : 50161
File Encoding         : 65001

Date: 2013-01-07 10:50:06
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `accountbalances`
-- ----------------------------
DROP TABLE IF EXISTS `accountbalances`;
CREATE TABLE `accountbalances` (
  `cl_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cl_GPSN_ID` bigint(255) NOT NULL,
  `balancedate` datetime NOT NULL,
  `balanceamount` float NOT NULL DEFAULT '0',
  `balanceterm` int(11) NOT NULL,
  `balanceyear` varchar(255) NOT NULL,
  PRIMARY KEY (`cl_id`),
  UNIQUE KEY `balIndex` (`cl_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of accountbalances
-- ----------------------------

-- ----------------------------
-- Table structure for `assignmentmarks`
-- ----------------------------
DROP TABLE IF EXISTS `assignmentmarks`;
CREATE TABLE `assignmentmarks` (
  `cl_markid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cl_studentid` varchar(13) NOT NULL,
  `cl_assignmentid` int(11) NOT NULL,
  `cl_mark` float DEFAULT NULL,
  `cl_exempt` int(1) DEFAULT '0',
  `cl_approved` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cl_markid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of assignmentmarks
-- ----------------------------

-- ----------------------------
-- Table structure for `assignments`
-- ----------------------------
DROP TABLE IF EXISTS `assignments`;
CREATE TABLE `assignments` (
  `cl_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cl_instructor` int(11) NOT NULL,
  `cl_topic` varchar(124) DEFAULT NULL,
  `cl_term` int(11) NOT NULL,
  `cl_year` varchar(9) NOT NULL,
  `cl_type` varchar(3) NOT NULL,
  `cl_date` datetime NOT NULL,
  `cl_datedued` datetime NOT NULL,
  `cl_course` int(11) NOT NULL,
  `cl_grade` int(11) NOT NULL,
  `cl_maxmark` float NOT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of assignments
-- ----------------------------

-- ----------------------------
-- Table structure for `billedstudents`
-- ----------------------------
DROP TABLE IF EXISTS `billedstudents`;
CREATE TABLE `billedstudents` (
  `cl_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cl_GPSN_ID` bigint(255) NOT NULL,
  `term` int(11) NOT NULL,
  `year` varchar(255) NOT NULL,
  PRIMARY KEY (`cl_id`),
  UNIQUE KEY `billindex` (`cl_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of billedstudents
-- ----------------------------

-- ----------------------------
-- Table structure for `comments`
-- ----------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `cl_ctcomment` text,
  `cl_prcomment` text,
  `cl_attendcomment` text,
  `cl_studentid` bigint(20) NOT NULL,
  `cl_term` int(1) NOT NULL,
  `cl_year` varchar(9) NOT NULL,
  `cl_grade` int(11) NOT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of comments
-- ----------------------------

-- ----------------------------
-- Table structure for `coursecontent`
-- ----------------------------
DROP TABLE IF EXISTS `coursecontent`;
CREATE TABLE `coursecontent` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `cl_courseid` int(11) NOT NULL,
  `cl_term` int(11) NOT NULL,
  `cl_year` varchar(12) NOT NULL,
  `cl_grade` int(11) NOT NULL,
  `cl_content` longtext,
  PRIMARY KEY (`cl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of coursecontent
-- ----------------------------

-- ----------------------------
-- Table structure for `courseinstructors`
-- ----------------------------
DROP TABLE IF EXISTS `courseinstructors`;
CREATE TABLE `courseinstructors` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `cl_course` int(11) NOT NULL,
  `cl_instructor` bigint(20) NOT NULL,
  `cl_grades` varchar(128) NOT NULL,
  `cl_dateassigned` datetime NOT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of courseinstructors
-- ----------------------------

-- ----------------------------
-- Table structure for `courses`
-- ----------------------------
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `cl_courseid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cl_coursename` varchar(128) NOT NULL,
  `cl_gradelevels` varchar(128) NOT NULL,
  `cl_examinable` int(1) NOT NULL DEFAULT '1',
  `cl_gradeexempt` varchar(255) DEFAULT '[]',
  `cl_shared` int(11) NOT NULL DEFAULT '0',
  `cl_courseorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cl_courseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of courses
-- ----------------------------

-- ----------------------------
-- Table structure for `exammarks`
-- ----------------------------
DROP TABLE IF EXISTS `exammarks`;
CREATE TABLE `exammarks` (
  `cl_examid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cl_studentid` bigint(20) NOT NULL,
  `cl_courseid` int(11) NOT NULL,
  `cl_term` int(11) NOT NULL,
  `cl_year` varchar(10) NOT NULL,
  `cl_mark` float DEFAULT NULL,
  `cl_grade` int(11) NOT NULL,
  `cl_comment` text,
  `cl_maxmark` float DEFAULT '0',
  `cl_exempt` int(1) DEFAULT '0',
  `cl_instructor` int(11) DEFAULT NULL,
  PRIMARY KEY (`cl_examid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of exammarks
-- ----------------------------

-- ----------------------------
-- Table structure for `examsexempt`
-- ----------------------------
DROP TABLE IF EXISTS `examsexempt`;
CREATE TABLE `examsexempt` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `cl_studentid` bigint(20) NOT NULL,
  `cl_courseid` int(11) NOT NULL,
  `cl_year` varchar(255) NOT NULL,
  `cl_term` int(11) NOT NULL,
  `cl_grade` int(11) NOT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of examsexempt
-- ----------------------------

-- ----------------------------
-- Table structure for `examsmarkover`
-- ----------------------------
DROP TABLE IF EXISTS `examsmarkover`;
CREATE TABLE `examsmarkover` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `term` int(11) NOT NULL,
  `year` varchar(9) NOT NULL,
  `grade` int(11) NOT NULL,
  `course` int(11) NOT NULL,
  `markover` int(11) NOT NULL,
  `instructor` int(11) DEFAULT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of examsmarkover
-- ----------------------------

-- ----------------------------
-- Table structure for `feegroups`
-- ----------------------------
DROP TABLE IF EXISTS `feegroups`;
CREATE TABLE `feegroups` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(255) NOT NULL,
  `gradelevels` varchar(255) NOT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of feegroups
-- ----------------------------

-- ----------------------------
-- Table structure for `instructors`
-- ----------------------------
DROP TABLE IF EXISTS `instructors`;
CREATE TABLE `instructors` (
  `cl_IID` int(11) NOT NULL AUTO_INCREMENT,
  `lastname` varchar(20) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `role` int(11) NOT NULL,
  `cl_grades` varchar(128) DEFAULT '0',
  `cl_courses` varchar(128) DEFAULT NULL,
  `cl_classteacher` int(11) DEFAULT NULL,
  `username` varchar(64) DEFAULT NULL,
  `password` varchar(256) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telno` varchar(15) DEFAULT NULL,
  `ecode` int(11) DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cl_IID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of instructors
-- ----------------------------

-- ----------------------------
-- Table structure for `menu`
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `route` varchar(255) DEFAULT NULL,
  `icon_class` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of menu
-- ----------------------------

-- ----------------------------
-- Table structure for `messages`
-- ----------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `cl_id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `fromName` varchar(255) NOT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of messages
-- ----------------------------

-- ----------------------------
-- Table structure for `options`
-- ----------------------------
DROP TABLE IF EXISTS `options`;
CREATE TABLE `options` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `optionname` varchar(255) NOT NULL,
  `optionvalue` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of options
-- ----------------------------

-- ----------------------------
-- Table structure for `preparedreport`
-- ----------------------------
DROP TABLE IF EXISTS `preparedreport`;
CREATE TABLE `preparedreport` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `cl_courseid` int(11) NOT NULL,
  `cl_studentid` bigint(20) NOT NULL,
  `cl_term` int(11) NOT NULL,
  `cl_year` varchar(10) NOT NULL,
  `cl_grade` int(11) NOT NULL,
  `HWK` float DEFAULT NULL,
  `UNT` float DEFAULT NULL,
  `GPW` float DEFAULT NULL,
  `CWK` float DEFAULT NULL,
  `PRJ` float DEFAULT NULL,
  `TCM` float DEFAULT NULL,
  `EXM` float DEFAULT NULL,
  `TM` float DEFAULT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of preparedreport
-- ----------------------------

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of resources
-- ----------------------------

-- ----------------------------
-- Table structure for `roles`
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role` int(11) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of roles
-- ----------------------------

-- ----------------------------
-- Table structure for `scheduledjobs`
-- ----------------------------
DROP TABLE IF EXISTS `scheduledjobs`;
CREATE TABLE `scheduledjobs` (
  `jobid` bigint(20) NOT NULL,
  `jobname` varchar(255) NOT NULL,
  `joburl` varchar(255) NOT NULL,
  `jobsysid` int(11) NOT NULL,
  `jobdate` datetime NOT NULL,
  PRIMARY KEY (`jobid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of scheduledjobs
-- ----------------------------

-- ----------------------------
-- Table structure for `store`
-- ----------------------------
DROP TABLE IF EXISTS `store`;
CREATE TABLE `store` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `itemname` varchar(255) NOT NULL,
  `itemquantity` int(11) NOT NULL,
  `itemprice` float NOT NULL,
  `itemcode` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of store
-- ----------------------------

-- ----------------------------
-- Table structure for `storepurchases`
-- ----------------------------
DROP TABLE IF EXISTS `storepurchases`;
CREATE TABLE `storepurchases` (
  `cl_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`cl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED;

-- ----------------------------
-- Records of storepurchases
-- ----------------------------

-- ----------------------------
-- Table structure for `students`
-- ----------------------------
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `cl_GPSN_ID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `cl_FirstName` varchar(50) NOT NULL,
  `cl_LastName` varchar(50) NOT NULL,
  `cl_OtherName` varchar(50) DEFAULT NULL,
  `cl_DOB` date DEFAULT NULL,
  `cl_POB` varchar(10) DEFAULT NULL,
  `cl_Gender` int(1) NOT NULL,
  `cl_DateEnrolled` date DEFAULT NULL,
  `cl_DateWithdrawn` date DEFAULT NULL,
  `cl_GradeLevel` int(11) DEFAULT NULL,
  `cl_Active` int(1) DEFAULT '1',
  `cl_ContactEmail` varchar(255) DEFAULT NULL,
  `cl_ContactTel` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`cl_GPSN_ID`),
  UNIQUE KEY `studindex` (`cl_GPSN_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of students
-- ----------------------------

-- ----------------------------
-- Table structure for `termbills`
-- ----------------------------
DROP TABLE IF EXISTS `termbills`;
CREATE TABLE `termbills` (
  `cl_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `feegroup` int(11) NOT NULL DEFAULT '-1',
  `specificgrades` int(11) DEFAULT NULL,
  `amount` float NOT NULL,
  `mandatory` int(11) NOT NULL DEFAULT '1',
  `term` int(11) NOT NULL,
  `year` varchar(9) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`cl_id`),
  UNIQUE KEY `billindex` (`cl_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of termbills
-- ----------------------------

-- ----------------------------
-- Table structure for `termdates`
-- ----------------------------
DROP TABLE IF EXISTS `termdates`;
CREATE TABLE `termdates` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `term` int(11) NOT NULL,
  `cl_startdate` datetime NOT NULL,
  `cl_enddate` datetime NOT NULL,
  `year` varchar(9) NOT NULL,
  `holidays` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of termdates
-- ----------------------------

-- ----------------------------
-- Table structure for `transactions`
-- ----------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `cl_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transtype` int(11) NOT NULL,
  `cl_GPSN_ID` bigint(255) NOT NULL,
  `gradelevel` int(11) NOT NULL,
  `transdate` datetime NOT NULL,
  `transdescription` varchar(255) NOT NULL,
  `transinitiator` int(11) NOT NULL,
  `transpaymode` int(11) DEFAULT NULL,
  `transslipno` varchar(255) DEFAULT NULL,
  `transamount` float NOT NULL,
  `transterm` int(11) NOT NULL,
  `transyear` varchar(255) NOT NULL,
  PRIMARY KEY (`cl_id`),
  UNIQUE KEY `transindex` (`cl_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of transactions
-- ----------------------------

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(250) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `role` int(11) DEFAULT NULL,
  `active` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of users
-- ----------------------------

-- ----------------------------
-- View structure for `vw_accountbalances`
-- ----------------------------
DROP VIEW IF EXISTS `vw_accountbalances`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_accountbalances` AS select `accountbalances`.`cl_GPSN_ID` AS `cl_GPSN_ID`,concat_ws(' ',`students`.`cl_FirstName`,`students`.`cl_LastName`) AS `fullname`,`students`.`cl_GradeLevel` AS `cl_GradeLevel`,`students`.`cl_ContactTel` AS `cl_ContactTel`,`students`.`cl_ContactEmail` AS `cl_ContactEmail`,`accountbalances`.`balanceamount` AS `balanceamount`,`accountbalances`.`balancedate` AS `balancedate`,`accountbalances`.`balanceterm` AS `balanceterm`,`accountbalances`.`balanceyear` AS `balanceyear`,`accountbalances`.`cl_id` AS `cl_id` from (`accountbalances` join `students` on((`accountbalances`.`cl_GPSN_ID` = `students`.`cl_GPSN_ID`))) order by `students`.`cl_GradeLevel` ;

-- ----------------------------
-- View structure for `vw_assignmentmarks`
-- ----------------------------
DROP VIEW IF EXISTS `vw_assignmentmarks`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_assignmentmarks` AS select `assignmentmarks`.`cl_studentid` AS `cl_studentid`,`assignmentmarks`.`cl_assignmentid` AS `cl_assignmentid`,`assignmentmarks`.`cl_mark` AS `cl_mark`,`assignmentmarks`.`cl_exempt` AS `cl_exempt`,`assignmentmarks`.`cl_approved` AS `cl_approved`,concat_ws(' ',`students`.`cl_FirstName`,`students`.`cl_LastName`) AS `fullname`,`students`.`cl_LastName` AS `cl_LastName`,`students`.`cl_FirstName` AS `cl_FirstName`,`assignmentmarks`.`cl_markid` AS `cl_markid` from ((`assignmentmarks` join `assignments` on((`assignments`.`cl_id` = `assignmentmarks`.`cl_assignmentid`))) join `students` on((`assignmentmarks`.`cl_studentid` = `students`.`cl_GPSN_ID`))) order by concat_ws(' ',`students`.`cl_FirstName`,`students`.`cl_LastName`) ;

-- ----------------------------
-- View structure for `vw_assignmentmarks_stds`
-- ----------------------------
DROP VIEW IF EXISTS `vw_assignmentmarks_stds`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_assignmentmarks_stds` AS select `assignmentmarks`.`cl_studentid` AS `cl_studentid`,`assignmentmarks`.`cl_assignmentid` AS `cl_assignmentid`,`assignmentmarks`.`cl_mark` AS `cl_mark`,`assignmentmarks`.`cl_exempt` AS `cl_exempt`,`assignmentmarks`.`cl_approved` AS `cl_approved`,concat_ws(' ',`students`.`cl_FirstName`,`students`.`cl_LastName`) AS `fullname`,`assignments`.`cl_topic` AS `cl_topic`,`assignments`.`cl_maxmark` AS `cl_maxmark`,`assignments`.`cl_grade` AS `cl_grade`,`assignmentmarks`.`cl_markid` AS `cl_markid` from ((`assignmentmarks` join `assignments` on((`assignments`.`cl_id` = `assignmentmarks`.`cl_assignmentid`))) join `students` on((`assignmentmarks`.`cl_studentid` = `students`.`cl_GPSN_ID`))) order by concat_ws(' ',`students`.`cl_FirstName`,`students`.`cl_LastName`) ;

-- ----------------------------
-- View structure for `vw_assignments`
-- ----------------------------
DROP VIEW IF EXISTS `vw_assignments`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_assignments` AS select `assignments`.`cl_instructor` AS `cl_instructor`,`assignments`.`cl_topic` AS `cl_topic`,`assignments`.`cl_term` AS `cl_term`,`assignments`.`cl_year` AS `cl_year`,`assignments`.`cl_type` AS `cl_type`,`assignments`.`cl_date` AS `cl_date`,`assignments`.`cl_course` AS `cl_course`,`assignments`.`cl_datedued` AS `cl_datedued`,`assignments`.`cl_grade` AS `cl_grade`,`assignments`.`cl_maxmark` AS `cl_maxmark`,`assignments`.`cl_id` AS `cl_id`,`courses`.`cl_coursename` AS `cl_coursename` from (`assignments` join `courses` on((`assignments`.`cl_course` = `courses`.`cl_courseid`))) order by `assignments`.`cl_date` desc ;

-- ----------------------------
-- View structure for `vw_assignmentstudents`
-- ----------------------------
DROP VIEW IF EXISTS `vw_assignmentstudents`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_assignmentstudents` AS select distinct `assignmentmarks`.`cl_studentid` AS `cl_GPSN_ID`,(select concat_ws(' ',`students`.`cl_FirstName`,`students`.`cl_LastName`) from `students` where (`students`.`cl_GPSN_ID` = `assignmentmarks`.`cl_studentid`)) AS `fullname`,`assignments`.`cl_term` AS `cl_term`,`assignments`.`cl_year` AS `cl_year`,`assignments`.`cl_grade` AS `cl_GradeLevel`,`assignments`.`cl_course` AS `cl_course` from (`assignmentmarks` join `assignments` on((`assignmentmarks`.`cl_assignmentid` = `assignments`.`cl_id`))) ;

-- ----------------------------
-- View structure for `vw_commentreport`
-- ----------------------------
DROP VIEW IF EXISTS `vw_commentreport`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_commentreport` AS select concat_ws(' ',`students`.`cl_FirstName`,`students`.`cl_OtherName`,`students`.`cl_LastName`) AS `Fullname`,`preparedreport`.`cl_term` AS `cl_term`,`preparedreport`.`cl_year` AS `cl_year`,`preparedreport`.`cl_grade` AS `cl_grade`,`preparedreport`.`cl_courseid` AS `cl_courseid`,`preparedreport`.`TM` AS `TM`,`exammarks`.`cl_comment` AS `cl_comment`,`preparedreport`.`cl_studentid` AS `cl_studentid`,`coursecontent`.`cl_content` AS `cl_content`,`courses`.`cl_coursename` AS `cl_coursename`,`exammarks`.`cl_exempt` AS `cl_exempt`,`preparedreport`.`cl_id` AS `cl_id` from ((((`preparedreport` join `students` on((`preparedreport`.`cl_studentid` = `students`.`cl_GPSN_ID`))) left join `exammarks` on(((`preparedreport`.`cl_term` = `exammarks`.`cl_term`) and (`preparedreport`.`cl_year` = `exammarks`.`cl_year`) and (`preparedreport`.`cl_grade` = `exammarks`.`cl_grade`) and (`preparedreport`.`cl_courseid` = `exammarks`.`cl_courseid`) and (`preparedreport`.`cl_studentid` = `exammarks`.`cl_studentid`)))) left join `coursecontent` on(((`preparedreport`.`cl_courseid` = `coursecontent`.`cl_courseid`) and (`preparedreport`.`cl_term` = `coursecontent`.`cl_term`) and (`preparedreport`.`cl_year` = convert(`coursecontent`.`cl_year` using utf8)) and (`preparedreport`.`cl_grade` = `coursecontent`.`cl_grade`)))) join `courses` on((`preparedreport`.`cl_courseid` = `courses`.`cl_courseid`))) ;

-- ----------------------------
-- View structure for `vw_cumreport`
-- ----------------------------
DROP VIEW IF EXISTS `vw_cumreport`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_cumreport` AS select `assignments`.`cl_id` AS `cl_id`,`assignments`.`cl_instructor` AS `cl_instructor`,`assignments`.`cl_term` AS `cl_term`,`assignments`.`cl_year` AS `cl_year`,`assignments`.`cl_type` AS `cl_type`,`assignments`.`cl_course` AS `cl_course`,`assignments`.`cl_grade` AS `cl_grade`,`assignments`.`cl_maxmark` AS `cl_maxmark`,`assignmentmarks`.`cl_assignmentid` AS `cl_assignmentid`,`students`.`cl_FirstName` AS `cl_FirstName`,`students`.`cl_LastName` AS `cl_LastName`,`students`.`cl_OtherName` AS `cl_OtherName`,`assignmentmarks`.`cl_exempt` AS `cl_exempt`,`assignmentmarks`.`cl_mark` AS `cl_mark`,`assignmentmarks`.`cl_studentid` AS `cl_studentid` from ((`assignments` join `assignmentmarks` on((`assignments`.`cl_id` = `assignmentmarks`.`cl_assignmentid`))) join `students` on((`assignmentmarks`.`cl_studentid` = `students`.`cl_GPSN_ID`))) order by `assignmentmarks`.`cl_studentid`,`assignments`.`cl_course` ;

-- ----------------------------
-- View structure for `vw_examsarchive`
-- ----------------------------
DROP VIEW IF EXISTS `vw_examsarchive`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_examsarchive` AS select `exammarks`.`cl_examid` AS `cl_examid`,`exammarks`.`cl_studentid` AS `cl_studentid`,`exammarks`.`cl_courseid` AS `cl_courseid`,`exammarks`.`cl_term` AS `cl_term`,`exammarks`.`cl_year` AS `cl_year`,`exammarks`.`cl_mark` AS `cl_mark`,`exammarks`.`cl_grade` AS `cl_grade`,`exammarks`.`cl_comment` AS `cl_comment`,`exammarks`.`cl_maxmark` AS `cl_maxmark`,`students`.`cl_FirstName` AS `cl_FirstName`,`students`.`cl_LastName` AS `cl_LastName`,concat_ws(' ',`students`.`cl_FirstName`,`students`.`cl_LastName`) AS `fullname`,`exammarks`.`cl_instructor` AS `cl_instructor`,`exammarks`.`cl_exempt` AS `cl_exempt` from (`exammarks` join `students` on((`exammarks`.`cl_studentid` = `students`.`cl_GPSN_ID`))) ;

-- ----------------------------
-- View structure for `vw_getcourseinstructor`
-- ----------------------------
DROP VIEW IF EXISTS `vw_getcourseinstructor`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_getcourseinstructor` AS select `courseinstructors`.`cl_course` AS `cl_course`,concat_ws(' ',`instructors`.`firstname`,`instructors`.`lastname`) AS `fullname`,`courseinstructors`.`cl_grades` AS `cl_grades`,`courseinstructors`.`cl_id` AS `cl_id` from (`instructors` join `courseinstructors` on((`instructors`.`cl_IID` = `courseinstructors`.`cl_instructor`))) ;

-- ----------------------------
-- View structure for `vw_getexempt`
-- ----------------------------
DROP VIEW IF EXISTS `vw_getexempt`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_getexempt` AS select `assignmentmarks`.`cl_exempt` AS `cl_exempt`,sum(`assignments`.`cl_maxmark`) AS `ETotal`,`assignments`.`cl_grade` AS `cl_grade`,`assignments`.`cl_course` AS `cl_course`,`assignments`.`cl_term` AS `cl_term`,`assignments`.`cl_year` AS `cl_year`,`assignments`.`cl_type` AS `cl_type`,sum(`assignmentmarks`.`cl_mark`) AS `EMarkTotal`,`assignmentmarks`.`cl_studentid` AS `cl_studentid` from (`assignmentmarks` join `assignments` on((`assignments`.`cl_id` = `assignmentmarks`.`cl_assignmentid`))) where (`assignmentmarks`.`cl_exempt` = 1) group by `assignmentmarks`.`cl_studentid`,`assignments`.`cl_grade`,`assignments`.`cl_course`,`assignments`.`cl_type`,`assignmentmarks`.`cl_exempt`,`assignments`.`cl_term`,`assignments`.`cl_year` ;

-- ----------------------------
-- View structure for `vw_instructorcourses`
-- ----------------------------
DROP VIEW IF EXISTS `vw_instructorcourses`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_instructorcourses` AS select `courseinstructors`.`cl_instructor` AS `cl_instructor`,`courseinstructors`.`cl_course` AS `cl_courseid`,`courseinstructors`.`cl_grades` AS `cl_gradelevels`,`courses`.`cl_coursename` AS `cl_coursename`,`courseinstructors`.`cl_id` AS `cl_id`,`courses`.`cl_shared` AS `cl_shared`,concat_ws(' ',`instructors`.`firstname`,`instructors`.`lastname`) AS `fullname` from ((`courseinstructors` join `courses` on((`courseinstructors`.`cl_course` = `courses`.`cl_courseid`))) join `instructors` on((`courseinstructors`.`cl_instructor` = `instructors`.`cl_IID`))) ;

-- ----------------------------
-- View structure for `vw_transactions`
-- ----------------------------
DROP VIEW IF EXISTS `vw_transactions`;
CREATE ALGORITHM=UNDEFINED DEFINER=`developer`@`%` SQL SECURITY DEFINER VIEW `vw_transactions` AS select concat_ws(' ',`students`.`cl_FirstName`,`students`.`cl_LastName`) AS `fullname`,`transactions`.`transtype` AS `transtype`,`transactions`.`gradelevel` AS `gradelevel`,`transactions`.`transdate` AS `transdate`,`transactions`.`cl_id` AS `cl_id`,`transactions`.`cl_GPSN_ID` AS `cl_GPSN_ID`,`transactions`.`transdescription` AS `transdescription`,(select concat_ws(' ',`instructors`.`firstname`,`instructors`.`lastname`) from `instructors` where (`transactions`.`transinitiator` = `instructors`.`cl_IID`)) AS `transuser`,`transactions`.`transpaymode` AS `transpaymode`,`transactions`.`transslipno` AS `transslipno`,`transactions`.`transamount` AS `transamount`,`transactions`.`transterm` AS `transterm`,`transactions`.`transyear` AS `transyear`,`transactions`.`transinitiator` AS `transinitiator` from (`transactions` join `students` on((`transactions`.`cl_GPSN_ID` = `students`.`cl_GPSN_ID`))) ;

-- ----------------------------
-- Procedure structure for `sp_assessmentstats`
-- ----------------------------
DROP PROCEDURE IF EXISTS `sp_assessmentstats`;
DELIMITER ;;
CREATE DEFINER=`developer`@`%` PROCEDURE `sp_assessmentstats`(IN `instructor` int,IN `term` int,IN `ayear` varchar(9))
BEGIN
	#Routine body goes here...
	select COUNT(DISTINCT cl_id) as cnt , (SELECT cl_coursename from courses where cl_courseid = assignments.cl_course) as subject, cl_grade from assignments where cl_instructor = instructor and cl_term = term and cl_year = ayear
	GROUP BY cl_course, cl_grade
	ORDER BY cl_grade;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for `sp_examscorelist`
-- ----------------------------
DROP PROCEDURE IF EXISTS `sp_examscorelist`;
DELIMITER ;;
CREATE DEFINER=`developer`@`%` PROCEDURE `sp_examscorelist`(IN `term` int,IN `ayear` varchar(9),IN `courseid` int,`studentgrade` int, IN `examgrade` int)
BEGIN
	#Routine body goes here...
select SQL_CALC_FOUND_ROWS cl_GPSN_ID, cl_LastName, 
(select cl_mark from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = term and exammarks.cl_year = ayear and exammarks.cl_courseid = courseid and exammarks.cl_grade = examgrade) as exammark,
(select cl_exempt from exammarks where exammarks.cl_studentid = students.cl_GPSN_ID and exammarks.cl_term = term and exammarks.cl_year = ayear and exammarks.cl_courseid = courseid and exammarks.cl_grade = examgrade) as exempt,
(select markover from examsmarkover where examsmarkover.term = term and examsmarkover.year = ayear and examsmarkover.course = courseid and examsmarkover.grade = examgrade) as markedover
from students where cl_GradeLevel = studentgrade and cl_Active = TRUE;

END
;;
DELIMITER ;

-- ----------------------------
-- Function structure for `fn_accountbalance`
-- ----------------------------
DROP FUNCTION IF EXISTS `fn_accountbalance`;
DELIMITER ;;
CREATE DEFINER=`developer`@`%` FUNCTION `fn_accountbalance`(`studentid` bigint, `term` int, `ayear` varchar(9)) RETURNS float
BEGIN
	#Routine body goes here...
	DECLARE payables FLOAT;
  DECLARE paid FLOAT;
  DECLARE discount FLOAT;
  DECLARE balance FLOAT;

	SELECT SUM(transamount) INTO payables from transactions
  WHERE transactions.transtype = 0 
				AND transactions.transterm = term 
				AND transactions.transyear = ayear
				AND transactions.cl_GPSN_ID = studentid;
   
  SELECT SUM(transamount) INTO paid from transactions
  WHERE transactions.transtype = 1 
				AND transactions.transterm = term 
				AND transactions.transyear = ayear
				AND transactions.cl_GPSN_ID = studentid;

  SELECT SUM(transamount) INTO discount from transactions
  WHERE transactions.transtype = 2 
				AND transactions.transterm = term 
				AND transactions.transyear = ayear
				AND transactions.cl_GPSN_ID = studentid;

  if (ISNULL(payables)) THEN
		set payables = 0;
	end IF;

  if (ISNULL(paid)) THEN
		set paid = 0;
  end if;

  if (ISNULL(discount)) THEN
		set discount = 0;
  end if;



  SET balance = (payables - discount - paid);

	RETURN balance;
END
;;
DELIMITER ;

-- ----------------------------
-- Function structure for `fn_groupFee`
-- ----------------------------
DROP FUNCTION IF EXISTS `fn_groupFee`;
DELIMITER ;;
CREATE DEFINER=`developer`@`%` FUNCTION `fn_groupFee`(`group` int, `type` int, `mandatory` int) RETURNS float
BEGIN
	#Routine body goes here...
	DECLARE termFee INT;

	select SUM(amount) into termfee from termbills
	where (termbills.feegroup = `group` or termbills.feegroup = -1) 
				and specificgrades = -1 
				and termbills.type = `type` 
				and termbills.mandatory = `mandatory`; 

	RETURN termFee;
END
;;
DELIMITER ;
