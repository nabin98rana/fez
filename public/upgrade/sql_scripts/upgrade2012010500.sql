CREATE TABLE %TABLE_PREFIX%favourites_search (
  fvs_id int(11) NOT NULL AUTO_INCREMENT,
  fvs_search_parameters varchar(2048) DEFAULT NULL,
  fvs_username varchar(64) DEFAULT NULL,
  fvs_email_me tinyint(4) DEFAULT NULL,
  fvs_most_recent_item_date varchar(100) DEFAULT NULL,
  fvs_alias varchar(100) DEFAULT NULL,
  fvs_description varchar(2048) DEFAULT NULL,
  fvs_unsubscribe_hash varchar(40) DEFAULT NULL,
  PRIMARY KEY (fvs_id)
)
