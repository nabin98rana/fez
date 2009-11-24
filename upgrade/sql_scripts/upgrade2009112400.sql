CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%google_scholar_citations (
  `gs_id` int(11) NOT NULL auto_increment,
  `gs_pid` varchar(64) character set utf8 NOT NULL default '',
  `gs_last_checked` int(10) NOT NULL,
  `gs_count` int(10) NOT NULL,
  `gs_link` text character set utf8,
  `gs_created` int(11) NOT NULL,
  PRIMARY KEY  (`gs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%thomson_citations (
  `tc_id` int(11) NOT NULL auto_increment,
  `tc_pid` varchar(64) NOT NULL,
  `tc_last_checked` int(10) NOT NULL,
  `tc_count` int(10) NOT NULL,
  `tc_created` int(11) NOT NULL,
  PRIMARY KEY  (`tc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
