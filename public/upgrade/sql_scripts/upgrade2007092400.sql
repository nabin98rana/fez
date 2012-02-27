REPLACE INTO %TABLE_PREFIX%search_key VALUES 
(39,'Edition',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(40,'Place of Publication',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(41,'Start Page',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(42,'End Page',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(43,'Chapter Number',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(44,'Issue Number',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(45,'Volume Number',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(46,'Conference Dates',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(47,'Conference Location',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(48,'Patent Number',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(49,'Country of Issue',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(50,'Date Available',NULL,0,0,0,0,'date','none','',450005,NULL,'date',1,''), 
(51,'Language',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(52,'Anglicised Title',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(53,'Language of Title',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(54,'English Title',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),  
(55,'Anglicised Publisher',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''), 
(56,'English Publisher',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,'');

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_edition (
  rek_edition_id int(11) NOT NULL auto_increment,
  rek_edition_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_edition_xsdmf_id int(11) default NULL,
  rek_edition varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_edition_id),
  KEY rek_edition (rek_edition),
  KEY rek_edition_pid (rek_edition_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_place_of_publication (
  rek_place_of_publication_id int(11) NOT NULL auto_increment,
  rek_place_of_publication_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_place_of_publication_xsdmf_id int(11) default NULL,
  rek_place_of_publication varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_place_of_publication_id),
  KEY rek_place_of_publication (rek_place_of_publication),
  KEY rek_place_of_publication_pid (rek_place_of_publication_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_start_page (
  rek_start_page_id int(11) NOT NULL auto_increment,
  rek_start_page_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_start_page_xsdmf_id int(11) default NULL,
  rek_start_page varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_start_page_id),
  KEY rek_start_page (rek_start_page),
  KEY rek_start_page_pid (rek_start_page_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_end_page (
  rek_end_page_id int(11) NOT NULL auto_increment,
  rek_end_page_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_end_page_xsdmf_id int(11) default NULL,
  rek_end_page varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_end_page_id),
  KEY rek_end_page (rek_end_page),
  KEY rek_end_page_pid (rek_end_page_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_chapter_number (
  rek_chapter_number_id int(11) NOT NULL auto_increment,
  rek_chapter_number_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_chapter_number_xsdmf_id int(11) default NULL,
  rek_chapter_number varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_chapter_number_id),
  KEY rek_chapter_number (rek_chapter_number),
  KEY rek_chapter_number_pid (rek_chapter_number_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_issue_number (
  rek_issue_number_id int(11) NOT NULL auto_increment,
  rek_issue_number_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_issue_number_xsdmf_id int(11) default NULL,
  rek_issue_number varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_issue_number_id),
  KEY rek_issue_number (rek_issue_number),
  KEY rek_issue_number_pid (rek_issue_number_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_volume_number (
  rek_volume_number_id int(11) NOT NULL auto_increment,
  rek_volume_number_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_volume_number_xsdmf_id int(11) default NULL,
  rek_volume_number varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_volume_number_id),
  KEY rek_volume_number (rek_volume_number),
  KEY rek_volume_number_pid (rek_volume_number_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_conference_dates (
  rek_conference_dates_id int(11) NOT NULL auto_increment,
  rek_conference_dates_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_conference_dates_xsdmf_id int(11) default NULL,
  rek_conference_dates varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_conference_dates_id),
  KEY rek_conference_dates (rek_conference_dates),
  KEY rek_conference_dates_pid (rek_conference_dates_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_conference_location (
  rek_conference_location_id int(11) NOT NULL auto_increment,
  rek_conference_location_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_conference_location_xsdmf_id int(11) default NULL,
  rek_conference_location varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_conference_location_id),
  KEY rek_conference_location (rek_conference_location),
  KEY rek_conference_location_pid (rek_conference_location_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_patent_number (
  rek_patent_number_id int(11) NOT NULL auto_increment,
  rek_patent_number_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_patent_number_xsdmf_id int(11) default NULL,
  rek_patent_number varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_patent_number_id),
  KEY rek_patent_number (rek_patent_number),
  KEY rek_patent_number_pid (rek_patent_number_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_country_of_issue (
  rek_country_of_issue_id int(11) NOT NULL auto_increment,
  rek_country_of_issue_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_country_of_issue_xsdmf_id int(11) default NULL,
  rek_country_of_issue varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_country_of_issue_id),
  KEY rek_country_of_issue (rek_country_of_issue),
  KEY rek_country_of_issue_pid (rek_country_of_issue_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_date_available (
  rek_date_available_id int(11) NOT NULL auto_increment,
  rek_date_available_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_date_available_xsdmf_id int(11) default NULL,
  rek_date_available datetime default NULL COMMENT 'Date Available',
  PRIMARY KEY  (rek_date_available_id),
  KEY rek_date_available (rek_date_available),
  KEY rek_date_available_pid (rek_date_available_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_language (
  rek_language_id int(11) NOT NULL auto_increment,
  rek_language_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_language_xsdmf_id int(11) default NULL,
  rek_language varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_language_id),
  KEY rek_language (rek_language),
  KEY rek_language_pid (rek_language_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_anglicised_title (
  rek_anglicised_title_id int(11) NOT NULL auto_increment,
  rek_anglicised_title_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_anglicised_title_xsdmf_id int(11) default NULL,
  rek_anglicised_title varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_anglicised_title_id),
  KEY rek_anglicised_title (rek_anglicised_title),
  KEY rek_anglicised_title_pid (rek_anglicised_title_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_language_of_title (
  rek_language_of_title_id int(11) NOT NULL auto_increment,
  rek_language_of_title_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_language_of_title_xsdmf_id int(11) default NULL,
  rek_language_of_title varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_language_of_title_id),
  KEY rek_language_of_title (rek_language_of_title),
  KEY rek_language_of_title_pid (rek_language_of_title_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_english_title (
  rek_english_title_id int(11) NOT NULL auto_increment,
  rek_english_title_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_english_title_xsdmf_id int(11) default NULL,
  rek_english_title varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_english_title_id),
  KEY rek_english_title (rek_english_title),
  KEY rek_english_title_pid (rek_english_title_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_anglicised_publisher (
  rek_anglicised_publisher_id int(11) NOT NULL auto_increment,
  rek_anglicised_publisher_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_anglicised_publisher_xsdmf_id int(11) default NULL,
  rek_anglicised_publisher varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_anglicised_publisher_id),
  KEY rek_anglicised_publisher (rek_anglicised_publisher),
  KEY rek_anglicised_publisher_pid (rek_anglicised_publisher_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_english_publisher (
  rek_english_publisher_id int(11) NOT NULL auto_increment,
  rek_english_publisher_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_english_publisher_xsdmf_id int(11) default NULL,
  rek_english_publisher varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_english_publisher_id),
  KEY rek_english_publisher (rek_english_publisher),
  KEY rek_english_publisher_pid (rek_english_publisher_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;