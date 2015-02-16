CREATE TABLE %TABLE_PREFIX%journal_uq_tiered (
  jnl_id int(11) NOT NULL AUTO_INCREMENT,
  jnl_journal_name varchar(255) NOT NULL,
  jnl_era_id int(11) NOT NULL,
  jnl_era_year varchar(10) DEFAULT NULL,
  jnl_created_date date DEFAULT NULL,
  jnl_updated_date date DEFAULT NULL,
  jnl_rank varchar(10) DEFAULT NULL,
  jnl_foreign_name varchar(255) DEFAULT NULL,
  PRIMARY KEY (jnl_id),
  KEY idx_jnl_era_id (jnl_era_id),
  KEY idx_journal_name (jnl_journal_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%journal_uq_tiered_issns (
  jni_id int(11) NOT NULL AUTO_INCREMENT,
  jni_jnl_id int(11) NOT NULL,
  jni_issn varchar(50) DEFAULT NULL,
  jni_issn_order tinyint(3) DEFAULT NULL,
  PRIMARY KEY (jni_id),
  KEY idx_jnl_journal_issn_id (jni_id),
  KEY idx_jnl_journal_id (jni_jnl_id),
  KEY jni_issn (jni_issn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%matched_uq_tiered_journals (
  mtj_pid varchar(64) NOT NULL DEFAULT '',
  mtj_jnl_id int(11) NOT NULL,
  mtj_status varchar(1) NOT NULL DEFAULT '',
  PRIMARY KEY (mtj_pid,mtj_jnl_id),
  KEY idx_mtj_eraid (mtj_jnl_id),
  KEY mtj_pid (mtj_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
