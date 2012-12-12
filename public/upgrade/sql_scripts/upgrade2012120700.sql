CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_fields_of_research (
  rek_fields_of_research_id int(11) NOT NULL AUTO_INCREMENT,
  rek_fields_of_research_pid varchar(64) DEFAULT NULL,
  rek_fields_of_research_xsdmf_id int(11) DEFAULT NULL,
  rek_fields_of_research int(11) DEFAULT NULL,
  rek_fields_of_research_order int(11) DEFAULT '1',
  PRIMARY KEY (rek_fields_of_research_id),
  UNIQUE KEY unique_constraint (rek_fields_of_research_pid,rek_fields_of_research,rek_fields_of_research_order),
  UNIQUE KEY unique_constraint_pid_order (rek_fields_of_research_pid,rek_fields_of_research_order),
  KEY rek_fields_of_research_pid (rek_fields_of_research_pid),
  KEY rek_fields_of_research (rek_fields_of_research),
  KEY rek_fields_of_research_order (rek_fields_of_research_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_fields_of_research__shadow (
  rek_fields_of_research_id int(11) NOT NULL AUTO_INCREMENT,
  rek_fields_of_research_pid varchar(64) DEFAULT NULL,
  rek_fields_of_research_xsdmf_id int(11) DEFAULT NULL,
  rek_fields_of_research int(11) DEFAULT NULL,
  rek_fields_of_research_order int(11) DEFAULT '1',
  rek_fields_of_research_stamp datetime DEFAULT NULL,
  PRIMARY KEY (rek_fields_of_research_id),
  UNIQUE KEY unique_constraint (rek_fields_of_research_pid,rek_fields_of_research_order,rek_fields_of_research_stamp),
  KEY rek_fields_of_research_pid (rek_fields_of_research_pid),
  KEY rek_fields_of_research (rek_fields_of_research),
  KEY rek_fields_of_research_order (rek_fields_of_research_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_seo_code (
  rek_seo_code_id int(11) NOT NULL AUTO_INCREMENT,
  rek_seo_code_pid varchar(64) DEFAULT NULL,
  rek_seo_code_xsdmf_id int(11) DEFAULT NULL,
  rek_seo_code int(11) DEFAULT NULL,
  rek_seo_code_order int(11) DEFAULT '1',
  PRIMARY KEY (rek_seo_code_id),
  UNIQUE KEY unique_constraint (rek_seo_code_pid,rek_seo_code,rek_seo_code_order),
  UNIQUE KEY unique_constraint_pid_order (rek_seo_code_pid,rek_seo_code_order),
  KEY rek_seo_code_pid (rek_seo_code_pid),
  KEY rek_seo_code (rek_seo_code),
  KEY rek_seo_code_order (rek_seo_code_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_seo_code__shadow (
  rek_seo_code_id int(11) NOT NULL AUTO_INCREMENT,
  rek_seo_code_pid varchar(64) DEFAULT NULL,
  rek_seo_code_xsdmf_id int(11) DEFAULT NULL,
  rek_seo_code int(11) DEFAULT NULL,
  rek_seo_code_order int(11) DEFAULT '1',
  rek_seo_code_stamp datetime DEFAULT NULL,
  PRIMARY KEY (rek_seo_code_id),
  UNIQUE KEY unique_constraint (rek_seo_code_pid,rek_seo_code_order,rek_seo_code_stamp),
  KEY rek_seo_code_pid (rek_seo_code_pid),
  KEY rek_seo_code (rek_seo_code),
  KEY rek_seo_code_order (rek_seo_code_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE %TABLE_PREFIX%record_search_key
    ADD COLUMN rek_copyright_xsdmf_id int(11),
    ADD COLUMN rek_copyright int(11);

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_109','core','109','Fields of Research','','0','0','0','0','0','contvocab','none','',451780,'Controlled_Vocab::getTitle','int','1','','1','','0','','0','');

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_110','core','110','SEO Code','','0','0','0','0','0','contvocab','none','',450779,'Controlled_Vocab::getTitle','int','1','','1','','0','','0','');

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_111','core','111','Copyright','','0','0','0','0','0','checkbox','none','',450779,'','int','0','','0','','0','','0','');

