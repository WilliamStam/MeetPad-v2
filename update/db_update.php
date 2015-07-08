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
	"ALTER TABLE  `mp_content_comments` ADD  `parentID` INT( 6 ) NULL DEFAULT NULL AFTER  `contentID` ,
ADD INDEX (  `parentID` );",
	"ALTER TABLE  `mp_content_poll_voted` ADD  `ID` INT( 7 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;",
	"ALTER TABLE  `mp_content` CHANGE  `poll_allow_nr_votes`  `poll_allow_edit` TINYINT( 1 ) NULL DEFAULT  '0';",
    "UPDATE  `mp_content` SET  `poll_allow_edit` =0 WHERE 1;"

)

?>
