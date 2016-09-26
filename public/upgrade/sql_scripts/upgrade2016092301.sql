INSERT IGNORE INTO %TABLE_PREFIX%search_key(sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_my%TABLE_PREFIX%visible,sek_order,sek_html_input,sek_%TABLE_PREFIX%variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_comment_function,sek_faceting,sek_derived_function,sek_lookup_id_function,sek_bulkchange) values ('core_144','core',144,'Copyright Agreement','','',0,0,0,0,'checkbox','none','',NULL,'','int',1,'',0,'',NULL,0,'','',0);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_copyright_agreement (
     rek_copyright_agreement_id int(11) NOT NULL auto_increment,
     rek_copyright_agreement_pid varchar(64) NOT NULL,
     rek_copyright_agreement_xsdmf_id int(11) NOT NULL,
      rek_copyright_agreement varchar(255) default NULL,
     PRIMARY KEY (rek_copyright_agreement_id),
     KEY rek_copyright_agreement (rek_copyright_agreement),
     KEY rek_copyright_agreement_pid (rek_copyright_agreement_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_copyright_agreement__shadow (
     rek_copyright_agreement_id int(11) NOT NULL,
     rek_copyright_agreement_stamp datetime,
      rek_copyright_agreement_pid varchar(64) NOT NULL,
     rek_copyright_agreement_xsdmf_id int(11) NOT NULL,
      rek_copyright_agreement varchar(255) default NULL,
      PRIMARY KEY (rek_copyright_agreement_pid,rek_copyright_agreement_stamp),
      KEY rek_copyright_agreement (rek_copyright_agreement),
     KEY rek_copyright_agreement_pid (rek_copyright_agreement_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_contact_details_physical__shadow (
     rek_contact_details_physical_id int(11) NOT NULL,
     rek_contact_details_physical_stamp datetime,
      rek_contact_details_physical_pid varchar(64) NOT NULL,
     rek_contact_details_physical_xsdmf_id int(11) NOT NULL,
      rek_contact_details_physical_order int(11) default 1,
      rek_contact_details_physical varchar(255) default NULL,
      PRIMARY KEY (rek_contact_details_physical_pid,rek_contact_details_physical_stamp,rek_contact_details_physical_order),
      KEY rek_contact_details_physical (rek_contact_details_physical),
     KEY rek_contact_details_physical_pid (rek_contact_details_physical_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_extent__shadow (
     rek_extent_id int(11) NOT NULL,
     rek_extent_stamp datetime,
      rek_extent_pid varchar(64) NOT NULL,
     rek_extent_xsdmf_id int(11) NOT NULL,
      rek_extent varchar(255) default NULL,
      PRIMARY KEY (rek_extent_pid,rek_extent_stamp),
      KEY rek_extent (rek_extent),
     KEY rek_extent_pid (rek_extent_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_file_description__shadow (
     rek_file_description_id int(11) NOT NULL,
     rek_file_description_stamp datetime,
      rek_file_description_pid varchar(64) NOT NULL,
     rek_file_description_xsdmf_id int(11) NOT NULL,
      rek_file_description_order int(11) default 1,
      rek_file_description text default NULL,
      PRIMARY KEY (rek_file_description_pid,rek_file_description_stamp,rek_file_description_order),
      FULLTEXT rek_file_description (rek_file_description),
     KEY rek_file_description_pid (rek_file_description_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS %TABLE_PREFIX%record_search_key_file_downloads;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_geographic_coordinates__shadow (
     rek_geographic_coordinates_id int(11) NOT NULL,
     rek_geographic_coordinates_stamp datetime,
      rek_geographic_coordinates_pid varchar(64) NOT NULL,
     rek_geographic_coordinates_xsdmf_id int(11) NOT NULL,
      rek_geographic_coordinates_order int(11) default 1,
      rek_geographic_coordinates varchar(255) default NULL,
      PRIMARY KEY (rek_geographic_coordinates_pid,rek_geographic_coordinates_stamp,rek_geographic_coordinates_order),
      KEY rek_geographic_coordinates (rek_geographic_coordinates),
     KEY rek_geographic_coordinates_pid (rek_geographic_coordinates_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_language_of_parent_title__shadow (
     rek_language_of_parent_title_id int(11) NOT NULL,
     rek_language_of_parent_title_stamp datetime,
      rek_language_of_parent_title_pid varchar(64) NOT NULL,
     rek_language_of_parent_title_xsdmf_id int(11) NOT NULL,
      rek_language_of_parent_title varchar(255) default NULL,
      PRIMARY KEY (rek_language_of_parent_title_pid,rek_language_of_parent_title_stamp),
      KEY rek_language_of_parent_title (rek_language_of_parent_title),
     KEY rek_language_of_parent_title_pid (rek_language_of_parent_title_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_loc_subject_heading__shadow (
     rek_loc_subject_heading_id int(11) NOT NULL,
     rek_loc_subject_heading_stamp datetime,
      rek_loc_subject_heading_pid varchar(64) NOT NULL,
     rek_loc_subject_heading_xsdmf_id int(11) NOT NULL,
      rek_loc_subject_heading_order int(11) default 1,
      rek_loc_subject_heading varchar(255) default NULL,
      PRIMARY KEY (rek_loc_subject_heading_pid,rek_loc_subject_heading_stamp,rek_loc_subject_heading_order),
      KEY rek_loc_subject_heading (rek_loc_subject_heading),
     KEY rek_loc_subject_heading_pid (rek_loc_subject_heading_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_org_id__shadow (
     rek_org_id_id int(11) NOT NULL,
     rek_org_id_stamp datetime,
      rek_org_id_pid varchar(64) NOT NULL,
     rek_org_id_xsdmf_id int(11) NOT NULL,
      rek_org_id_order int(11) default 1,
      rek_org_id varchar(255) default NULL,
      PRIMARY KEY (rek_org_id_pid,rek_org_id_stamp,rek_org_id_order),
      KEY rek_org_id (rek_org_id),
     KEY rek_org_id_pid (rek_org_id_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_org_role__shadow (
     rek_org_role_id int(11) NOT NULL,
     rek_org_role_stamp datetime,
      rek_org_role_pid varchar(64) NOT NULL,
     rek_org_role_xsdmf_id int(11) NOT NULL,
      rek_org_role_order int(11) default 1,
      rek_org_role varchar(255) default NULL,
      PRIMARY KEY (rek_org_role_pid,rek_org_role_stamp,rek_org_role_order),
      KEY rek_org_role (rek_org_role),
     KEY rek_org_role_pid (rek_org_role_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_contributor_role__shadow (
     rek_contributor_role_id int(11) NOT NULL,
     rek_contributor_role_stamp datetime,
      rek_contributor_role_pid varchar(64) NOT NULL,
     rek_contributor_role_xsdmf_id int(11) NOT NULL,
      rek_contributor_role_order int(11) default 1,
      rek_contributor_role varchar(255) default NULL,
      PRIMARY KEY (rek_contributor_role_pid,rek_contributor_role_stamp,rek_contributor_role_order),
      KEY rek_contributor_role (rek_contributor_role),
     KEY rek_contributor_role_pid (rek_contributor_role_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

REPLACE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,
sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,
sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,
sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,
sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_118','core','118','Software Required','','0','0','0','0','0',
	'text','none','',450779,'','varchar','1','','1','','0','','0','');

REPLACE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function)
	VALUES ('core_116','core','116','Funding Body','','0','0','0','0','0','text','none','',450779,'','varchar','1','','1','','0','','0','');


DROP TABLE IF EXISTS %TABLE_PREFIX%record_search_key_software_required;
DROP TABLE IF EXISTS %TABLE_PREFIX%record_search_key_software_required__shadow;
DROP TABLE IF EXISTS %TABLE_PREFIX%record_search_key_funding_body;
DROP TABLE IF EXISTS %TABLE_PREFIX%record_search_key_funding_body__shadow;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_funding_body (
     rek_funding_body_id int(11) NOT NULL auto_increment,
     rek_funding_body_pid varchar(64) NOT NULL,
     rek_funding_body_xsdmf_id int(11) NOT NULL,
      rek_funding_body_order int(11) default 1,
      rek_funding_body varchar(255) default NULL,
     PRIMARY KEY (rek_funding_body_id),
     KEY rek_funding_body (rek_funding_body),
     KEY rek_funding_body_pid (rek_funding_body_pid),
     UNIQUE KEY unique_constraint_pid_order (rek_funding_body_pid, rek_funding_body_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_funding_body__shadow (
     rek_funding_body_id int(11) NOT NULL,
     rek_funding_body_stamp datetime,
      rek_funding_body_pid varchar(64) NOT NULL,
     rek_funding_body_xsdmf_id int(11) NOT NULL,
      rek_funding_body_order int(11) default 1,
      rek_funding_body varchar(255) default NULL,
      PRIMARY KEY (rek_funding_body_pid,rek_funding_body_stamp,rek_funding_body_order),
      KEY rek_funding_body (rek_funding_body),
     KEY rek_funding_body_pid (rek_funding_body_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_software_required (
     rek_software_required_id int(11) NOT NULL auto_increment,
     rek_software_required_pid varchar(64) NOT NULL,
     rek_software_required_xsdmf_id int(11) NOT NULL,
      rek_software_required_order int(11) default 1,
      rek_software_required varchar(255) default NULL,
     PRIMARY KEY (rek_software_required_id),
     KEY rek_software_required (rek_software_required),
     KEY rek_software_required_pid (rek_software_required_pid),
     UNIQUE KEY unique_constraint_pid_order (rek_software_required_pid, rek_software_required_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_software_required__shadow (
     rek_software_required_id int(11) NOT NULL,
     rek_software_required_stamp datetime,
      rek_software_required_pid varchar(64) NOT NULL,
     rek_software_required_xsdmf_id int(11) NOT NULL,
      rek_software_required_order int(11) default 1,
      rek_software_required varchar(255) default NULL,
      PRIMARY KEY (rek_software_required_pid,rek_software_required_stamp,rek_software_required_order),
      KEY rek_software_required (rek_software_required),
     KEY rek_software_required_pid (rek_software_required_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS %TABLE_PREFIX%record_search_key_grant_id;

CREATE TABLE %TABLE_PREFIX%record_search_key_grant_id (
     rek_grant_id_id int(11) NOT NULL auto_increment,
     rek_grant_id_pid varchar(64) NOT NULL,
     rek_grant_id_xsdmf_id int(11) NOT NULL,
      rek_grant_id_order int(11) default 1,
      rek_grant_id varchar(255) default NULL,
     PRIMARY KEY (rek_grant_id_id),
     KEY rek_grant_id (rek_grant_id),
     KEY rek_grant_id_pid (rek_grant_id_pid),
     UNIQUE KEY unique_constraint_pid_order (rek_grant_id_pid, rek_grant_id_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;