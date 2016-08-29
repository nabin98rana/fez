INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_132','core',132,'Date Photo Taken','','',0,0,0,0,'text','none','',NULL,'','varchar',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_date_photo_taken` (
     `rek_date_photo_taken_id` int(11) NOT NULL auto_increment,
     `rek_date_photo_taken_pid` varchar(64) default NULL,
     `rek_date_photo_taken_xsdmf_id` int(11) default NULL,
      `rek_date_photo_taken` datetime default NULL,
     PRIMARY KEY (`rek_date_photo_taken_id`),
     KEY `rek_date_photo_taken` (`rek_date_photo_taken`),
     UNIQUE KEY `rek_date_photo_taken_pid` (`rek_date_photo_taken_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_133','core',133,'Date Scanned','','',0,0,0,0,'text','none','',NULL,'','varchar',1,'',0,'',NULL,0,'','',0);


CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_date_scanned` (
     `rek_date_scanned_id` int(11) NOT NULL auto_increment,
     `rek_date_scanned_pid` varchar(64) default NULL,
     `rek_date_scanned_xsdmf_id` int(11) default NULL,
      `rek_date_scanned` datetime default NULL,
     PRIMARY KEY (`rek_date_scanned_id`),
     KEY `rek_date_scanned` (`rek_date_scanned`),
     UNIQUE KEY `rek_date_scanned_pid` (`rek_date_scanned_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_134','core',134,'Total Pages Colour','','',0,0,0,0,'text','none','',NULL,'','varchar',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_total_pages_colour` (
     `rek_total_pages_colour_id` int(11) NOT NULL auto_increment,
     `rek_total_pages_colour_pid` varchar(64) default NULL,
     `rek_total_pages_colour_xsdmf_id` int(11) default NULL,
      `rek_total_pages_colour` varchar(255) default NULL,
     PRIMARY KEY (`rek_total_pages_colour_id`),
     KEY `rek_total_pages_colour` (`rek_total_pages_colour`),
     UNIQUE KEY `rek_total_pages_colour_pid` (`rek_total_pages_colour_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_135','core',135,'Total Pages BW','','',0,0,0,0,'text','none','',NULL,'','varchar',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_total_pages_bw` (
     `rek_total_pages_bw_id` int(11) NOT NULL auto_increment,
     `rek_total_pages_bw_pid` varchar(64) default NULL,
     `rek_total_pages_bw_xsdmf_id` int(11) default NULL,
      `rek_total_pages_bw` varchar(255) default NULL,
     PRIMARY KEY (`rek_total_pages_bw_id`),
     KEY `rek_total_pages_bw` (`rek_total_pages_bw`),
     UNIQUE KEY `rek_total_pages_bw_pid` (`rek_total_pages_bw_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_143','core',143,'Original Format','','',0,0,0,0,'text','none','',NULL,'','varchar',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_original_format` (
     `rek_original_format_id` int(11) NOT NULL auto_increment,
     `rek_original_format_pid` varchar(64) default NULL,
     `rek_original_format_xsdmf_id` int(11) default NULL,
      `rek_original_format` varchar(255) default NULL,
     PRIMARY KEY (`rek_original_format_id`),
     KEY `rek_original_format` (`rek_original_format`),
     UNIQUE KEY `rek_original_format_pid` (`rek_original_format_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_136','core',136,'Abbreviated Title','','',0,0,0,0,'text','none','',NULL,'','varchar',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_abbreviated_title` (
     `rek_abbreviated_title_id` int(11) NOT NULL auto_increment,
     `rek_abbreviated_title_pid` varchar(64) default NULL,
     `rek_abbreviated_title_xsdmf_id` int(11) default NULL,
      `rek_abbreviated_title` varchar(255) default NULL,
     PRIMARY KEY (`rek_abbreviated_title_id`),
     KEY `rek_abbreviated_title` (`rek_abbreviated_title`),
     UNIQUE KEY `rek_abbreviated_title_pid` (`rek_abbreviated_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_137','core',137,'Construction Date','','',0,0,0,0,'text','none','',NULL,'','varchar',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_construction_date` (
     `rek_construction_date_id` int(11) NOT NULL auto_increment,
     `rek_construction_date_pid` varchar(64) default NULL,
     `rek_construction_date_xsdmf_id` int(11) default NULL,
      `rek_construction_date` varchar(255) default NULL,
     PRIMARY KEY (`rek_construction_date_id`),
     KEY `rek_construction_date` (`rek_construction_date`),
     UNIQUE KEY `rek_construction_date_pid` (`rek_construction_date_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_138','core',138,'Embase ID','','',0,0,0,0,'text','none','',NULL,'','varchar',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_embase_id` (
     `rek_embase_id_id` int(11) NOT NULL auto_increment,
     `rek_embase_id_pid` varchar(64) default NULL,
     `rek_embase_id_xsdmf_id` int(11) default NULL,
      `rek_embase_id` varchar(255) default NULL,
     PRIMARY KEY (`rek_embase_id_id`),
     KEY `rek_embase_id` (`rek_embase_id`),
     UNIQUE KEY `rek_embase_id_pid` (`rek_embase_id_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_139','core',139,'Section','','',0,0,0,0,'text','none','',NULL,'','varchar',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_section` (
     `rek_section_id` int(11) NOT NULL auto_increment,
     `rek_section_pid` varchar(64) default NULL,
     `rek_section_xsdmf_id` int(11) default NULL,
      `rek_section` varchar(255) default NULL,
     PRIMARY KEY (`rek_section_id`),
     KEY `rek_section` (`rek_section`),
     UNIQUE KEY `rek_section_pid` (`rek_section_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_140','core',140,'References','','',0,0,0,0,'textarea','none','',NULL,'','text',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_references` (
     `rek_references_id` int(11) NOT NULL auto_increment,
     `rek_references_pid` varchar(64) default NULL,
     `rek_references_xsdmf_id` int(11) default NULL,
      `rek_references` TEXT default NULL,
     PRIMARY KEY (`rek_references_id`),
     FULLTEXT KEY `rek_references` (`rek_references`),
     UNIQUE KEY `rek_references_pid` (`rek_references_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_141','core',141,'Print Details','','',0,0,0,0,'textarea','none','',NULL,'','text',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_print_details` (
     `rek_print_details_id` int(11) NOT NULL auto_increment,
     `rek_print_details_pid` varchar(64) default NULL,
     `rek_print_details_xsdmf_id` int(11) default NULL,
      `rek_print_details` TEXT default NULL,
     PRIMARY KEY (`rek_print_details_id`),
     FULLTEXT KEY `rek_print_details` (`rek_print_details`),
     UNIQUE KEY `rek_print_details_pid` (`rek_print_details_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_142','core',142,'Additional Notes','','',0,0,0,0,'textarea','none','',NULL,'','text',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%record_search_key_additional_notes` (
     `rek_additional_notes_id` int(11) NOT NULL auto_increment,
     `rek_additional_notes_pid` varchar(64) default NULL,
     `rek_additional_notes_xsdmf_id` int(11) default NULL,
      `rek_additional_notes` TEXT default NULL,
     PRIMARY KEY (`rek_additional_notes_id`),
     FULLTEXT KEY `rek_additional_notes` (`rek_additional_notes`),
     UNIQUE KEY `rek_additional_notes_pid` (`rek_additional_notes_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;