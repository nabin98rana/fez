CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_org_unit_name (
  `rek_org_unit_name_id` int(11) NOT NULL auto_increment,
  `rek_org_unit_name_pid` varchar(64) character set utf8 collate utf8_general_ci default NULL,
  `rek_org_unit_name_xsdmf_id` int(11) default NULL,
  `rek_org_unit_name` varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (`rek_org_unit_name_id`),
  KEY `rek_org_unit_name_pid` (`rek_org_unit_name_pid`, `rek_org_unit_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_org_name (
  `rek_org_name_id` int(11) NOT NULL auto_increment,
  `rek_org_name_pid` varchar(64) character set utf8 collate utf8_general_ci default NULL,
  `rek_org_name_xsdmf_id` int(11) default NULL,
  `rek_org_name` varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (`rek_org_name_id`),
  KEY `rek_org_name_pid` (`rek_org_name_pid`, `rek_org_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_report_number (
  `rek_report_number_id` int(11) NOT NULL auto_increment,
  `rek_report_number_pid` varchar(64) character set utf8 collate utf8_general_ci default NULL,
  `rek_report_number_xsdmf_id` int(11) default NULL,
  `rek_report_number` varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (`rek_report_number_id`),
  KEY `rek_report_number_pid` (`rek_report_number_pid`,`rek_report_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO %TABLE_PREFIX%search_key VALUES 
(70,'Org Unit Name','School, Department or Centre',0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(71,'Org Name','Institution',0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(72,'Report Number',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,'');	

