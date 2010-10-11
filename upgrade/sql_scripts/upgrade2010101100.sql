ALTER TABLE %TABLE_PREFIX%my_research_claimed_flagged
	add column `mrc_user_username`  varchar(255) NOT NULL;

ALTER TABLE %TABLE_PREFIX%my_research_possible_flagged
	add column `mrp_user_username`  varchar(255) NOT NULL;

TRUNCATE %TABLE_PREFIX%my_research_claimed_flagged;
TRUNCATE %TABLE_PREFIX%my_research_possible_flagged;
