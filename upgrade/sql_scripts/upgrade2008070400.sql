CREATE TABLE %TABLE_PREFIX%sessions (
  `session_id` varchar(100) NOT NULL default '',
  `session_data` text NOT NULL,
  `expires` int(11) NOT NULL default '0',
  PRIMARY KEY  (`session_id`)
);
