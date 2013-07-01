CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%altmetric (
  as_id int(11) NOT NULL auto_increment,
  as_amid int(11) NOT NULL,
  as_doi varchar(255) NOT NULL,
  as_score int(11) NOT NULL,
  as_created int(11) NOT NULL,
  as_last_checked int(11) NOT NULL,
  PRIMARY KEY  (as_id),
  KEY as_amid (as_amid),
  KEY as_doi (as_doi)
);