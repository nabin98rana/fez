CREATE TABLE %TABLE_PREFIX%author_identifier_user_grants (
  aig_id int(11) NOT NULL AUTO_INCREMENT,
  aig_author_id int(11) NOT NULL,
  aig_id_type int(11) DEFAULT NULL,
  aig_name varchar(255) NOT NULL,
  aig_status varchar(45) DEFAULT NULL,
  aig_expires int(10) unsigned DEFAULT NULL,
  aig_details text COMMENT '''a formatted string, refer to identifiers.details_format for type''',
  aig_created timestamp NULL DEFAULT NULL,
  aig_updated timestamp NULL DEFAULT NULL,
  aig_details_dump text,
  PRIMARY KEY (aig_id),
  UNIQUE KEY unique_constraint (aig_author_id,aig_id_type,aig_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%author_identifier_identifiers (
  ai_id int(11) NOT NULL AUTO_INCREMENT,
  ai_id_type int(11) DEFAULT NULL,
  ai_name varchar(45) NOT NULL,
  ai_short_form varchar(255) DEFAULT NULL,
  ai_description text,
  ai_url varchar(512) DEFAULT NULL,
  ai_image varchar(255) DEFAULT NULL,
  PRIMARY KEY (ai_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;