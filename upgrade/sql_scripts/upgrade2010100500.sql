CREATE TABLE %TABLE_PREFIX%my_research_possible_flagged ( 
	mrp_id int(11) NOT NULL auto_increment PRIMARY KEY,
	mrp_pid varchar(64) NOT NULL,
	mrp_author_username varchar(255) NOT NULL,
	mrp_timestamp datetime NOT NULL,
	mrp_correction text default NULL
);
