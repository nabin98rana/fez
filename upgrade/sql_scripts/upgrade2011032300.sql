CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%linksamr_locks (
  lnl_name varchar(8) NOT NULL,
  lnl_value int(10) unsigned NOT NULL,
  lnl_pid int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (lnl_name)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%linksamr_queue (
  lnq_key int(10) unsigned NOT NULL AUTO_INCREMENT,
  lnq_id varchar(128) NOT NULL DEFAULT '',
  lnq_op varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (lnq_key),
  UNIQUE KEY id_op (lnq_id,lnq_op)
);