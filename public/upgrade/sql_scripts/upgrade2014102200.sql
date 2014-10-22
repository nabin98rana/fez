CREATE TABLE %TABLE_PREFIX%record_search_key_refereed_source (
  rek_refereed_source_id int(11) NOT NULL AUTO_INCREMENT,
  rek_refereed_source_pid varchar(64) DEFAULT NULL,
  rek_refereed_source_xsdmf_id int(11) DEFAULT NULL,
  rek_refereed_source varchar(255) DEFAULT NULL,
  PRIMARY KEY (rek_refereed_source_id),
  KEY rek_refereed_source (rek_refereed_source),
  KEY rek_refereed_source_pid (rek_refereed_source_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
