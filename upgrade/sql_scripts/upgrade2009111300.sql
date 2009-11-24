CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%internal_notes (
	`ain_id` INT(11) NOT NULL AUTO_INCREMENT,
	`ain_pid` varchar(64) NOT NULL,
	`ain_detail` TEXT DEFAULT NULL,
	PRIMARY KEY (ain_id)
);

REPLACE INTO `%TABLE_PREFIX%config` (`config_name`,`config_module`,`config_value`) VALUES ('app_internal_notes', 'core', 'ON');
