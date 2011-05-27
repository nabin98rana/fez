CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%favourites (
  fvt_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  fvt_pid varchar(64) NOT NULL,
  fvt_username varchar(64) NOT NULL,
  PRIMARY KEY (fvt_id)
);
