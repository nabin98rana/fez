REPLACE INTO %TABLE_PREFIX%search_key VALUES ('core_82','core',82,'Rights','',0,0,0,999,'text','none','',450005,NULL,'text',1,'',0);
CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_rights (
  `rek_rights_id` int(11) NOT NULL auto_increment,
  `rek_rights_pid` varchar(64) character set utf8 collate utf8_general_ci default NULL,
  `rek_rights_xsdmf_id` int(11) default NULL,
  `rek_rights`  text character set utf8 collate utf8_general_ci,
  PRIMARY KEY  (`rek_rights_id`),
  KEY `rek_rights_pid` (`rek_rights_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
