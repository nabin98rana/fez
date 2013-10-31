CREATE TABLE %TABLE_PREFIX%doi_created (
  dcr_id int(11) NOT NULL AUTO_INCREMENT,
  dcr_pid varchar(255) DEFAULT NULL,
  dcr_doi_year int(11) DEFAULT NULL,
  dcr_doi_num int(11) DEFAULT NULL,
  dcr_creator varchar(255) DEFAULT NULL,
  dcr_date datetime DEFAULT NULL,
  PRIMARY KEY (dcr_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
