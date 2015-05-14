CREATE TABLE %TABLE_PREFIX%import_value_to_pid (
  imp_id int(11) NOT NULL AUTO_INCREMENT,
  imp_pid varchar(255) DEFAULT NULL,
  imp_value varchar(255) DEFAULT NULL,
  imp_key varchar(255) DEFAULT NULL,
  imp_history varchar(255) DEFAULT NULL,
  PRIMARY KEY (imp_id)
) COMMENT='This table is used with a misc script to update pids.'