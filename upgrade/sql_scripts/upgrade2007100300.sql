CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_translated_title (
  rek_translated_title_id int(11) NOT NULL auto_increment,
  rek_translated_title_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_translated_title_xsdmf_id int(11) default NULL,
  rek_translated_title varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_translated_title_id),
  KEY rek_translated_title_pid (rek_translated_title_pid,rek_translated_title)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_phonetic_title (
  rek_phonetic_title_id int(11) NOT NULL auto_increment,
  rek_phonetic_title_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_phonetic_title_xsdmf_id int(11) default NULL,
  rek_phonetic_title varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_phonetic_title_id),
  KEY rek_phonetic_title_pid (rek_phonetic_title_pid,rek_phonetic_title)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_translated_conference_name (
  rek_translated_conference_name_id int(11) NOT NULL auto_increment,
  rek_translated_conference_name_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_translated_conference_name_xsdmf_id int(11) default NULL,
  rek_translated_conference_name varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_translated_conference_name_id),
  KEY rek_translated_conference_name_pid (rek_translated_conference_name_pid,rek_translated_conference_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_phonetic_conference_name (
  rek_phonetic_conference_name_id int(11) NOT NULL auto_increment,
  rek_phonetic_conference_name_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_phonetic_conference_name_xsdmf_id int(11) default NULL,
  rek_phonetic_conference_name varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_phonetic_conference_name_id),
  KEY rek_phonetic_conference_name_pid (rek_phonetic_conference_name_pid,rek_phonetic_conference_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_translated_journal_name (
  rek_translated_journal_name_id int(11) NOT NULL auto_increment,
  rek_translated_journal_name_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_translated_journal_name_xsdmf_id int(11) default NULL,
  rek_translated_journal_name varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_translated_journal_name_id),
  KEY rek_translated_journal_name_pid (rek_translated_journal_name_pid,rek_translated_journal_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_phonetic_journal_name (
  rek_phonetic_journal_name_id int(11) NOT NULL auto_increment,
  rek_phonetic_journal_name_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_phonetic_journal_name_xsdmf_id int(11) default NULL,
  rek_phonetic_journal_name varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_phonetic_journal_name_id),
  KEY rek_phonetic_journal_name_pid (rek_phonetic_journal_name_pid,rek_phonetic_journal_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_translated_book_title (
  rek_translated_book_title_id int(11) NOT NULL auto_increment,
  rek_translated_book_title_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_translated_book_title_xsdmf_id int(11) default NULL,
  rek_translated_book_title varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_translated_book_title_id),
  KEY rek_translated_book_title_pid (rek_translated_book_title_pid,rek_translated_book_title)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_phonetic_book_title (
  rek_phonetic_book_title_id int(11) NOT NULL auto_increment,
  rek_phonetic_book_title_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_phonetic_book_title_xsdmf_id int(11) default NULL,
  rek_phonetic_book_title varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_phonetic_book_title_id),
  KEY rek_phonetic_book_title_pid (rek_phonetic_book_title_pid,rek_phonetic_book_title)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_translated_newspaper (
  rek_translated_newspaper_id int(11) NOT NULL auto_increment,
  rek_translated_newspaper_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_translated_newspaper_xsdmf_id int(11) default NULL,
  rek_translated_newspaper varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_translated_newspaper_id),
  KEY rek_translated_newspaper_pid (rek_translated_newspaper_pid,rek_translated_newspaper)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_phonetic_newspaper (
  rek_phonetic_newspaper_id int(11) NOT NULL auto_increment,
  rek_phonetic_newspaper_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_phonetic_newspaper_xsdmf_id int(11) default NULL,
  rek_phonetic_newspaper varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_phonetic_newspaper_id),
  KEY rek_phonetic_newspaper_pid (rek_phonetic_newspaper_pid,rek_phonetic_newspaper)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_issn (
  rek_issn_id int(11) NOT NULL auto_increment,
  rek_issn_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_issn_xsdmf_id int(11) default NULL,
  rek_issn varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_issn_id),
  KEY rek_issn_pid (rek_issn_pid,rek_issn)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isbn (
  rek_isbn_id int(11) NOT NULL auto_increment,
  rek_isbn_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_isbn_xsdmf_id int(11) default NULL,
  rek_isbn varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_isbn_id),
  KEY rek_isbn_pid (rek_isbn_pid,rek_isbn)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_prn (
  rek_prn_id int(11) NOT NULL auto_increment,
  rek_prn_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_prn_xsdmf_id int(11) default NULL,
  rek_prn varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_prn_id),
  KEY rek_prn_pid (rek_prn_pid,rek_prn)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isi_loc (
  rek_isi_loc_id int(11) NOT NULL auto_increment,
  rek_isi_loc_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_isi_loc_xsdmf_id int(11) default NULL,
  rek_isi_loc varchar(255) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_isi_loc_id),
  KEY rek_isi_loc_pid (rek_isi_loc_pid,rek_isi_loc)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_output_availability (
  rek_output_availability_id int(11) NOT NULL auto_increment,
  rek_output_availability_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_output_availability_xsdmf_id int(11) default NULL,
  rek_output_availability varchar(1) character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_output_availability_id),
  KEY rek_output_availability_pid (rek_output_availability_pid,rek_output_availability)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_na_explanation (
  rek_na_explanation_id int(11) NOT NULL auto_increment,
  rek_na_explanation_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_na_explanation_xsdmf_id int(11) default NULL,
  rek_na_explanation text character set utf8 collate utf8_general_ci,
  PRIMARY KEY  (rek_na_explanation_id),
  FULLTEXT KEY rek_na_explanation (rek_na_explanation),
  KEY rek_na_explanation_pid (rek_na_explanation_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_sensitivity_explanation (
  rek_sensitivity_explanation_id int(11) NOT NULL auto_increment,
  rek_sensitivity_explanation_pid varchar(64) character set utf8 collate utf8_general_ci default NULL,
  rek_sensitivity_explanation_xsdmf_id int(11) default NULL,
  rek_sensitivity_explanation text character set utf8 collate utf8_general_ci default NULL,
  PRIMARY KEY  (rek_sensitivity_explanation_id),
  FULLTEXT KEY  (rek_sensitivity_explanation),
  KEY rek_sensitivity_explanation_pid (rek_sensitivity_explanation_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO %TABLE_PREFIX%search_key VALUES 
(52,'Phonetic Title',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(54,'Translated Title',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(55,'Phonetic Journal Name',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(56,'Translated Journal Name',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(57,'Phonetic Book Title',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(58,'Translated Book Title',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(59,'Phonetic Newspaper',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(60,'Translated Newspaper',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(61,'Phonetic Conference Name',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(62,'Translated Conference Name',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(63,'ISSN',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(64,'ISBN',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(65,'ISI LOC',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(66,'PRN',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(67,'Output Availability',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(68,'NA Explanation',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,''),
(69,'Sensitivity Explanation',NULL,0,0,0,0,'text','none','',450005,NULL,'varchar',1,'');	

DROP TABLE IF EXISTS %TABLE_PREFIX%search_key_anglicised_title;
DROP TABLE IF EXISTS %TABLE_PREFIX%search_key_english_title;
DROP TABLE IF EXISTS %TABLE_PREFIX%search_key_anglicised_publisher;
DROP TABLE IF EXISTS %TABLE_PREFIX%search_key_english_publisher;