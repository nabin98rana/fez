CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_author_affiliation_id` (
  `rek_author_affiliation_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_affiliation_id_pid` varchar(64) NOT NULL,
  `rek_author_affiliation_id_xsdmf_id` int(11) NOT NULL,
  `rek_author_affiliation_id_order` int(11) DEFAULT '1',
  `rek_author_affiliation_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_author_affiliation_id_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_author_affiliation_id_pid`,`rek_author_affiliation_id_order`),
  KEY `rek_author_affiliation_id` (`rek_author_affiliation_id`),
  KEY `rek_author_affiliation_id_pid` (`rek_author_affiliation_id_pid`),
  CONSTRAINT `rek_affid_foreign` FOREIGN KEY (`rek_author_affiliation_id_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_author_affiliation_id__shadow` (
  `rek_author_affiliation_id_id` int(11) NOT NULL,
  `rek_author_affiliation_id_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_author_affiliation_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author_affiliation_id` varchar(255) DEFAULT NULL,
  `rek_author_affiliation_id_order` int(11) NOT NULL DEFAULT '1',
  `rek_author_affiliation_id_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_author_affiliation_id_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_author_affiliation_id_version`,`rek_author_affiliation_id_order`),
  KEY `rek_author_affiliation_id_pid` (`rek_author_affiliation_id_pid`),
  KEY `rek_author_affiliation_id_pi` (`rek_author_affiliation_id_pid`,`rek_author_affiliation_id_stamp`,`rek_author_affiliation_id_order`),
  CONSTRAINT `rek_affid__foreign` FOREIGN KEY (`rek_author_affiliation_id_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_author_affiliation_name` (
  `rek_author_affiliation_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_affiliation_name_pid` varchar(64) NOT NULL,
  `rek_author_affiliation_name_xsdmf_id` int(11) NOT NULL,
  `rek_author_affiliation_name_order` int(11) DEFAULT '1',
  `rek_author_affiliation_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_author_affiliation_name_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_author_affiliation_name_pid`,`rek_author_affiliation_name_order`),
  KEY `rek_author_affiliation_name` (`rek_author_affiliation_name`),
  KEY `rek_author_affiliation_name_pid` (`rek_author_affiliation_name_pid`),
  CONSTRAINT `rek_affna_foreign` FOREIGN KEY (`rek_author_affiliation_name_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_author_affiliation_name__shadow` (
  `rek_author_affiliation_name_id` int(11) NOT NULL,
  `rek_author_affiliation_name_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_author_affiliation_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author_affiliation_name` varchar(255) DEFAULT NULL,
  `rek_author_affiliation_name_order` int(11) NOT NULL DEFAULT '1',
  `rek_author_affiliation_name_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_author_affiliation_name_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_author_affiliation_name_version`,`rek_author_affiliation_name_order`),
  KEY `rek_author_affiliation_name_pid` (`rek_author_affiliation_name_pid`),
  KEY `rek_author_affiliation_name_pi` (`rek_author_affiliation_name_pid`,`rek_author_affiliation_name_stamp`,`rek_author_affiliation_name_order`),
  CONSTRAINT `rek_affna__foreign` FOREIGN KEY (`rek_author_affiliation_name_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_author_affiliation_country` (
  `rek_author_affiliation_country_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_affiliation_country_pid` varchar(64) NOT NULL,
  `rek_author_affiliation_country_xsdmf_id` int(11) NOT NULL,
  `rek_author_affiliation_country_order` int(11) DEFAULT '1',
  `rek_author_affiliation_country` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_author_affiliation_country_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_author_affiliation_country_pid`,`rek_author_affiliation_country_order`),
  KEY `rek_author_affiliation_country` (`rek_author_affiliation_country`),
  KEY `rek_author_affiliation_country_pid` (`rek_author_affiliation_country_pid`),
  CONSTRAINT `rek_affac_foreign` FOREIGN KEY (`rek_author_affiliation_country_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_author_affiliation_country__shadow` (
  `rek_author_affiliation_country_id` int(11) NOT NULL,
  `rek_author_affiliation_country_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_author_affiliation_country_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author_affiliation_country` varchar(255) DEFAULT NULL,
  `rek_author_affiliation_country_order` int(11) NOT NULL DEFAULT '1',
  `rek_author_affiliation_country_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_author_affiliation_country_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_author_affiliation_country_version`,`rek_author_affiliation_country_order`),
  KEY `rek_author_affiliation_country_pid` (`rek_author_affiliation_country_pid`),
  KEY `rek_author_affiliation_country_pi` (`rek_author_affiliation_country_pid`,`rek_author_affiliation_country_stamp`,`rek_author_affiliation_country_order`),
  CONSTRAINT `rek_affac__foreign` FOREIGN KEY (`rek_author_affiliation_country_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_author_affiliation_full_address` (
  `rek_author_affiliation_full_address_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_affiliation_full_address_pid` varchar(64) NOT NULL,
  `rek_author_affiliation_full_address_xsdmf_id` int(11) NOT NULL,
  `rek_author_affiliation_full_address_order` int(11) DEFAULT '1',
  `rek_author_affiliation_full_address` text,
  PRIMARY KEY (`rek_author_affiliation_full_address_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_author_affiliation_full_address_pid`,`rek_author_affiliation_full_address_order`),
  FULLTEXT KEY `rek_author_affiliation_full_address` (`rek_author_affiliation_full_address`),
  KEY `rek_author_affiliation_full_address_pid` (`rek_author_affiliation_full_address_pid`),
  CONSTRAINT `rek_afffa_foreign` FOREIGN KEY (`rek_author_affiliation_full_address_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_author_affiliation_full_address__shadow` (
  `rek_author_affiliation_full_address_id` int(11) NOT NULL,
  `rek_author_affiliation_full_address_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_author_affiliation_full_address_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author_affiliation_full_address` text DEFAULT NULL,
  `rek_author_affiliation_full_address_order` int(11) NOT NULL DEFAULT '1',
  `rek_author_affiliation_full_address_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_author_affiliation_full_address_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_author_affiliation_full_address_version`,`rek_author_affiliation_full_address_order`),
  KEY `rek_author_affiliation_full_address_pid` (`rek_author_affiliation_full_address_pid`),
  KEY `rek_author_affiliation_full_address_pi` (`rek_author_affiliation_full_address_pid`,`rek_author_affiliation_full_address_stamp`,`rek_author_affiliation_full_address_order`),
  CONSTRAINT `rek_afffa__foreign` FOREIGN KEY (`rek_author_affiliation_full_address_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
