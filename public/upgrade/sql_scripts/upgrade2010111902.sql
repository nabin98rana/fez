CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%journal_for_codes (
	jne_id int(11) unsigned NOT NULL AUTO_INCREMENT,
	jne_era_id varchar(6) NOT NULL,
	jne_for_code varchar(6) NOT NULL,
	jne_number int(11) NOT NULL,
	PRIMARY KEY (jne_id)
);
