CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_corresponding_email` (
  `rek_corresponding_email_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_corresponding_email_pid` varchar(64) NOT NULL,
  `rek_corresponding_email_xsdmf_id` int(11) NOT NULL,
  `rek_corresponding_email_order` int(11) DEFAULT '1',
  `rek_corresponding_email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_corresponding_email_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_corresponding_email_pid`,`rek_corresponding_email_order`),
  KEY `rek_corresponding_email` (`rek_corresponding_email`),
  KEY `rek_corresponding_email_pid` (`rek_corresponding_email_pid`),
  CONSTRAINT `rek_correm_foreign` FOREIGN KEY (`rek_corresponding_email_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_corresponding_email__shadow` (
  `rek_corresponding_email_id` int(11) NOT NULL,
  `rek_corresponding_email_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_corresponding_email_xsdmf_id` int(11) DEFAULT NULL,
  `rek_corresponding_email` varchar(255) DEFAULT NULL,
  `rek_corresponding_email_order` int(11) NOT NULL DEFAULT '1',
  `rek_corresponding_email_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_corresponding_email_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_corresponding_email_version`,`rek_corresponding_email_order`),
  KEY `rek_corresponding_email_pid` (`rek_corresponding_email_pid`),
  KEY `rek_corresponding_email_pi` (`rek_corresponding_email_pid`,`rek_corresponding_email_stamp`,`rek_corresponding_email_order`),
  CONSTRAINT `rek_correm__foreign` FOREIGN KEY (`rek_corresponding_email_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_corresponding_name` (
  `rek_corresponding_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_corresponding_name_pid` varchar(64) NOT NULL,
  `rek_corresponding_name_xsdmf_id` int(11) NOT NULL,
  `rek_corresponding_name_order` int(11) DEFAULT '1',
  `rek_corresponding_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_corresponding_name_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_corresponding_name_pid`,`rek_corresponding_name_order`),
  KEY `rek_corresponding_name` (`rek_corresponding_name`),
  KEY `rek_corresponding_name_pid` (`rek_corresponding_name_pid`),
  CONSTRAINT `rek_corrna_foreign` FOREIGN KEY (`rek_corresponding_name_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_corresponding_name__shadow` (
  `rek_corresponding_name_id` int(11) NOT NULL,
  `rek_corresponding_name_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_corresponding_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_corresponding_name` varchar(255) DEFAULT NULL,
  `rek_corresponding_name_order` int(11) NOT NULL DEFAULT '1',
  `rek_corresponding_name_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_corresponding_name_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_corresponding_name_version`,`rek_corresponding_name_order`),
  KEY `rek_corresponding_name_pid` (`rek_corresponding_name_pid`),
  KEY `rek_corresponding_name_pi` (`rek_corresponding_name_pid`,`rek_corresponding_name_stamp`,`rek_corresponding_name_order`),
  CONSTRAINT `rek_corrna__foreign` FOREIGN KEY (`rek_corresponding_name_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_corresponding_country` (
  `rek_corresponding_country_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_corresponding_country_pid` varchar(64) NOT NULL,
  `rek_corresponding_country_xsdmf_id` int(11) NOT NULL,
  `rek_corresponding_country_order` int(11) DEFAULT '1',
  `rek_corresponding_country` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_corresponding_country_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_corresponding_country_pid`,`rek_corresponding_country_order`),
  KEY `rek_corresponding_country` (`rek_corresponding_country`),
  KEY `rek_corresponding_country_pid` (`rek_corresponding_country_pid`),
  CONSTRAINT `rek_corrca_foreign` FOREIGN KEY (`rek_corresponding_country_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_corresponding_country__shadow` (
  `rek_corresponding_country_id` int(11) NOT NULL,
  `rek_corresponding_country_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_corresponding_country_xsdmf_id` int(11) DEFAULT NULL,
  `rek_corresponding_country` varchar(255) DEFAULT NULL,
  `rek_corresponding_country_order` int(11) NOT NULL DEFAULT '1',
  `rek_corresponding_country_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_corresponding_country_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_corresponding_country_version`,`rek_corresponding_country_order`),
  KEY `rek_corresponding_country_pid` (`rek_corresponding_country_pid`),
  KEY `rek_corresponding_country_pi` (`rek_corresponding_country_pid`,`rek_corresponding_country_stamp`,`rek_corresponding_country_order`),
  CONSTRAINT `rek_corrca__foreign` FOREIGN KEY (`rek_corresponding_country_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_corresponding_organisation` (
  `rek_corresponding_organisation_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_corresponding_organisation_pid` varchar(64) NOT NULL,
  `rek_corresponding_organisation_xsdmf_id` int(11) NOT NULL,
  `rek_corresponding_organisation_order` int(11) DEFAULT '1',
  `rek_corresponding_organisation` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_corresponding_organisation_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_corresponding_organisation_pid`,`rek_corresponding_organisation_order`),
  KEY `rek_corresponding_organisation` (`rek_corresponding_organisation`),
  KEY `rek_corresponding_organisation_pid` (`rek_corresponding_organisation_pid`),
  CONSTRAINT `rek_corror_foreign` FOREIGN KEY (`rek_corresponding_organisation_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_corresponding_organisation__shadow` (
  `rek_corresponding_organisation_id` int(11) NOT NULL,
  `rek_corresponding_organisation_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_corresponding_organisation_xsdmf_id` int(11) DEFAULT NULL,
  `rek_corresponding_organisation` varchar(255) DEFAULT NULL,
  `rek_corresponding_organisation_order` int(11) NOT NULL DEFAULT '1',
  `rek_corresponding_organisation_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_corresponding_organisation_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_corresponding_organisation_version`,`rek_corresponding_organisation_order`),
  KEY `rek_corresponding_organisation_pid` (`rek_corresponding_organisation_pid`),
  KEY `rek_corresponding_organisation_pi` (`rek_corresponding_organisation_pid`,`rek_corresponding_organisation_stamp`,`rek_corresponding_organisation_order`),
  CONSTRAINT `rek_corror__foreign` FOREIGN KEY (`rek_corresponding_organisation_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
