CREATE TABLE %TABLE_PREFIX%auth_index2_lister (
  authi_pid varchar(64) character set utf8 collate utf8_bin NOT NULL,
  authi_arg_id int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (authi_pid,authi_arg_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
