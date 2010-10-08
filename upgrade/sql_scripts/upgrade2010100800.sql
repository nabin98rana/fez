ALTER TABLE %TABLE_PREFIX%my_research_possible_flagged
	add column `mrp_type`  varchar(1) NOT NULL;

TRUNCATE %TABLE_PREFIX%my_research_claimed_flagged;
TRUNCATE %TABLE_PREFIX%my_research_possible_flagged;
