INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_148','core','148','Creator Name','','0','0','0','0','0','text','none','',NULL,'','varchar','1','','1','','0','','0','');


INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_149','core','149','Creator ID','','0','0','0','0','0','text','none','',NULL,'Author::getFullName','int','1','','1','','0','','0','');

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_150','core','150','Scale','','0','0','0','0','0','text','none','',NULL,'','varchar','1','','0','','0','','0','');

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_151','core','151','Job Number','','0','0','0','0','0','text','none','',NULL,'','varchar','1','','0','','0','','0','');

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange)
  values ('core_152','core','152','Project Start Date','','',0,0,0,0,'date','none','',NULL,'','date','1','','0','',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_creator_name (
     rek_creator_name_id int(11) NOT NULL auto_increment,
     rek_creator_name_pid varchar(64) NOT NULL,
     rek_creator_name_xsdmf_id int(11) NOT NULL,
     rek_creator_name_order int(11) default 1,
     rek_creator_name varchar(255) default NULL,
     PRIMARY KEY (rek_creator_name_id),
     KEY rek_creator_name (rek_creator_name),
     KEY rek_creator_name_pid (rek_creator_name_pid),
     UNIQUE KEY unique_constraint_pid_order (rek_creator_name_pid, rek_creator_name_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_creator_name__shadow (
     rek_creator_name_id int(11) NOT NULL,
     rek_creator_name_stamp datetime,
     rek_creator_name_pid varchar(64) NOT NULL,
     rek_creator_name_xsdmf_id int(11) NOT NULL,
     rek_creator_name_order int(11) default 1,
     rek_creator_name varchar(255) default NULL,
     PRIMARY KEY (rek_creator_name_pid,rek_creator_name_stamp,rek_creator_name_order),
     KEY rek_creator_name (rek_creator_name),
     KEY rek_creator_name_pid (rek_creator_name_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%record_search_key_creator_id (
     rek_creator_id_id int(11) NOT NULL auto_increment,
     rek_creator_id_pid varchar(64) NOT NULL,
     rek_creator_id_xsdmf_id int(11) NOT NULL,
     rek_creator_id_order int(11) default 1,
     rek_creator_id int(11) default NULL,
     PRIMARY KEY (rek_creator_id_id),
     KEY rek_creator_id (rek_creator_id),
     KEY rek_creator_id_pid (rek_creator_id_pid),
     UNIQUE KEY unique_constraint_pid_order (rek_creator_id_pid, rek_creator_id_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%record_search_key_creator_id__shadow (
     rek_creator_id_id int(11) NOT NULL,
     rek_creator_id_stamp datetime,
     rek_creator_id_pid varchar(64) NOT NULL,
     rek_creator_id_xsdmf_id int(11) NOT NULL,
     rek_creator_id_order int(11) default 1,
     rek_creator_id int(11) default NULL,
     PRIMARY KEY (rek_creator_id_pid,rek_creator_id_stamp,rek_creator_id_order),
     KEY rek_creator_id (rek_creator_id),
     KEY rek_creator_id_pid (rek_creator_id_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_scale` (
     `rek_scale_id` int(11) NOT NULL auto_increment,
     `rek_scale_pid` varchar(64) default NULL,
     `rek_scale_xsdmf_id` int(11) default NULL,
      `rek_scale` varchar(255) default NULL,
     PRIMARY KEY (`rek_scale_id`),
     KEY `rek_scale` (`rek_scale`),
     UNIQUE KEY `rek_scale_pid` (`rek_scale_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_scale__shadow (
  rek_scale_id int(11) NOT NULL,
  rek_scale_pid varchar(64) NOT NULL DEFAULT '',
  rek_scale_xsdmf_id int(11) DEFAULT NULL,
  rek_scale varchar(255) DEFAULT NULL,
  rek_scale_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_scale_pid,rek_scale_stamp),
  KEY rek_scale (rek_scale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_job_number` (
     `rek_job_number_id` int(11) NOT NULL auto_increment,
     `rek_job_number_pid` varchar(64) default NULL,
     `rek_job_number_xsdmf_id` int(11) default NULL,
      `rek_job_number` varchar(255) default NULL,
     PRIMARY KEY (`rek_job_number_id`),
     KEY `rek_job_number` (`rek_job_number`),
     UNIQUE KEY `rek_job_number_pid` (`rek_job_number_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_job_number__shadow (
  rek_job_number_id int(11) NOT NULL,
  rek_job_number_pid varchar(64) NOT NULL DEFAULT '',
  rek_job_number_xsdmf_id int(11) DEFAULT NULL,
  rek_job_number varchar(255) DEFAULT NULL,
  rek_job_number_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_job_number_pid,rek_job_number_stamp),
  KEY rek_job_number (rek_job_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_project_start_date` (
     `rek_project_start_date_id` int(11) NOT NULL auto_increment,
     `rek_project_start_date_pid` varchar(64) default NULL,
     `rek_project_start_date_xsdmf_id` int(11) default NULL,
      `rek_project_start_date` datetime default NULL,
     PRIMARY KEY (`rek_project_start_date_id`),
     KEY `rek_project_start_date` (`rek_project_start_date`),
     UNIQUE KEY `rek_project_start_date_pid` (`rek_project_start_date_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_project_start_date__shadow (
  rek_project_start_date_id int(11) NOT NULL,
  rek_project_start_date_pid varchar(64) NOT NULL DEFAULT '',
  rek_project_start_date_xsdmf_id int(11) DEFAULT NULL,
  rek_project_start_date datetime DEFAULT NULL,
  rek_project_start_date_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_project_start_date_pid,rek_project_start_date_stamp),
  KEY rek_project_start_date (rek_project_start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;