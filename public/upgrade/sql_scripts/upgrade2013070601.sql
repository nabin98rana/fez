CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isdatasetof (
     rek_isdatasetof_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_isdatasetof_pid VARCHAR(64) DEFAULT NULL,
     rek_isdatasetof_xsdmf_id INT(11) DEFAULT NULL,
     rek_isdatasetof int(11) DEFAULT NULL,
     PRIMARY KEY (rek_isdatasetof_id),
     KEY rek_isdatasetof (rek_isdatasetof),
     KEY rek_isdatasetof_pid (rek_isdatasetof_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isdatasetof__shadow (
     rek_isdatasetof_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_isdatasetof_stamp DATETIME,
     rek_isdatasetof_pid VARCHAR(64) DEFAULT NULL,
     rek_isdatasetof_xsdmf_id INT(11) DEFAULT NULL,
     rek_isdatasetof int(11) DEFAULT NULL,
     PRIMARY KEY (rek_isdatasetof_id),
     KEY rek_isdatasetof (rek_isdatasetof),
     KEY rek_isdatasetof_pid (rek_isdatasetof_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_122','core','122','isDatasetOf','','0','0','0','0','0','text','none','',450779,'Record::getTitleFromIndex','varchar','1','','0','','0','','0','');
