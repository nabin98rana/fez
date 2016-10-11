ALTER TABLE %TABLE_PREFIX%datastream_info ADD COLUMN dsi_size INT(11) DEFAULT NULL;
ALTER TABLE %TABLE_PREFIX%datastream_info ADD COLUMN dsi_version VARCHAR(50) DEFAULT NULL;
ALTER TABLE %TABLE_PREFIX%datastream_info ADD COLUMN dsi_checksum VARCHAR(255) DEFAULT NULL;
ALTER TABLE %TABLE_PREFIX%datastream_info ADD COLUMN dsi_open_access TINYINT(1) NULL DEFAULT NULL;

CREATE TABLE %TABLE_PREFIX%datastream_info__shadow (
	dsi_id INT(11) NOT NULL,
	dsi_pid VARCHAR(255) NOT NULL DEFAULT '',
	dsi_dsid VARCHAR(255) NOT NULL DEFAULT '',
	dsi_permissions VARCHAR(255) NULL DEFAULT NULL,
	dsi_embargo_date DATE NULL DEFAULT NULL,
	dsi_embargo_processed INT(11) NULL DEFAULT '0',
	dsi_mimetype VARCHAR(128) NULL DEFAULT NULL,
	dsi_url TEXT NULL,
	dsi_copyright CHAR(1) NULL DEFAULT NULL,
	dsi_watermark CHAR(1) NULL DEFAULT NULL,
	dsi_security_inherited CHAR(1) NULL DEFAULT NULL,
	dsi_state CHAR(1) NULL DEFAULT NULL,
	dsi_size INT(11) NULL DEFAULT NULL,
	dsi_version VARCHAR(50) NULL DEFAULT NULL,
	dsi_checksum VARCHAR(255) NULL DEFAULT NULL,
	dsi_open_access TINYINT(1) NULL DEFAULT NULL,
	dsi_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (dsi_pid, dsi_dsid, dsi_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
