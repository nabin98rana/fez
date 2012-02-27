CREATE TABLE %TABLE_PREFIX%near_matched_journals (
  nmj_id int(11) NOT NULL AUTO_INCREMENT,
  nmj_pid varchar(64) NOT NULL,
  nmj_jnl_id int(11) NOT NULL,
  nmj_jnl_journal_name varchar(255) NOT NULL,
  nmj_rek_journal_name varchar(255) NOT NULL,
  nmj_similarity DECIMAL(13,2) NOT NULL,
  nmj_created_date datetime NOT NULL,
  PRIMARY KEY (nmj_id),
  KEY nmj_pid (nmj_pid),
  KEY nmj_similarity (nmj_similarity),
  KEY nmj_jnl_id (nmj_jnl_id)
);
