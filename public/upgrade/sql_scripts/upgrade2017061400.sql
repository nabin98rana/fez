CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_article_number` (
  `rek_article_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_article_number_pid` varchar(64) DEFAULT NULL,
  `rek_article_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_article_number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_article_number_id`),
  UNIQUE KEY `unique_constraint` (`rek_article_number_pid`,`rek_article_number`),
  UNIQUE KEY `rek_article_number_pid` (`rek_article_number_pid`),
  KEY `rek_article_number` (`rek_article_number`),
  CONSTRAINT `rek_artnu_foreign` FOREIGN KEY (`rek_article_number_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_article_number__shadow` (
  `rek_article_number_id` int(11) NOT NULL,
  `rek_article_number_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_article_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_article_number` varchar(255) DEFAULT NULL,
  `rek_article_number_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_article_number_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_article_number_version`),
  KEY `rek_article_number` (`rek_article_number`),
  KEY `rek_article_number_pi` (`rek_article_number_pid`,`rek_article_number_stamp`),
  CONSTRAINT `rek_artnu__foreign` FOREIGN KEY (`rek_article_number_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_grant_agency` (
  `rek_grant_agency_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_grant_agency_pid` varchar(64) NOT NULL,
  `rek_grant_agency_xsdmf_id` int(11) NOT NULL,
  `rek_grant_agency_order` int(11) DEFAULT '1',
  `rek_grant_agency` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_grant_agency_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_grant_agency_pid`,`rek_grant_agency_order`),
  KEY `rek_grant_agency` (`rek_grant_agency`),
  KEY `rek_grant_agency_pid` (`rek_grant_agency_pid`),
  CONSTRAINT `rek_graag_foreign` FOREIGN KEY (`rek_grant_agency_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_grant_agency__shadow` (
  `rek_grant_agency_id` int(11) NOT NULL,
  `rek_grant_agency_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_grant_agency_xsdmf_id` int(11) DEFAULT NULL,
  `rek_grant_agency` varchar(255) DEFAULT NULL,
  `rek_grant_agency_order` int(11) NOT NULL DEFAULT '1',
  `rek_grant_agency_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_grant_agency_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_grant_agency_version`,`rek_grant_agency_order`),
  KEY `rek_grant_agency_pid` (`rek_grant_agency_pid`),
  KEY `rek_grant_agency_pi` (`rek_grant_agency_pid`,`rek_grant_agency_stamp`,`rek_grant_agency_order`),
  CONSTRAINT `rek_graag__foreign` FOREIGN KEY (`rek_grant_agency_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_grant_agency_id` (
  `rek_grant_agency_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_grant_agency_id_pid` varchar(64) NOT NULL,
  `rek_grant_agency_id_xsdmf_id` int(11) NOT NULL,
  `rek_grant_agency_id_order` int(11) DEFAULT '1',
  `rek_grant_agency_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_grant_agency_id_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_grant_agency_id_pid`,`rek_grant_agency_id_order`),
  KEY `rek_grant_agency_id` (`rek_grant_agency_id`),
  KEY `rek_grant_agency_id_pid` (`rek_grant_agency_id_pid`),
  CONSTRAINT `rek_graac_foreign` FOREIGN KEY (`rek_grant_agency_id_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_grant_agency_id__shadow` (
  `rek_grant_agency_id_id` int(11) NOT NULL,
  `rek_grant_agency_id_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_grant_agency_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_grant_agency_id` varchar(255) DEFAULT NULL,
  `rek_grant_agency_id_order` int(11) NOT NULL DEFAULT '1',
  `rek_grant_agency_id_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_grant_agency_id_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_grant_agency_id_version`,`rek_grant_agency_id_order`),
  KEY `rek_grant_agency_id_pid` (`rek_grant_agency_id_pid`),
  KEY `rek_grant_agency_id_pi` (`rek_grant_agency_id_pid`,`rek_grant_agency_id_stamp`,`rek_grant_agency_id_order`),
  CONSTRAINT `rek_graac__foreign` FOREIGN KEY (`rek_grant_agency_id_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_grant_text` (
  `rek_grant_text_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_grant_text_pid` varchar(64) NOT NULL,
  `rek_grant_text_xsdmf_id` int(11) NOT NULL,
  `rek_grant_text_order` int(11) DEFAULT '1',
  `rek_grant_text` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_grant_text_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_grant_text_pid`,`rek_grant_text_order`),
  KEY `rek_grant_text` (`rek_grant_text`),
  KEY `rek_grant_text_pid` (`rek_grant_text_pid`),
  CONSTRAINT `rek_grate_foreign` FOREIGN KEY (`rek_grant_text_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_grant_text__shadow` (
  `rek_grant_text_id` int(11) NOT NULL,
  `rek_grant_text_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_grant_text_xsdmf_id` int(11) DEFAULT NULL,
  `rek_grant_text` varchar(255) DEFAULT NULL,
  `rek_grant_text_order` int(11) NOT NULL DEFAULT '1',
  `rek_grant_text_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_grant_text_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_grant_text_version`,`rek_grant_text_order`),
  KEY `rek_grant_text_pid` (`rek_grant_text_pid`),
  KEY `rek_grant_text_pi` (`rek_grant_text_pid`,`rek_grant_text_stamp`,`rek_grant_text_order`),
  CONSTRAINT `rek_grate__foreign` FOREIGN KEY (`rek_grant_text_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_grant_acronym` (
  `rek_grant_acronym_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_grant_acronym_pid` varchar(64) NOT NULL,
  `rek_grant_acronym_xsdmf_id` int(11) NOT NULL,
  `rek_grant_acronym_order` int(11) DEFAULT '1',
  `rek_grant_acronym` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_grant_acronym_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_grant_acronym_pid`,`rek_grant_acronym_order`),
  KEY `rek_grant_acronym` (`rek_grant_acronym`),
  KEY `rek_grant_acronym_pid` (`rek_grant_acronym_pid`),
  CONSTRAINT `rek_graar_foreign` FOREIGN KEY (`rek_grant_acronym_pid`) REFERENCES `fez_record_search_key` (`rek_pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_grant_acronym__shadow` (
  `rek_grant_acronym_id` int(11) NOT NULL,
  `rek_grant_acronym_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_grant_acronym_xsdmf_id` int(11) DEFAULT NULL,
  `rek_grant_acronym` varchar(255) DEFAULT NULL,
  `rek_grant_acronym_order` int(11) NOT NULL DEFAULT '1',
  `rek_grant_acronym_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rek_grant_acronym_version` varchar(100) NOT NULL,
  PRIMARY KEY (`rek_grant_acronym_version`,`rek_grant_acronym_order`),
  KEY `rek_grant_acronym_pid` (`rek_grant_acronym_pid`),
  KEY `rek_grant_acronym_pi` (`rek_grant_acronym_pid`,`rek_grant_acronym_stamp`,`rek_grant_acronym_order`),
  CONSTRAINT `rek_graar__foreign` FOREIGN KEY (`rek_grant_acronym_version`) REFERENCES `fez_record_search_key__shadow` (`rek_version`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
