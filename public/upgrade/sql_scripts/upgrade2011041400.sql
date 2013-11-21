CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%wok_locks (
  wkl_name varchar(8) NOT NULL,
  wkl_value int(10) NOT NULL,
  wkl_pid int(10) DEFAULT NULL,
  PRIMARY KEY (wkl_name)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%wok_queue (
  wkq_key int(10) unsigned NOT NULL AUTO_INCREMENT,
  wkq_id varchar(128) NOT NULL DEFAULT '',
  wkq_op varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (wkq_key),
  UNIQUE KEY id_op (wkq_id,wkq_op)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%wok_queue_aut (
  wka_key int(10) unsigned NOT NULL AUTO_INCREMENT,
  wka_id varchar(128) NOT NULL DEFAULT '',
  wka_aut_id varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (wka_key),
  UNIQUE KEY id_op (wka_id,wka_aut_id)
);