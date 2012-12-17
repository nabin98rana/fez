CREATE TABLE %TABLE_PREFIX%record_search_key_supervisor (
  rek_supervisor_id int(11) NOT NULL AUTO_INCREMENT,
  rek_supervisor_pid varchar(64) DEFAULT NULL,
  rek_supervisor_xsdmf_id int(11) DEFAULT NULL,
  rek_supervisor varchar(255) DEFAULT NULL,
  rek_supervisor_order int(11) DEFAULT '1',
  PRIMARY KEY (rek_supervisor_id),
  UNIQUE KEY unique_constraint (rek_supervisor_pid,rek_supervisor,rek_supervisor_order),
  UNIQUE KEY unique_constraint_pid_order (rek_supervisor_pid,rek_supervisor_order),
  KEY rek_supervisor_pid (rek_supervisor_pid),
  KEY rek_supervisor (rek_supervisor),
  KEY rek_supervisor_order (rek_supervisor_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%record_search_key_supervisor__shadow (
  rek_supervisor_id int(11) NOT NULL AUTO_INCREMENT,
  rek_supervisor_pid varchar(64) DEFAULT NULL,
  rek_supervisor_xsdmf_id int(11) DEFAULT NULL,
  rek_supervisor varchar(255) DEFAULT NULL,
  rek_supervisor_order int(11) DEFAULT '1',
  rek_supervisor_stamp datetime DEFAULT NULL,
  PRIMARY KEY (rek_supervisor_id),
  UNIQUE KEY unique_constraint (rek_supervisor_pid,rek_supervisor_order,rek_supervisor_stamp),
  KEY rek_supervisor_pid (rek_supervisor_pid),
  KEY rek_supervisor (rek_supervisor),
  KEY rek_supervisor_order (rek_supervisor_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%record_search_key_supervisor_id (
  rek_supervisor_id_id int(11) NOT NULL AUTO_INCREMENT,
  rek_supervisor_id_pid varchar(64) DEFAULT NULL,
  rek_supervisor_id_xsdmf_id int(11) DEFAULT NULL,
  rek_supervisor_id int(11) DEFAULT NULL,
  rek_supervisor_id_order int(11) DEFAULT '1',
  PRIMARY KEY (rek_supervisor_id_id),
  UNIQUE KEY unique_constraint (rek_supervisor_id_pid,rek_supervisor_id,rek_supervisor_id_order),
  UNIQUE KEY unique_constraint_pid_order (rek_supervisor_id_pid,rek_supervisor_id_order),
  KEY rek_supervisor_id_pid (rek_supervisor_id_pid),
  KEY rek_supervisor_id (rek_supervisor_id),
  KEY rek_supervisor_id_order (rek_supervisor_id_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%record_search_key_supervisor_id__shadow (
  rek_supervisor_id_id int(11) NOT NULL AUTO_INCREMENT,
  rek_supervisor_id_pid varchar(64) DEFAULT NULL,
  rek_supervisor_id_xsdmf_id int(11) DEFAULT NULL,
  rek_supervisor_id int(11) DEFAULT NULL,
  rek_supervisor_id_order int(11) DEFAULT '1',
  rek_supervisor_id_stamp datetime DEFAULT NULL,
  PRIMARY KEY (rek_supervisor_id_id),
  UNIQUE KEY unique_constraint (rek_supervisor_id_pid,rek_supervisor_id_order,rek_supervisor_id_stamp),
  KEY rek_supervisor_id_pid (rek_supervisor_id_pid),
  KEY rek_supervisor_id (rek_supervisor_id),
  KEY rek_supervisor_id_order (rek_supervisor_id_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_107','core','107','Supervisor','','0','0','0','0','0','text','none','','','','varchar','1','','1','','0','','0','');

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_108','core','108','Supervisor ID','','0','0','0','0','0','text','none','','','Author::getFullName','int','1','','1','','0','','0','');

