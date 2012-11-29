CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%file_attachments (
  fat_did int(11) NOT NULL AUTO_INCREMENT,
  fat_hash varchar(50) NOT NULL,
  fat_filename varchar(200) NOT NULL,
  fat_label varchar(200) DEFAULT NULL,
  fat_version timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fat_metaid int(11) NOT NULL,
  fat_state enum('A','D') NOT NULL DEFAULT 'A',
  fat_size int(20) NOT NULL DEFAULT '0',
  fat_pid varchar(15) NOT NULL DEFAULT '0',
  fat_mimetype varchar(100) DEFAULT NULL,
  fat_controlgroup char(1) NOT NULL DEFAULT 'M',
  fat_xdis_id int(11) DEFAULT '5',
  fat_copyright char(1) DEFAULT NULL,
  fat_watermark char(1) DEFAULT NULL,
  fat_security_inherited char(1) DEFAULT NULL,
  PRIMARY KEY (fat_did),
  KEY 'unique_pid_hash_filename' ('fat_hash','fat_pid', 'fat_filename', 'fat_version')
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%file_attachments_shadow (
  fat_did int(11) NOT NULL AUTO_INCREMENT,
  fat_hash varchar(50) NOT NULL,
  fat_filename varchar(200) NOT NULL,
  fat_label varchar(200) DEFAULT NULL,
  fat_version timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fat_metaid int(11) NOT NULL,
  fat_state enum('A','D') NOT NULL DEFAULT 'A',
  fat_size int(20) NOT NULL DEFAULT '0',
  fat_pid varchar(15) NOT NULL DEFAULT '0',
  fat_mimetype varchar(100) DEFAULT NULL,
  fat_controlgroup char(1) NOT NULL DEFAULT 'M',
  fat_xdis_id int(11) DEFAULT '5',
  fat_copyright char(1) DEFAULT NULL,
  fat_watermark char(1) DEFAULT NULL,
  fat_security_inherited char(1) DEFAULT NULL,
  fat_stamp datetime DEFAULT NULL,
  PRIMARY KEY (fat_did)
);