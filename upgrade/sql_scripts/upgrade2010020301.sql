CREATE TABLE %TABLE_PREFIX%conference
(
	`cnf_conference_id` int(11) not null AUTO_INCREMENT,
	`cnf_conference_name` varchar(255) not null,
	`cnf_acronym` varchar(50),
	`cnf_rank` varchar(1),
	`cnf_era_id` varchar(10),
	`cnf_created_date` date default NULL,
	`cnf_updated_date` date default NULL,
	primary key (cnf_conference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
