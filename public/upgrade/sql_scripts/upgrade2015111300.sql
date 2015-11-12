CREATE TABLE %TABLE_PREFIXrecord_search_key_retracted (
  rek_retracted_id int(11) NOT NULL auto_increment,
  rek_retracted_pid varchar(64) default NULL,
  rek_retracted_xsdmf_id int(11) default NULL,
  rek_retracted int default NULL,
  PRIMARY KEY (rek_retracted_id),
  KEY rek_retracted (rek_retracted),
  KEY rek_retracted_pid (rek_retracted_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
