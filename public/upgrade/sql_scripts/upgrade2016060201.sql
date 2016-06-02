CREATE IF NOT EXISTS TABLE `%TABLE_PREFIX%record_search_key_retracted__shadow` (
     `rek_retracted_id` int(11) NOT NULL auto_increment,
     `rek_retracted_stamp` datetime,
      `rek_retracted_pid` varchar(64) default NULL,
     `rek_retracted_xsdmf_id` int(11) default NULL,
      `rek_retracted` int default NULL,
     PRIMARY KEY (`rek_retracted_id`),
     KEY `rek_retracted` (`rek_retracted`),
     KEY `rek_retracted_pid` (`rek_retracted_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;