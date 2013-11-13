CREATE TABLE %TABLE_PREFIX%scopus_import_stats (
  scs_pid varchar(64) DEFAULT NULL,
  scs_contrib_id varchar(255) DEFAULT NULL,
  scs_operation varchar(8) DEFAULT NULL,
  scs_count int(11) DEFAULT NULL,
  scs_doc_type varchar(100) DEFAULT NULL,
  scs_ag_type varchar(100) DEFAULT NULL,
  scs_title TEXT DEFAULT NULL,
  PRIMARY KEY (scs_contrib_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
