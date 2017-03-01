CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_extent` (
     `rek_extent_id` int(11) NOT NULL auto_increment,
     `rek_extent_pid` varchar(64) default NULL,
     `rek_extent_xsdmf_id` int(11) default NULL,
     `rek_extent` varchar(255) default NULL,
     	PRIMARY KEY (`rek_extent_id`),
	    UNIQUE INDEX `unique_constraint` (`rek_extent_pid`, `rek_extent`),
	    UNIQUE INDEX `rek_extent_pid` (`rek_extent_pid`),
	    KEY `rek_extent` (`rek_extent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
