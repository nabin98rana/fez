CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%conference_for_codes (
	cfe_id int(11) unsigned NOT NULL AUTO_INCREMENT,
	cfe_era_id varchar(6) NOT NULL,
	cfe_for_code varchar(6) NOT NULL,
	cfe_number int(11) NOT NULL,
	PRIMARY KEY (cfe_id)
);
