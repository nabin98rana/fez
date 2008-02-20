DROP TABLE IF EXISTS  %TABLE_PREFIX%main_chapter;
CREATE TABLE %TABLE_PREFIX%main_chapter (
	mc_id int(10) unsigned NOT NULL auto_increment,
	mc_pid varchar(32) NOT NULL,
	mc_author_id int(11) NOT NULL,
	mc_status int(1) DEFAULT 0,
	PRIMARY KEY (mc_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
