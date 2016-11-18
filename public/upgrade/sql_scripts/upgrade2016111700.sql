INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_146','core','146','Architect Name','','0','0','0','0','0','text','none','',NULL,'','varchar','1','','1','','0','','0','');


INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_147','core','147','Architect ID','','0','0','0','0','0','text','none','',NULL,'Author::getFullName','int','1','','1','','0','','0','');

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_architect_name (
     rek_architect_name_id int(11) NOT NULL auto_increment,
     rek_architect_name_pid varchar(64) NOT NULL,
     rek_architect_name_xsdmf_id int(11) NOT NULL,
     rek_architect_name_order int(11) default 1,
     rek_architect_name varchar(255) default NULL,
     PRIMARY KEY (rek_architect_name_id),
     KEY rek_architect_name (rek_architect_name),
     KEY rek_architect_name_pid (rek_architect_name_pid),
     UNIQUE KEY unique_constraint_pid_order (rek_architect_name_pid, rek_architect_name_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_architect_name__shadow (
     rek_architect_name_id int(11) NOT NULL,
     rek_architect_name_stamp datetime,
     rek_architect_name_pid varchar(64) NOT NULL,
     rek_architect_name_xsdmf_id int(11) NOT NULL,
     rek_architect_name_order int(11) default 1,
     rek_architect_name varchar(255) default NULL,
     PRIMARY KEY (rek_architect_name_pid,rek_architect_name_stamp,rek_architect_name_order),
     KEY rek_architect_name (rek_architect_name),
     KEY rek_architect_name_pid (rek_architect_name_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%record_search_key_architect_id (
     rek_architect_id_id int(11) NOT NULL auto_increment,
     rek_architect_id_pid varchar(64) NOT NULL,
     rek_architect_id_xsdmf_id int(11) NOT NULL,
     rek_architect_id_order int(11) default 1,
     rek_architect_id int(11) default NULL,
     PRIMARY KEY (rek_architect_id_id),
     KEY rek_architect_id (rek_architect_id),
     KEY rek_architect_id_pid (rek_architect_id_pid),
     UNIQUE KEY unique_constraint_pid_order (rek_architect_id_pid, rek_architect_id_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%record_search_key_architect_id__shadow (
     rek_architect_id_id int(11) NOT NULL,
     rek_architect_id_stamp datetime,
     rek_architect_id_pid varchar(64) NOT NULL,
     rek_architect_id_xsdmf_id int(11) NOT NULL,
     rek_architect_id_order int(11) default 1,
     rek_architect_id int(11) default NULL,
     PRIMARY KEY (rek_architect_id_pid,rek_architect_id_stamp,rek_architect_id_order),
     KEY rek_architect_id (rek_architect_id),
     KEY rek_architect_id_pid (rek_architect_id_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;