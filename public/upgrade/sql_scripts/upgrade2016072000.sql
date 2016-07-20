CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%_reports (
  sel_id int(11) NOT NULL AUTO_INCREMENT,
  sel_title varchar(128) DEFAULT NULL,
  sel_query text,
  sel_description text,
  PRIMARY KEY (sel_id)
);