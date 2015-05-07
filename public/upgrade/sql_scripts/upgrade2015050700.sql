CREATE TABLE %TABLE_PREFIX%thomson_citations_cache (
  tc_id int(11) NOT NULL AUTO_INCREMENT,
  tc_pid varchar(64) NOT NULL DEFAULT '',
  tc_last_checked int(10) NOT NULL,
  tc_count int(10) NOT NULL,
  tc_created int(11) NOT NULL,
  tc_isi_loc varchar(255) DEFAULT NULL,
  tc_diff_previous int(10) DEFAULT NULL,
  PRIMARY KEY (tc_id),
  UNIQUE KEY tc_isi_loc (tc_isi_loc),
  KEY tc_created (tc_created)
);

CREATE TABLE %TABLE_PREFIX%scopus_citations_cache (
  sc_id int(11) NOT NULL AUTO_INCREMENT,
  sc_pid varchar(64) NOT NULL DEFAULT '',
  sc_last_checked int(10) NOT NULL,
  sc_count int(10) NOT NULL,
  sc_created int(11) NOT NULL,
  sc_eid varchar(255) DEFAULT NULL,
  sc_diff_previous int(10) DEFAULT NULL,
  PRIMARY KEY (sc_id),
  UNIQUE KEY sc_eid (sc_eid),
  KEY sc_pid_index (sc_pid),
  KEY sc_created (sc_created)
);
