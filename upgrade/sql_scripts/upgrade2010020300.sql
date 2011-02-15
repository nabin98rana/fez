CREATE TABLE %TABLE_PREFIX%journal
(
	jnl_journal_id int(11) not null AUTO_INCREMENT,
	jnl_journal_name varchar(255) not null,
	jnl_era_id varchar(10),
	jnl_created_date date default NULL,
	jnl_updated_date date default NULL,
	primary key (jnl_journal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE %TABLE_PREFIX%journal_issns
(
	jnl_journal_issn_id int(11) not null AUTO_INCREMENT,
	jnl_journal_id int(11) not null,
	jnl_issn varchar(50) not null,
	jnl_issn_order tinyint(3),
	primary key (jnl_journal_issn_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
