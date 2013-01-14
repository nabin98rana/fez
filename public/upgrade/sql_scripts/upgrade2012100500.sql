CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%sherpa_romeo (
  srm_id int(11) NOT NULL AUTO_INCREMENT,
  srm_issn varchar(255) DEFAULT NULL,
  srm_journal_name varchar(255) DEFAULT NULL,
  srm_xml mediumtext,
  srm_colour varchar(255) DEFAULT NULL,
  srm_date_updated datetime DEFAULT NULL,
  PRIMARY KEY (srm_id),
  UNIQUE KEY `Unique` (`srm_issn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
