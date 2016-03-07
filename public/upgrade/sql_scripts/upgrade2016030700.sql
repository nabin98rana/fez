CREATE TABLE fez_publons_journals (
  psj_id int(11) NOT NULL AUTO_INCREMENT,
  psj_journal_id int(11) DEFAULT NULL,
  psj_journal_name varchar(255) DEFAULT NULL,
  psj_journal_issn varchar(32) DEFAULT NULL,
  psj_journal_eissn varchar(32) DEFAULT NULL,
  psj_journal_tier varchar(10) DEFAULT NULL,
  PRIMARY KEY (psj_id),
  UNIQUE KEY psj_journal_id (psj_journal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE fez_publons_publishers (
  psp_id int(11) NOT NULL AUTO_INCREMENT,
  psp_publisher_id int(11) DEFAULT NULL,
  psp_publisher_name varchar(512) DEFAULT NULL,
  PRIMARY KEY (psp_id),
  UNIQUE KEY psp_publisher_id (psp_publisher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE fez_publons_reviews (
  psr_id int(11) NOT NULL AUTO_INCREMENT,
  psr_aut_id int(11) DEFAULT NULL,
  psr_publons_id varchar(64) DEFAULT NULL,
  psr_date_reviewed varchar(32) DEFAULT NULL,
  psr_verified tinyint(1) DEFAULT NULL,
  psr_publisher_id int(11) DEFAULT NULL,
  psr_journal_id int(11) DEFAULT NULL,
  psr_journal_article varchar(4047) DEFAULT NULL,
  psr_update_date datetime DEFAULT NULL,
  PRIMARY KEY (psr_id),
  UNIQUE KEY psr_author_id,psr_publon_id (psr_aut_id,psr_publons_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
