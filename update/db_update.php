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
	"ALTER TABLE `mp_users`  DROP `landing_page`,  DROP `reset_password`;"

)

?>
