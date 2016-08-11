CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%file_attachments (
  fat_did int(11) NOT NULL AUTO_INCREMENT,
  fat_filename varchar(200) NOT NULL,
  fat_pid varchar(15) NOT NULL DEFAULT '0',
  fat_mimetype varchar(100) DEFAULT NULL,
  fat_url text,
  fat_copyright char(1) DEFAULT NULL,
  fat_watermark char(1) DEFAULT NULL,
  fat_security_inherited char(1) DEFAULT NULL,
  PRIMARY KEY (fat_did),
  KEY unique_pid_filename (fat_pid, fat_filename)
);
