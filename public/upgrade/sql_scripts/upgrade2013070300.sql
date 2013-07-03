CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%datastream_info (
  dsi_id int(11) NOT NULL AUTO_INCREMENT,
  dsi_pid varchar(255) DEFAULT NULL,
  dsi_dsid varchar(255) DEFAULT NULL,
  dsi_permissions varchar(255) DEFAULT NULL,
  dsi_embargo_date date DEFAULT NULL,
  PRIMARY KEY (dsi_id),
  KEY `pid, dsid` (dsi_pid,dsi_dsid),
  KEY `embargo_date` (dsi_embargo_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;