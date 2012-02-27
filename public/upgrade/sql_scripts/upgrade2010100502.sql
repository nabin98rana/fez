CREATE TABLE %TABLE_PREFIX%my_research_claimed_flagged ( 
	mrc_id int(11) NOT NULL auto_increment PRIMARY KEY,
	mrc_pid varchar(64) NOT NULL,
	mrc_author_username varchar(255) NOT NULL,
	mrc_timestamp datetime NOT NULL,
	mrc_correction text default NULL
);
