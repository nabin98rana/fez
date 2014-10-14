CREATE TABLE %TABLE_PREFIX%record_search_key_herdc_code (
  rek_herdc_code_id int(11) NOT NULL AUTO_INCREMENT,
  rek_herdc_code_pid varchar(64) DEFAULT NULL,
  rek_herdc_code_xsdmf_id int(11) DEFAULT NULL,
  rek_herdc_code int(11) DEFAULT NULL,
  PRIMARY KEY (rek_herdc_code_id),
  UNIQUE KEY rek_herdc_pid (rek_herdc_code_pid),
  KEY rek_herdc (rek_herdc_code)
) DEFAULT CHARSET=utf8;