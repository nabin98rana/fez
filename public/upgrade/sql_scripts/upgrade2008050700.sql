INSERT INTO %TABLE_PREFIX%search_key VALUES ('core_80','core',80,'Link','',0,0,0,999,'text','none','',450005,NULL,'text',1,'');
INSERT INTO %TABLE_PREFIX%search_key VALUES ('core_81','core',81,'Link Description','',0,0,0,999,'text','none','',450005,NULL,'text',1,'');

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_link (
  rek_link_id int(11) NOT NULL auto_increment,
  rek_link_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_link_xsdmf_id int(11) default NULL,
  rek_link  text character set utf8 collate utf8_general_ci,
  PRIMARY KEY  (rek_link_id),
  KEY rek_link_pid (rek_link_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_link_description (
  rek_link_description_id int(11) NOT NULL auto_increment,
  rek_link_description_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_link_description_xsdmf_id int(11) default NULL,
  rek_link_description  text character set utf8 collate utf8_general_ci,
  PRIMARY KEY  (rek_link_description_id),
  KEY rek_link_description_pid (rek_link_description_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_parent_publication (
  rek_parent_publication_id int(11) NOT NULL auto_increment,
  rek_parent_publication_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_parent_publication_xsdmf_id int(11) default NULL,
  rek_parent_publication  varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_parent_publication_id),
  KEY rek_parent_publication_pid (rek_parent_publication_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_convener (
  rek_convener_id int(11) NOT NULL auto_increment,
  rek_convener_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_convener_xsdmf_id int(11) default NULL,
  rek_convener  varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_convener_id),
  KEY rek_convener_pid (rek_convener_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

