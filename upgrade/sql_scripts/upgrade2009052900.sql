CREATE TABLE  %TABLE_PREFIX%fez_google_scholar_citations (
  gs_pid varchar(64) NOT NULL,
  gs_last_checked int(10) unsigned NOT NULL,
  gs_count int(10) unsigned NOT NULL,
  gs_link TEXT,
  gs_id int(11) unsigned NOT NULL auto_increment,
  gs_created int(11) unsigned NOT NULL,
  PRIMARY KEY(gs_id)
);
