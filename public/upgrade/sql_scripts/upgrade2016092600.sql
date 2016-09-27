INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_145','core','145','Type of Data','','0','0','0','0','0','text','none','',NULL,'','varchar','1','','1','','0','','0','');

CREATE TABLE %TABLE_PREFIX%record_search_key_type_of_data (
     rek_type_of_data_id int(11) NOT NULL auto_increment,
     rek_type_of_data_pid varchar(64) NOT NULL,
     rek_type_of_data_xsdmf_id int(11) NOT NULL,
      rek_type_of_data_order int(11) default 1,
      rek_type_of_data varchar(255) default NULL,
     PRIMARY KEY (rek_type_of_data_id),
     KEY rek_type_of_data (rek_type_of_data),
     KEY rek_type_of_data_pid (rek_type_of_data_pid),
     UNIQUE KEY unique_constraint_pid_order (rek_type_of_data_pid, rek_type_of_data_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%record_search_key_type_of_data__shadow (
     rek_type_of_data_id int(11) NOT NULL,
     rek_type_of_data_stamp datetime,
      rek_type_of_data_pid varchar(64) NOT NULL,
     rek_type_of_data_xsdmf_id int(11) NOT NULL,
      rek_type_of_data_order int(11) default 1,
      rek_type_of_data varchar(255) default NULL,
      PRIMARY KEY (rek_type_of_data_pid,rek_type_of_data_stamp,rek_type_of_data_order),
      KEY rek_type_of_data (rek_type_of_data),
     KEY rek_type_of_data_pid (rek_type_of_data_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;