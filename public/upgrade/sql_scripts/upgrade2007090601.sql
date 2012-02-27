CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key (
  rek_pid varchar(64) character set utf8 collate utf8_bin NOT NULL COMMENT 'PID',
  rek_title_xsdmf_id int(11) default NULL,
  rek_title varchar(255) character set utf8 collate utf8_bin default NULL COMMENT 'Title',
  rek_description_xsdmf_id int(11) default NULL,
  rek_description text character set utf8 collate utf8_bin COMMENT 'Description',
  rek_display_type_xsdmf_id int(11) default NULL,
  rek_display_type int(11) default NULL COMMENT 'Display Type',
  rek_status_xsdmf_id int(11) default NULL,
  rek_status int(11) default NULL COMMENT 'Status',
  rek_date_xsdmf_id int(11) default NULL,
  rek_date datetime default NULL COMMENT 'Date',
  rek_object_type_xsdmf_id int(11) default NULL,
  rek_object_type int(11) default NULL COMMENT 'Object Type',
  rek_depositor_xsdmf_id int(11) default NULL,
  rek_depositor int(11) default NULL COMMENT 'Depositor',
  rek_created_date_xsdmf_id int(11) default NULL,
  rek_created_date datetime default NULL COMMENT 'Created Date',
  rek_updated_date_xsdmf_id int(11) default NULL,
  rek_updated_date datetime default NULL COMMENT 'Updated Date',
  PRIMARY KEY  (rek_pid),
  KEY rek_display_type (rek_display_type),
  KEY rek_status (rek_status),
  KEY rek_date (rek_date),
  KEY rek_object_type (rek_object_type),
  KEY rek_depositor (rek_depositor),
  KEY rek_created_date (rek_created_date),
  KEY rek_updated_date (rek_updated_date),
  KEY rek_title (rek_title),
  FULLTEXT KEY rek_description (rek_description),
  FULLTEXT KEY rek_fulltext (rek_title,rek_description),
  FULLTEXT KEY rek_fulltext_all (rek_pid,rek_title,rek_description)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_alternative_title (
  rek_alternative_title_id int(11) NOT NULL auto_increment,
  rek_alternative_title_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_alternative_title_xsdmf_id int(11) default NULL,
  rek_alternative_title varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_alternative_title_id),
  KEY rek_alternative_title (rek_alternative_title),
  KEY rek_alternative_title_pid (rek_alternative_title_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_assigned_group_id (
  rek_assigned_group_id_id int(11) NOT NULL auto_increment,
  rek_assigned_group_id_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_assigned_group_id_xsdmf_id int(11) default NULL,
  rek_assigned_group_id int(11) default NULL,
  PRIMARY KEY  (rek_assigned_group_id_id),
  KEY rek_assigned_group_id_pid (rek_assigned_group_id_pid),
  KEY rek_assigned_group_id (rek_assigned_group_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_assigned_user_id (
  rek_assigned_user_id_id int(11) NOT NULL auto_increment,
  rek_assigned_user_id_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_assigned_user_id_xsdmf_id int(11) default NULL,
  rek_assigned_user_id int(11) default NULL,
  PRIMARY KEY  (rek_assigned_user_id_id),
  KEY rek_assigned_user_id_pid (rek_assigned_user_id_pid),
  KEY rek_assigned_user_id (rek_assigned_user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_author (
  rek_author_id int(11) NOT NULL auto_increment,
  rek_author_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_author_xsdmf_id int(11) default NULL,
  rek_author varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_author_id),
  KEY rek_author_pid (rek_author_pid),
  KEY rek_author (rek_author)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_author_id (
  rek_author_id_id int(11) NOT NULL auto_increment,
  rek_author_id_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_author_id_xsdmf_id int(11) default NULL,
  rek_author_id int(11) default NULL,
  PRIMARY KEY  (rek_author_id_id),
  KEY rek_author_id_pid (rek_author_id_pid),
  KEY rek_author_id (rek_author_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_book_title (
  rek_book_title_id int(11) NOT NULL auto_increment,
  rek_book_title_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_book_title_xsdmf_id int(11) default NULL,
  rek_book_title varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_book_title_id),
  KEY rek_book_title (rek_book_title),
  KEY rek_book_title_pid (rek_book_title_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_conference_name (
  rek_conference_name_id int(11) NOT NULL auto_increment,
  rek_conference_name_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_conference_name_xsdmf_id int(11) default NULL,
  rek_conference_name varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_conference_name_id),
  KEY rek_conference_name_pid (rek_conference_name_pid),
  KEY rek_conference_name (rek_conference_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_contributor (
  rek_contributor_id int(11) NOT NULL auto_increment,
  rek_contributor_pid varchar(64) collate utf8_bin default NULL,
  rek_contributor_xsdmf_id int(11) default NULL,
  rek_contributor varchar(255) collate utf8_bin default NULL,
  PRIMARY KEY  (rek_contributor_id),
  KEY rek_contributor_pid (rek_contributor_pid),
  KEY rek_contributor (rek_contributor)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_contributor_id (
  rek_contributor_id_id int(11) NOT NULL auto_increment,
  rek_contributor_id_pid varchar(64) default NULL,
  rek_contributor_id_xsdmf_id int(11) default NULL,
  rek_contributor_id int(11) default NULL,
  PRIMARY KEY  (rek_contributor_id_id),
  KEY rek_contributor_id_pid (rek_contributor_id_pid),
  KEY rek_contributor_id (rek_contributor_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_file_attachment_content (
  rek_file_attachment_content_id int(11) NOT NULL auto_increment,
  rek_file_attachment_content_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_file_attachment_content_xsdmf_id int(11) default NULL,
  rek_file_attachment_content text character set utf8 collate utf8_bin,
  PRIMARY KEY  (rek_file_attachment_content_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_file_attachment_name (
  rek_file_attachment_name_id int(11) NOT NULL auto_increment,
  rek_file_attachment_name_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_file_attachment_name_xsdmf_id int(11) default NULL,
  rek_file_attachment_name varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_file_attachment_name_id),
  UNIQUE KEY rek_file_attachment_name_pid_unique (rek_file_attachment_name_pid,rek_file_attachment_name),
  KEY rek_file_attachment_name_id (rek_file_attachment_name_pid),
  KEY rek_file_attachment_name (rek_file_attachment_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_file_downloads (
  rek_file_downloads_id int(11) NOT NULL auto_increment,
  rek_file_downloads_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_file_downloads_xsdmf_id int(11) default NULL,
  rek_file_downloads int(11) default NULL,
  PRIMARY KEY  (rek_file_downloads_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_identifier (
  rek_identifier_id int(11) NOT NULL auto_increment,
  rek_identifier_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_identifier_xsdmf_id int(11) default NULL,
  rek_identifier varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_identifier_id),
  KEY rek_identifier_pid (rek_identifier_pid),
  KEY rek_identifier (rek_identifier)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isannotationof (
  rek_isannotationof_id int(11) NOT NULL auto_increment,
  rek_isannotationof_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_isannotationof_xsdmf_id int(11) default NULL,
  rek_isannotationof varchar(64) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_isannotationof_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isdatacomponentof (
  rek_isdatacomponentof_id int(11) NOT NULL auto_increment,
  rek_isdatacomponentof_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_isdatacomponentof_xsdmf_id int(11) default NULL,
  rek_isdatacomponentof varchar(64) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_isdatacomponentof_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isderivationof (
  rek_isderivationof_id int(11) NOT NULL auto_increment,
  rek_isderivationof_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_isderivationof_xsdmf_id int(11) default NULL,
  rek_isderivationof varchar(64) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_isderivationof_id),
  KEY rek_isderivationof (rek_isderivationof),
  KEY rek_isderivationof_pid (rek_isderivationof_pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_ismemberof (
  rek_ismemberof_id int(11) NOT NULL auto_increment,
  rek_ismemberof_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_ismemberof_xsdmf_id int(11) default NULL,
  rek_ismemberof varchar(64) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_ismemberof_id),
  KEY rek_ismemberof_pid_value (rek_ismemberof_pid,rek_ismemberof),
  KEY rek_ismemberof_pid (rek_ismemberof)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_journal_name (
  rek_journal_name_id int(11) NOT NULL auto_increment,
  rek_journal_name_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_journal_name_xsdmf_id int(11) default NULL,
  rek_journal_name varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_journal_name_id),
  KEY rek_journal_name_pid (rek_journal_name_pid),
  KEY rek_journal_name (rek_journal_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_keywords (
  rek_keywords_id int(11) NOT NULL auto_increment,
  rek_keywords_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_keywords_xsdmf_id int(11) default NULL,
  rek_keywords varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_keywords_id),
  KEY rek_keywords_pid (rek_keywords_pid),
  KEY rek_keywords (rek_keywords),
  FULLTEXT KEY rek_keywords_fulltext (rek_keywords)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_newspaper (
  rek_newspaper_id int(11) NOT NULL auto_increment,
  rek_newspaper_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_newspaper_xsdmf_id int(11) default NULL,
  rek_newspaper varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_newspaper_id),
  KEY rek_newspaper_pid (rek_newspaper_pid),
  KEY rek_newspaper (rek_newspaper)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_notes (
  rek_notes_id int(11) NOT NULL auto_increment,
  rek_notes_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_notes_xsdmf_id int(11) default NULL,
  rek_notes text character set utf8 collate utf8_bin,
  PRIMARY KEY  (rek_notes_id),
  KEY rek_notes_pid (rek_notes_pid),
  FULLTEXT KEY rek_notes (rek_notes)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_publisher (
  rek_publisher_id int(11) NOT NULL auto_increment,
  rek_publisher_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_publisher_xsdmf_id int(11) default NULL,
  rek_publisher varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_publisher_id),
  KEY rek_publisher_pid (rek_publisher_pid),
  KEY rek_publisher (rek_publisher)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_refereed (
  rek_refereed_id int(11) NOT NULL auto_increment,
  rek_refereed_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_refereed_xsdmf_id int(11) default NULL,
  rek_refereed int(11) default NULL,
  PRIMARY KEY  (rek_refereed_id),
  KEY rek_refereed_pid (rek_refereed_pid),
  KEY rek_refereed (rek_refereed)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_research_program (
  rek_research_program_id int(11) NOT NULL auto_increment,
  rek_research_program_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_research_program_xsdmf_id int(11) default NULL,
  rek_research_program varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_research_program_id),
  KEY rek_research_program_pid (rek_research_program_pid),
  KEY rek_research_program (rek_research_program)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_series (
  rek_series_id int(11) NOT NULL auto_increment,
  rek_series_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_series_xsdmf_id int(11) default NULL,
  rek_series varchar(255) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY  (rek_series_id),
  KEY rek_series_pid (rek_series_pid),
  KEY rek_series (rek_series)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_subject (
  rek_subject_id int(11) NOT NULL auto_increment,
  rek_subject_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_subject_xsdmf_id int(11) default NULL,
  rek_subject int(11) default NULL,
  PRIMARY KEY  (rek_subject_id),
  KEY rek_subject_pid (rek_subject_pid,rek_subject)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_xsd_display_option (
  rek_xsd_display_option_id int(11) NOT NULL auto_increment,
  rek_xsd_display_option_pid varchar(64) character set utf8 collate utf8_bin default NULL,
  rek_xsd_display_option_xsdmf_id int(11) default NULL,
  rek_xsd_display_option int(11) default NULL,
  PRIMARY KEY  (rek_xsd_display_option_id),
  KEY rek_xsd_display_option_pid (rek_xsd_display_option_pid),
  KEY rek_xsd_display_option (rek_xsd_display_option)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%workflow_roles (
  wfr_wfl_id int(11) unsigned NOT NULL,
  wfr_aro_id int(11) unsigned NOT NULL,
  PRIMARY KEY  (wfr_wfl_id,wfr_aro_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


RENAME TABLE %TABLE_PREFIX%auth_index2 TO %TABLE_PREFIX%auth_index2_pre_fez2_upgrade;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_index2 (                                                    
                   authi_pid varchar(64) character set utf8 collate utf8_bin NOT NULL default '',  
                   authi_role int(11) unsigned NOT NULL default '0',                               
                   authi_arg_id int(11) unsigned NOT NULL default '0',                             
                   PRIMARY KEY  (authi_pid,authi_role,authi_arg_id),                           
                   KEY authi_role_arg_id (authi_role,authi_arg_id),                            
                   KEY authi_role (authi_pid,authi_role),                                      
                   KEY authi_pid_arg_id (authi_pid,authi_arg_id),                              
                   KEY authi_pid (authi_pid),                                                    
                   KEY authi_arg_id (authi_arg_id)                                               
                 ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_roles (
	aro_id int(11) unsigned NOT NULL  auto_increment,
	aro_role varchar(64) NOT NULL,
	aro_ranking int(11) unsigned NOT NULL,
	PRIMARY KEY (aro_id)
) Engine=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO %TABLE_PREFIX%auth_roles VALUES (1,'Annotator',0),(2,'Approver',0),(3,'Archival_Format_Viewer',0),(4,'Commentor',0),(5,'Comment_Viewer',0),(6,'Community_Administrator',0),(7,'Creator',0),(8,'Editor',0),(9,'Lister',0),(10,'Viewer',0),(0,'',0);

ALTER TABLE %TABLE_PREFIX%background_process 
	add KEY bgp_started(bgp_started), 
	add KEY bgp_state(bgp_state), 
	add KEY bgp_usr_id(bgp_usr_id), COMMENT='';
	
ALTER TABLE %TABLE_PREFIX%search_key 
	add column sek_data_type varchar(10) NULL  after sek_lookup_function, 
	add column sek_relationship tinyint(1) NULL  DEFAULT '0' COMMENT '0 is 1-1, 1 is 1-M' after sek_data_type, 
	add column sek_meta_header varchar(64) NULL  after sek_relationship, COMMENT='';
	
REPLACE INTO %TABLE_PREFIX%search_key VALUES (2,'Title',NULL,1,1,1,0,'text','none','',450005,NULL,'varchar',0,'DC.Title'),(3,'Author','Author Name',1,1,0,1,'text','none','',1,'','varchar',1,'DC.Creator'),(4,'Subject','',1,1,0,20,'allcontvocab','none','',1,'Controlled_Vocab::getTitle','int',1,'DC.Subject'),(5,'Description',NULL,1,1,NULL,2,'text','','',1,NULL,'text',0,'DC.Description'),(6,'File Attachment Name','',0,0,0,9,'text','none','',450005,NULL,'varchar',1,'DC.Format'),(7,'File Attachment Content',NULL,0,0,NULL,999,'text','none','',1,NULL,'text',1,''),(8,'isMemberOf','Collection',1,1,1,12,'multiple','none','Collection::getAssocList()',450005,'','varchar',1,''),(9,'Status','',0,0,1,6,'combo','none','Status::getUnpublishedAssocList()',450005,'Status::getTitle','int',0,''),(10,'Object Type','',1,0,0,8,'multiple','none','$ret_list',450005,'Object_Type::getTitle','int',0,''),(11,'Display Type','',1,0,0,5,'multiple','none','$xdis_list',450005,'XSD_Display::getTitle','int',0,'DC.Type'),(12,'Keywords','',0,0,0,3,'text','none','',450005,NULL,'varchar',1,''),(13,'Notes',NULL,0,0,NULL,999,'','','',1,NULL,'text',1,''),(14,'Date','Published Date',1,1,1,7,'date','none','',450005,'','date',0,'DC.Date'),(15,'XSD Display Option','',0,0,0,999,'','none','',1,'XSD_Display::getTitle','int',1,''),(16,'File Downloads','',0,0,0,999,'text','none','',450005,NULL,'int',1,''),(17,'Created Date','',1,1,1,8,'date','none','',450005,'','date',0,''),(18,'Updated Date','',1,1,1,9,'date','none','',1,'','date',0,''),(19,'Research Program','',0,0,0,4,'text','none','',450005,NULL,'varchar',1,''),(20,'Depositor','',1,0,1,16,'combo','none','User::getAssocList()',450005,'User::getFullName','int',0,''),(21,'isDerivationOf','',0,0,0,15,'text','none','',450005,'','varchar',1,''),(22,'Assigned User ID','Assigned',0,0,1,11,'combo','none','User::getAssocList()',450005,'User::getFullName','int',1,''),(23,'Assigned Group ID','Team/Group',0,0,1,10,'combo','none','Group::getAssocListAll()',450005,'Group::getName','int',1,''),(24,'isDataComponentOf','',0,0,0,13,'multiple','none','',450005,'','varchar',1,''),(25,'isAnnotationOf','',0,0,0,14,'multiple','none','',450005,'','varchar',1,''),(26,'Author ID','Author',0,0,0,2,'multiple','none','Author::getAssocListAll()',450005,'Author::getFullName','int',1,''),(27,'Alternative Title','',0,0,0,999,'text','none','',450005,'','varchar',1,''),(28,'Pid','',1,1,1,0,'text','none','',450005,'','varchar',0,''),(29,'Publisher','',1,1,1,21,'text','none','',450005,'','varchar',1,'DC.Publisher'),(30,'Contributor','Contributor',0,0,0,22,'textarea','none','',450005,'','varchar',1,'DC.Contributor'),(31,'Contributor ID','',0,0,0,23,'text','none','',450005,'Author::getFullName','int',1,''),(32,'Refereed','',1,1,1,22,'checkbox','none','',450005,'','int',1,''),(33,'Series','',1,1,1,23,'text','none','',450005,'','varchar',1,''),(34,'Journal Name','',1,1,1,24,'text','none','',450005,'','varchar',1,''),(35,'Newspaper','',1,1,1,25,'text','none','',450005,'','varchar',1,''),(36,'Conference Name','',1,1,1,26,'text','none','',450005,'','varchar',1,''),(37,'Book Title','',1,1,1,27,'text','none','',450005,'','varchar',1,''),(38,'Identifier','',0,0,0,999,'text','none','',450005,'','varchar',1,'DC.Identifier');	
	
CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%workflow_roles (
	wfr_wfl_id int(11) unsigned NOT NULL,
	wfr_aro_id int(11) unsigned NOT NULL,
	PRIMARY KEY (wfr_wfl_id,wfr_aro_id) 
) Engine=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%workflow_state_roles (
	wfsr_wfs_id int(11) unsigned NOT NULL,
	wfsr_aro_id int(11) unsigned NOT NULL,
	PRIMARY KEY (wfsr_wfs_id,wfsr_aro_id) 
) Engine=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO %TABLE_PREFIX%workflow_state_roles
select wfs_id, aro_id from %TABLE_PREFIX%workflow_state
inner join %TABLE_PREFIX%auth_roles on instr(wfs_roles, aro_role) > 0 and aro_id != 0;

REPLACE INTO %TABLE_PREFIX%workflow_roles
select wfL_id, aro_id from %TABLE_PREFIX%workflow
inner join %TABLE_PREFIX%auth_roles on instr(wfL_roles, aro_role) > 0 and aro_id != 0;