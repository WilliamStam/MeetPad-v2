<?php
$sql = array(
		"ALTER TABLE  `mp_users_group` ADD  `ID` INT( 6 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;",
		"ALTER TABLE  `mp_users_company` ADD  `ID` INT( 6 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;",
		"ALTER TABLE  `mp_meetings_group` ADD  `ID` INT( 6 ) NULL AUTO_INCREMENT PRIMARY KEY FIRST;",
		"ALTER TABLE  `mp_content_group` ADD  `ID` INT( 6 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;",
		"ALTER TABLE  `mp_users` ADD  `tag` VARCHAR( 250 ) NULL DEFAULT NULL AFTER  `name`;",
		"ALTER TABLE  `mp_users_company` ADD  `tag` VARCHAR( 250 ) NULL DEFAULT NULL AFTER  `companyID`;",
		"ALTER TABLE  `mp_users` DROP `tag`;",
		"ALTER TABLE  `mp_users` ADD  `tag` VARCHAR( 250 ) NULL DEFAULT NULL AFTER  `password`;",
		"ALTER TABLE  `mp_users` ADD  `activated` TINYINT( 1 ) NULL DEFAULT  '0' AFTER  `tag`;",
		"ALTER TABLE `mp_users`  DROP `landing_page`,  DROP `reset_password`;",
		"ALTER TABLE  `mp_content_poll_answers` ADD  `orderby` INT( 3 ) NULL DEFAULT NULL;",
		"ALTER TABLE  `mp_content_files` ADD  `description` TEXT NULL DEFAULT NULL AFTER  `filesize`;",
		"ALTER TABLE  `mp_content_comments` ADD  `parentID` INT( 6 ) NULL DEFAULT NULL AFTER  `contentID` , ADD INDEX (  `parentID` );",
		"ALTER TABLE  `mp_content_poll_voted` ADD  `ID` INT( 7 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;",
		"ALTER TABLE  `mp_content` CHANGE  `poll_allow_nr_votes`  `poll_allow_edit` TINYINT( 1 ) NULL DEFAULT  '0';",
		"UPDATE  `mp_content` SET  `poll_allow_edit` =0 WHERE 1;",
		"ALTER TABLE  `mp_user_attendance` ADD  `ID` INT( 7 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;",
		"ALTER TABLE  `mp_user_seen_content` ADD  `ID` INT( 7 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;",
		"ALTER TABLE  `mp_logs` ADD  `typeID` INT( 6 ) NULL DEFAULT NULL AFTER  `datein` , ADD INDEX (  `typeID` );",
        "ALTER TABLE  `mp_logs` ADD  `data` TEXT NULL DEFAULT NULL AFTER  `text`;",
		"CREATE TABLE IF NOT EXISTS `mp_logs_types` (  `ID` int(6) NOT NULL AUTO_INCREMENT,  `type` varchar(100) DEFAULT NULL,  PRIMARY KEY (`ID`));",
		"INSERT INTO `mp_logs_types` (`ID`, `type`) VALUES (1, 'Authentication'), (2, 'Content'), (3, 'Meetings'), (4, 'Company'), (5, 'Users'), (6, 'Comments'), (7, 'Polls');",
		"TRUNCATE TABLE mp_logs;",
		"DROP TABLE mp_logging;",
		"DROP TABLE mp_logging_types;",
		"DROP TABLE mp_notices;",
		"DROP TABLE mp_user_activity;",
		"DROP TABLE mp_user_seen_comment;",
		"DROP TABLE mp_user_seen_notice;",
		"ALTER TABLE  `mp_logs` ADD  `sessionID` VARCHAR( 50 ) NULL DEFAULT NULL;",
		"TRUNCATE TABLE mp_users_login_log;",
		"ALTER TABLE  `mp_users_login_log` ADD  `sessionID` VARCHAR( 50 ) NULL DEFAULT NULL AFTER  `userID` , ADD INDEX (  `sessionID` );",
		"RENAME TABLE  `mp_users_login_log` TO  `mp_sessions` ;",
		"ALTER TABLE  `mp_logs` ADD INDEX (  `sessionID` );",
		"ALTER TABLE  `mp_content` ADD  `deleted` TINYINT( 1 ) NULL DEFAULT  '0';",
		"ALTER TABLE  `mp_logs` ADD  `fileID` INT( 6 ) NULL DEFAULT NULL AFTER  `optionID`;",
		"INSERT INTO  `mp_logs_types` (`ID` ,`type`)VALUES (NULL ,  'Item File');",
		"ALTER TABLE  `mp_logs` ADD INDEX (  `userID` );",
		"ALTER TABLE  `mp_logs` ADD INDEX (  `contentID` );",
		"ALTER TABLE  `mp_logs` ADD INDEX (  `commentID` );",
		"ALTER TABLE  `mp_logs` ADD INDEX (  `meetingID` );",
		"ALTER TABLE  `mp_logs` ADD INDEX (  `companyID` );",
		"ALTER TABLE  `mp_logs` ADD INDEX (  `optionID` );",
		"ALTER TABLE  `mp_logs` ADD INDEX (  `fileID` );",
		"ALTER TABLE  `mp_content_comments` ADD  `edited_by` INT( 6 ) NULL DEFAULT NULL AFTER  `userName`;",
		"ALTER TABLE  `mp_content_comments` ADD  `edited_date` TIMESTAMP NULL DEFAULT NULL AFTER  `edited_by`;",
		"ALTER TABLE  `mp_content_files` ADD  `deleted` TINYINT( 1 ) NULL DEFAULT  '0';",
		"ALTER TABLE  `mp_content` CHANGE  `orderby`  `orderby` INT( 4 ) NULL DEFAULT  '0';"
		
		
		
        
)

?>
