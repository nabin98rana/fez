CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_license (
     rek_license_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_license_pid VARCHAR(64) DEFAULT NULL,
     rek_license_xsdmf_id INT(11) DEFAULT NULL,
     rek_license int(11) DEFAULT NULL,
     PRIMARY KEY (rek_license_id),
     KEY rek_license (rek_license),
     KEY rek_license_pid (rek_license_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_license__shadow (
     rek_license_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_license_stamp DATETIME,
     rek_license_pid VARCHAR(64) DEFAULT NULL,
     rek_license_xsdmf_id INT(11) DEFAULT NULL,
     rek_license int(11) DEFAULT NULL,
     PRIMARY KEY (rek_license_id),
     KEY rek_license (rek_license),
     KEY rek_license_pid (rek_license_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_112','core','112','License','','0','0','0','0','0','contvocab','none','',450779,'Controlled_Vocab::getTitle','int','1','','0','','0','','0','');
