ALTER TABLE %TABLE_PREFIX%journal ADD COLUMN jnl_era_year INT(4) NULL AFTER jnl_era_id;
ALTER TABLE %TABLE_PREFIX%journal ADD COLUMN jnl_foreign_name VARCHAR(255) NULL AFTER jnl_rank;
ALTER TABLE %TABLE_PREFIX%conference ADD COLUMN cnf_era_year INT(4) NULL AFTER cnf_era_id;
ALTER TABLE %TABLE_PREFIX%conference ADD COLUMN cnf_foreign_name VARCHAR(255) NULL AFTER cnf_updated_date;

ALTER TABLE %TABLE_PREFIX%journal_issns
CHANGE jnl_journal_issn_id jni_id INT(11) NOT NULL AUTO_INCREMENT,
CHANGE jnl_journal_id jni_jnl_id INT(11) NOT NULL,
CHANGE jnl_issn jni_issn VARCHAR(50),
CHANGE jnl_issn_order jni_issn_order TINYINT(3) NULL;

ALTER TABLE %TABLE_PREFIX%journal_for_codes
CHANGE jne_era_id jne_jnl_id int(11) NOT NULL;

ALTER TABLE %TABLE_PREFIX%journal
CHANGE jnl_rank jnl_rank VARCHAR(2) NULL,
CHANGE jnl_journal_id jnl_id INT(11) NOT NULL AUTO_INCREMENT,
CHANGE jnl_era_id jnl_era_id INT(11) NOT NULL;

UPDATE %TABLE_PREFIX%journal
SET jnl_era_year = 2010
WHERE jnl_era_year IS NULL;

UPDATE %TABLE_PREFIX%conference
SET cnf_era_year = 2010
WHERE cnf_era_year IS NULL;

UPDATE %TABLE_PREFIX%journal_for_codes, %TABLE_PREFIX%journal
SET jne_jnl_id = jnl_id
WHERE jne_jnl_id = jnl_era_id;

UPDATE %TABLE_PREFIX%journal_issns, %TABLE_PREFIX%journal
SET jni_jnl_id = jnl_id
WHERE jni_jnl_id = jnl_era_id;

ALTER TABLE %TABLE_PREFIX%conference
CHANGE cnf_conference_id cnf_id INT(11) NOT NULL AUTO_INCREMENT,
CHANGE cnf_era_id cnf_era_id INT(11) NOT NULL;

ALTER TABLE %TABLE_PREFIX%conference_for_codes
CHANGE cfe_era_id cfe_cnf_id int(11) NOT NULL;

UPDATE %TABLE_PREFIX%conference_for_codes, %TABLE_PREFIX%conference
SET cfe_cnf_id = cnf_id
WHERE cfe_cnf_id = cnf_era_id;

ALTER TABLE %TABLE_PREFIX%matched_journals
CHANGE mtj_eraid mtj_jnl_id int(11) NOT NULL;

UPDATE %TABLE_PREFIX%matched_journals, %TABLE_PREFIX%journal
SET mtj_jnl_id = jnl_id
WHERE mtj_jnl_id = jnl_era_id;

ALTER TABLE %TABLE_PREFIX%matched_conferences
CHANGE mtc_eraid mtc_cnf_id int(11) NOT NULL;

UPDATE %TABLE_PREFIX%matched_conferences, %TABLE_PREFIX%conference
SET mtc_cnf_id = cnf_id
WHERE mtc_cnf_id = cnf_era_id;

ALTER TABLE %TABLE_PREFIX%matched_journals     CHANGE mtj_jnl_id mtj_jnl_id INT(11) NOT NULL,    DROP PRIMARY KEY,     ADD PRIMARY KEY(mtj_pid, mtj_jnl_id);
ALTER TABLE %TABLE_PREFIX%matched_conferences  CHANGE mtc_cnf_id mtc_cnf_id INT(11) NOT NULL,    DROP PRIMARY KEY,     ADD PRIMARY KEY(mtc_pid, mtc_cnf_id);