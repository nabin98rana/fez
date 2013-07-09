CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_grant_id (
     rek_grant_id_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_grant_id_pid VARCHAR(64) DEFAULT NULL,
     rek_grant_id_xsdmf_id INT(11) DEFAULT NULL,
     rek_grant_id int(11) DEFAULT NULL,
     PRIMARY KEY (rek_grant_id_id),
     KEY rek_grant_id (rek_grant_id),
     KEY rek_grant_id_pid (rek_grant_id_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_grant_id__shadow (
     rek_grant_id_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_grant_id_stamp DATETIME,
     rek_grant_id_pid VARCHAR(64) DEFAULT NULL,
     rek_grant_id_xsdmf_id INT(11) DEFAULT NULL,
     rek_grant_id varchar(255) DEFAULT NULL,
     PRIMARY KEY (rek_grant_id_id),
     KEY rek_grant_id (rek_grant_id),
     KEY rek_grant_id_pid (rek_grant_id_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_115','core','115','Grant ID','','0','0','0','0','0','text','none','',450779,'','varchar','1','','0','','0','','0','');

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_funding_body (
     rek_funding_body_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_funding_body_pid VARCHAR(64) DEFAULT NULL,
     rek_funding_body_xsdmf_id INT(11) DEFAULT NULL,
     rek_funding_body varchar(255) DEFAULT NULL,
     PRIMARY KEY (rek_funding_body_id),
     KEY rek_funding_body (rek_funding_body),
     KEY rek_funding_body_pid (rek_funding_body_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_funding_body__shadow (
     rek_funding_body_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_funding_body_stamp DATETIME,
     rek_funding_body_pid VARCHAR(64) DEFAULT NULL,
     rek_funding_body_xsdmf_id INT(11) DEFAULT NULL,
     rek_funding_body varchar(255) DEFAULT NULL,
     PRIMARY KEY (rek_funding_body_id),
     KEY rek_funding_body (rek_funding_body),
     KEY rek_funding_body_pid (rek_funding_body_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_116','core','116','Funding Body','','0','0','0','0','0','text','none','',450779,'','varchar','1','','0','','0','','0','');

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_description_of_resource (
     rek_description_of_resource_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_description_of_resource_pid VARCHAR(64) DEFAULT NULL,
     rek_description_of_resource_xsdmf_id INT(11) DEFAULT NULL,
     rek_description_of_resource TEXT DEFAULT NULL,
     PRIMARY KEY (rek_description_of_resource_id),
     KEY rek_description_of_resource (rek_description_of_resource),
     KEY rek_description_of_resource_pid (rek_description_of_resource_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_description_of_resource__shadow (
     rek_description_of_resource_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_description_of_resource_stamp DATETIME,
     rek_description_of_resource_pid VARCHAR(64) DEFAULT NULL,
     rek_description_of_resource_xsdmf_id INT(11) DEFAULT NULL,
     rek_description_of_resource TEXT DEFAULT NULL,
     PRIMARY KEY (rek_description_of_resource_id),
     KEY rek_description_of_resource (rek_description_of_resource),
     KEY rek_description_of_resource_pid (rek_description_of_resource_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_117','core','117','Description of Resource','','0','0','0','0','0','text','none','',450779,'','text','1','','0','','0','','0','');

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_software_required (
     rek_software_required_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_software_required_pid VARCHAR(64) DEFAULT NULL,
     rek_software_required_xsdmf_id INT(11) DEFAULT NULL,
     rek_software_required varchar(255) DEFAULT NULL,
     PRIMARY KEY (rek_software_required_id),
     KEY rek_software_required (rek_software_required),
     KEY rek_software_required_pid (rek_software_required_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_software_required__shadow (
     rek_software_required_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_software_required_stamp DATETIME,
     rek_software_required_pid VARCHAR(64) DEFAULT NULL,
     rek_software_required_xsdmf_id INT(11) DEFAULT NULL,
     rek_software_required varchar(255) DEFAULT NULL,
     PRIMARY KEY (rek_software_required_id),
     KEY rek_software_required (rek_software_required),
     KEY rek_software_required_pid (rek_software_required_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_118','core','118','Software Required','','0','0','0','0','0','text','none','',450779,'','varchar','1','','0','','0','','0','');

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_project_description (
     rek_project_description_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_project_description_pid VARCHAR(64) DEFAULT NULL,
     rek_project_description_xsdmf_id INT(11) DEFAULT NULL,
     rek_project_description TEXT DEFAULT NULL,
     PRIMARY KEY (rek_project_description_id),
     KEY rek_project_description (rek_project_description),
     KEY rek_project_description_pid (rek_project_description_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_project_description__shadow (
     rek_project_description_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_project_description_stamp DATETIME,
     rek_project_description_pid VARCHAR(64) DEFAULT NULL,
     rek_project_description_xsdmf_id INT(11) DEFAULT NULL,
     rek_project_description TEXT DEFAULT NULL,
     PRIMARY KEY (rek_project_description_id),
     KEY rek_project_description (rek_project_description),
     KEY rek_project_description_pid (rek_project_description_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_119','core','119','Project Description','','0','0','0','0','0','text','none','',450779,'','text','1','','0','','0','','0','');

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_project_name (
     rek_project_name_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_project_name_pid VARCHAR(64) DEFAULT NULL,
     rek_project_name_xsdmf_id INT(11) DEFAULT NULL,
     rek_project_name varchar(255) DEFAULT NULL,
     PRIMARY KEY (rek_project_name_id),
     KEY rek_project_name (rek_project_name),
     KEY rek_project_name_pid (rek_project_name_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_project_name__shadow (
     rek_project_name_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_project_name_stamp DATETIME,
     rek_project_name_pid VARCHAR(64) DEFAULT NULL,
     rek_project_name_xsdmf_id INT(11) DEFAULT NULL,
     rek_project_name varchar(255) DEFAULT NULL,
     PRIMARY KEY (rek_project_name_id),
     KEY rek_project_name (rek_project_name),
     KEY rek_project_name_pid (rek_project_name_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_120','core','120','Project Name','','0','0','0','0','0','text','none','',450779,'','varchar','1','','0','','0','','0','');

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_project_id (
     rek_project_id_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_project_id_pid VARCHAR(64) DEFAULT NULL,
     rek_project_id_xsdmf_id INT(11) DEFAULT NULL,
     rek_project_id varchar(255) DEFAULT NULL,
     PRIMARY KEY (rek_project_id_id),
     KEY rek_project_id (rek_project_id),
     KEY rek_project_id_pid (rek_project_id_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_project_id__shadow (
     rek_project_id_id INT(11) NOT NULL AUTO_INCREMENT,
     rek_project_id_stamp DATETIME,
     rek_project_id_pid VARCHAR(64) DEFAULT NULL,
     rek_project_id_xsdmf_id INT(11) DEFAULT NULL,
     rek_project_id varchar(255) DEFAULT NULL,
     PRIMARY KEY (rek_project_id_id),
     KEY rek_project_id (rek_project_id),
     KEY rek_project_id_pid (rek_project_id_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_121','core','121','Project ID','','0','0','0','0','0','text','none','',450779,'','varchar','1','','0','','0','','0','');