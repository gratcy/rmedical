ALTER TABLE `rockmedi_db`.`service_user_privilege` 
ADD COLUMN `group_en` VARCHAR(100) NULL AFTER `group`,
ADD COLUMN `name_en` VARCHAR(100) NULL AFTER `name`;

ALTER TABLE `rockmedi2_db`.`service_user` 
ADD COLUMN `store_id` INT(10) NOT NULL DEFAULT 0 AFTER `remark`;

ALTER TABLE `rockmedi2_db`.`service_user_page` 
ADD COLUMN `group_en` VARCHAR(100) NOT NULL AFTER `group`,
ADD COLUMN `name_en` VARCHAR(100) NOT NULL AFTER `name`;

ALTER TABLE `rockmedi2_db`.`item` 
ADD COLUMN `warranty` TINYINT(1) NOT NULL DEFAULT '1' AFTER `dqty`;

DELETE FROM `rockmedi2_db`.`service_user_page` WHERE `id`='29';
DELETE FROM `rockmedi2_db`.`service_user_page` WHERE `id`='30';
DELETE FROM `rockmedi2_db`.`service_user_page` WHERE `id`='26';
DELETE FROM `rockmedi2_db`.`service_user_page` WHERE `id`='23';

CREATE TABLE `transaction_tab` (
  `tid` int(10) NOT NULL AUTO_INCREMENT,
  `tdate` datetime DEFAULT NULL,
  `tno` varchar(20) NOT NULL,
  `tstore` int(10) NOT NULL,
  `tsid` int(10) NOT NULL,
  `tcid` int(10) NOT NULL,
  `tqty` int(2) NOT NULL,
  `tammount` double NOT NULL,
  `tdiscount` double NOT NULL,
  `ttotal` double NOT NULL,
  `tpayment` tinyint(1) DEFAULT '1',
  `tcardno` varchar(20) DEFAULT NULL,
  `tbank` int(10) DEFAULT NULL,
  `tstatus` tinyint(1) DEFAULT '0',
  `tcreated` varchar(255) DEFAULT NULL,
  `tmodified` varchar(255) DEFAULT NULL,
  `tapproved` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `transaction_detail_tab` (
  `tid` int(10) NOT NULL AUTO_INCREMENT,
  `ttid` int(10) NOT NULL,
  `tpid` int(10) NOT NULL,
  `tqty` int(3) NOT NULL,
  `tprice` double DEFAULT NULL,
  `tstatus` tinyint(1) DEFAULT '0',
  `tcreated` varchar(255) DEFAULT NULL,
  `tmodified` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `sms_queue_tab` (
  `sid` int(10) NOT NULL AUTO_INCREMENT,
  `suid` int(10) DEFAULT NULL,
  `stype` tinyint(1) DEFAULT '1',
  `sphone` varchar(25) DEFAULT NULL,
  `smessage` varchar(255) DEFAULT NULL,
  `sdate` datetime DEFAULT NULL,
  `sdatesent` datetime DEFAULT NULL,
  `sschedule` datetime DEFAULT NULL,
  `sstatus` tinyint(1) DEFAULT '0',
  `screated` varchar(255) DEFAULT NULL,
  `smodified` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `store_tab` (
  `sid` int(10) NOT NULL AUTO_INCREMENT,
  `smanager` int(10) DEFAULT NULL,
  `sname` varchar(150) DEFAULT NULL,
  `sphone` varchar(30) DEFAULT NULL,
  `saddr` text,
  `sstatus` tinyint(1) DEFAULT '0',
  `screated` varchar(255) DEFAULT NULL,
  `smodified` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `media_tab` (
  `mid` int(10) NOT NULL AUTO_INCREMENT,
  `mname` varchar(350) DEFAULT NULL,
  `mfile` varchar(500) DEFAULT NULL,
  `mtype` tinyint(1) DEFAULT '1',
  `mcreated` varchar(255) DEFAULT '1',
  `mmodified` varchar(255) DEFAULT '1',
  `mstatus` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;

CREATE TABLE `email_queue_tab` (
  `eid` int(10) NOT NULL AUTO_INCREMENT,
  `euid` int(10) DEFAULT NULL,
  `etype` tinyint(1) DEFAULT '1',
  `eemail` varchar(100) DEFAULT NULL,
  `esubject` varchar(150) DEFAULT NULL,
  `econtent` text,
  `edate` datetime DEFAULT NULL,
  `edatesent` datetime DEFAULT NULL,
  `eschedule` datetime DEFAULT NULL,
  `estatus` tinyint(1) DEFAULT '0',
  `ecreated` varchar(255) DEFAULT NULL,
  `emodified` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `customer_tab` (
  `cid` int(10) NOT NULL AUTO_INCREMENT,
  `cname` varchar(150) DEFAULT NULL,
  `cbirthday` int(10) DEFAULT NULL,
  `cphone` varchar(35) DEFAULT NULL,
  `cemail` varchar(150) DEFAULT NULL,
  `caddr` text,
  `cstatus` tinyint(1) DEFAULT '0',
  `ccreated` varchar(255) DEFAULT NULL,
  `cmodified` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `bank_tab` (
  `bid` int(10) NOT NULL AUTO_INCREMENT,
  `bname` varchar(150) DEFAULT NULL,
  `bdesc` varchar(350) DEFAULT NULL,
  `bstatus` tinyint(1) DEFAULT '0',
  `bcreated` varchar(255) DEFAULT NULL,
  `bmodified` varchar(255) DEFAULT NULL,
  `bank_tabcol` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`bid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `blasting_tab` (
  `bid` int(10) NOT NULL AUTO_INCREMENT,
  `bdate` datetime DEFAULT NULL,
  `bschedule` datetime DEFAULT NULL,
  `bsubject` varchar(255) NOT NULL,
  `bcontent` text,
  `bblasting` tinyint(1) NOT NULL DEFAULT '1',
  `bsms` varchar(255) DEFAULT NULL,
  `bstatus` tinyint(1) NOT NULL DEFAULT '0',
  `bcreated` varchar(255) DEFAULT NULL,
  `bmodified` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`bid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `blasting_template_tab` (
  `bid` int(10) NOT NULL AUTO_INCREMENT,
  `btype` tinyint(1) NOT NULL DEFAULT '1',
  `bmtype` tinyint(1) DEFAULT '1',
  `bname` varchar(150) NOT NULL,
  `bsubject` varchar(150) DEFAULT NULL,
  `bcontent` text,
  `bstatus` tinyint(1) NOT NULL DEFAULT '0',
  `bmodified` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`bid`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

