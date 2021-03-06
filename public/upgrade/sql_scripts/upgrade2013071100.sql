CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%scopus_queue (
  spq_key int(10) unsigned NOT NULL AUTO_INCREMENT,
  spq_id varchar(128) NOT NULL DEFAULT '',
  spq_op varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (spq_key),
  UNIQUE KEY id_op (spq_id,spq_op)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%scopus_session (
  scs_id int(11) NOT NULL AUTO_INCREMENT,
  scs_tok varchar(400) NOT NULL,
  scs_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (scs_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%scopus_locks (
  scl_name varchar(8) NOT NULL,
  scl_value int(10) NOT NULL,
  scl_pid int(10) DEFAULT NULL,
  PRIMARY KEY (scl_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
