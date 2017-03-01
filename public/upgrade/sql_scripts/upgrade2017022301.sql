CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_datastream_policy` (
     `rek_datastream_policy_id` int(11) NOT NULL auto_increment,
     `rek_datastream_policy_pid` varchar(64) default NULL,
     `rek_datastream_policy_xsdmf_id` int(11) default NULL,
     `rek_datastream_policy` varchar(255) default NULL,
     	PRIMARY KEY (`rek_datastream_policy_id`),
	    UNIQUE INDEX `unique_constraint` (`rek_datastream_policy_pid`, `rek_datastream_policy`),
	    UNIQUE INDEX `rek_datastream_policy_pid` (`rek_datastream_policy_pid`),
	    KEY `rek_datastream_policy` (`rek_datastream_policy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_datastream_policy__shadow` (
     `rek_datastream_policy_id` int(11) NOT NULL auto_increment,
     `rek_datastream_policy_pid` varchar(64) default NULL,
     `rek_datastream_policy_xsdmf_id` int(11) default NULL,
     `rek_datastream_policy` varchar(255) default NULL,
     	PRIMARY KEY (`rek_datastream_policy_id`),
	    UNIQUE INDEX `unique_constraint` (`rek_datastream_policy_pid`, `rek_datastream_policy`),
	    UNIQUE INDEX `rek_datastream_policy_pid` (`rek_datastream_policy_pid`),
	    KEY `rek_datastream_policy` (`rek_datastream_policy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `%TABLE_PREFIX%record_search_key_datastream_policy__shadow` ADD COLUMN `rek_datastream_policy_version` varchar(100) NOT NULL AFTER `rek_datastream_policy_stamp`;
ALTER TABLE `%TABLE_PREFIX%record_search_key_datastream_policy` ADD CONSTRAINT `rek_datpo_foreign` FOREIGN KEY (`rek_datastream_policy_pid`) REFERENCES `%TABLE_PREFIX%record_search_key` (`rek_pid`) ON DELETE CASCADE;
ALTER TABLE `%TABLE_PREFIX%record_search_key_datastream_policy__shadow` ADD CONSTRAINT `rek_datpo__foreign` FOREIGN KEY (`rek_datastream_policy_version`) REFERENCES `%TABLE_PREFIX%record_search_key__shadow` (`rek_version`) ON DELETE CASCADE;
