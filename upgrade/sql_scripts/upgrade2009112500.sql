CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%scopus_citations (
  `sc_id` int(11) NOT NULL auto_increment,
  `sc_pid` varchar(64) NOT NULL,
  `sc_last_checked` int(10) NOT NULL,
  `sc_count` int(10) NOT NULL,
  `sc_created` int(11) NOT NULL,
  PRIMARY KEY  (`sc_id`)
);
ALTER TABLE %TABLE_PREFIX%record_search_key ADD COLUMN `rek_scopus_citation_count` INT(11);