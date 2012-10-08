CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%fez_sherpa_romeo (
  srm_id int(11) NOT NULL AUTO_INCREMENT,
  srm_journal_name varchar(255) DEFAULT NULL,
  srm_xml mediumtext,
  srm_colour varchar(255) DEFAULT NULL,
  srm_issn varchar(255) DEFAULT NULL,
  srm_date_updated datetime DEFAULT NULL,
  PRIMARY KEY (srm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
