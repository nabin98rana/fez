CREATE TABLE  %TABLE_PREFIX%thomson_doctype_mappings (
  tdm_id int(11) unsigned NOT NULL AUTO_INCREMENT,
  tdm_xdis_id int(11) unsigned NOT NULL,
  tdm_doctype varchar(5) NOT NULL,
  tdm_service varchar(45) NOT NULL,
  tdm_subtype varchar(255) DEFAULT NULL,
  PRIMARY KEY (tdm_id)
);