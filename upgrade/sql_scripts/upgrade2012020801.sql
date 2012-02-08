-- add search keys used by Data Collection doc type (ANDS/RIF-CS). 
-- ****NOTE: core numbers will need updating before moving to trunk

-- add search keys for 1->1 keys

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_92','core','92','ANDS Collection Type','','0','0','0','0','0','text','none','','','','varchar','0','','0','','0','','0','');
	
INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_93','core','93','Start Date','','0','0','0','0','0','date','none','','','','date','0','','0','','0','','0','');

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_94','core','94','End Date','','0','0','0','0','0','date','none','','','','date','0','','0','','0','','0','');

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_95','core','95','Access Conditions','','0','0','0','0','0','text','none','','','','varchar','0','','0','','0','','0','');
	
INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_96','core','96','Extent','','0','0','0','0','0','text','none','','','','varchar','0','','0','','0','','0','');	

-- add search keys for 1->M keys

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_97','core','97','Contact Details Email','','0','0','0','0','0','text','none','','','','varchar','1','','1','','0','','0','');

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_98','core','98','Contact Details Physical','','0','0','0','0','0','text','none','','','','varchar','1','','1','','0','','0','');	

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_99','core','99','Library of Congress Subject Heading','','0','0','0','0','0','text','none','','','','varchar','1','DC.Subject','1','','0','','0','');	

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_100','core','100','Coverage Period','','0','0','0','0','0','text','none','','','','varchar','1','DC.Subject','1','','0','','0','');	
	
INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_101','core','101','Geographic Area','','0','0','0','0','0','text','none','','','','varchar','1','DC.Subject','1','','0','','0','');	

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_102','core','102','Geographic Coordinates','','0','0','0','0','0','text','none','','','','varchar','1','DC.Subject','1','','0','','0','');

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_103','core','103','Author Role','','0','0','0','0','0','text','none','','','','varchar','1','','1','','0','','0','');
	
INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_104','core','104','Contributor Role','','0','0','0','0','0','text','none','','','','varchar','1','','1','','0','','0','');	

INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_105','core','105','Org ID','','0','0','0','0','0','text','none','','','','varchar','1','','1','','0','','0','');	
	
INSERT IGNORE INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) 
	VALUES ('core_106','core','106','Org Role','','0','0','0','0','0','text','none','','','','varchar','1','','1','','0','','0','');	
	
-- add tables that the search keys will use

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_contact_details_email (               
 rek_contact_details_email_id int(11) NOT NULL auto_increment,          
 rek_contact_details_email_pid varchar(64) default NULL,                
 rek_contact_details_email_xsdmf_id int(11) default NULL,               
 rek_contact_details_email_order int(11) default '1',                   
 rek_contact_details_email varchar(255) default NULL,                   
 PRIMARY KEY  (rek_contact_details_email_id)
); 

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_contact_details_physical (               
 rek_contact_details_physical_id int(11) NOT NULL auto_increment,          
 rek_contact_details_physical_pid varchar(64) default NULL,                
 rek_contact_details_physical_xsdmf_id int(11) default NULL,               
 rek_contact_details_physical_order int(11) default '1',                   
 rek_contact_details_physical varchar(255) default NULL,                   
 PRIMARY KEY  (rek_contact_details_physical_id),                           
); 

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_library_of_congress_subject_heading (               
 rek_library_of_congress_subject_heading_id int(11) NOT NULL auto_increment,          
 rek_library_of_congress_subject_heading_pid varchar(64) default NULL,                
 rek_library_of_congress_subject_heading_xsdmf_id int(11) default NULL,               
 rek_library_of_congress_subject_heading_order int(11) default '1',                   
 rek_library_of_congress_subject_heading varchar(255) default NULL,                   
 PRIMARY KEY  (rek_library_of_congress_subject_heading_id),                           
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_coverage_period (               
 rek_coverage_period_id int(11) NOT NULL auto_increment,          
 rek_coverage_period_pid varchar(64) default NULL,                
 rek_coverage_period_xsdmf_id int(11) default NULL,               
 rek_coverage_period_order int(11) default '1',                   
 rek_coverage_period varchar(255) default NULL,                   
 PRIMARY KEY  (rek_coverage_period_id),                           
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_geographic_area (               
 rek_geographic_area_id int(11) NOT NULL auto_increment,          
 rek_geographic_area_pid varchar(64) default NULL,                
 rek_geographic_area_xsdmf_id int(11) default NULL,               
 rek_geographic_area_order int(11) default '1',                   
 rek_geographic_area varchar(255) default NULL,                   
 PRIMARY KEY  (rek_geographic_area_id),                           
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_geographic_coordinates (               
 rek_geographic_coordinates_id int(11) NOT NULL auto_increment,          
 rek_geographic_coordinates_pid varchar(64) default NULL,                
 rek_geographic_coordinates_xsdmf_id int(11) default NULL,               
 rek_geographic_coordinates_order int(11) default '1',                   
 rek_geographic_coordinates varchar(255) default NULL,                   
 PRIMARY KEY  (rek_geographic_coordinates_id),                           
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_author_role (               
 rek_author_role_id int(11) NOT NULL auto_increment,          
 rek_author_role_pid varchar(64) default NULL,                
 rek_author_role_xsdmf_id int(11) default NULL,               
 rek_author_role_order int(11) default '1',                   
 rek_author_role varchar(255) default NULL,                   
 PRIMARY KEY  (rek_author_role_id),                           
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_contributor_role (               
 rek_contributor_role_id int(11) NOT NULL auto_increment,          
 rek_contributor_role_pid varchar(64) default NULL,                
 rek_contributor_role_xsdmf_id int(11) default NULL,               
 rek_contributor_role_order int(11) default '1',                   
 rek_contributor_role varchar(255) default NULL,                   
 PRIMARY KEY  (rek_contributor_role_id),                           
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_org_id (               
 rek_org_id_id int(11) NOT NULL auto_increment,          
 rek_org_id_pid varchar(64) default NULL,                
 rek_org_id_xsdmf_id int(11) default NULL,               
 rek_org_id_order int(11) default '1',                   
 rek_org_id varchar(255) default NULL,                   
 PRIMARY KEY  (rek_org_id_id),                           
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_org_role (               
 rek_org_role_id int(11) NOT NULL auto_increment,          
 rek_org_role_pid varchar(64) default NULL,                
 rek_org_role_xsdmf_id int(11) default NULL,               
 rek_org_role_order int(11) default '1',                   
 rek_org_role varchar(255) default NULL,                   
 PRIMARY KEY  (rek_org_role_id),                           
);
  

-- add columns in frsk that the search keys will use
	
ALTER TABLE %TABLE_PREFIX%record_search_key
    ADD COLUMN rek_ands_collection_type_xsdmf_id int(11), 
    ADD COLUMN rek_ands_collection_type varchar(255);
	
ALTER TABLE %TABLE_PREFIX%record_search_key 
    ADD COLUMN rek_start_date_xsdmf_id int(11), 
    ADD COLUMN rek_start_date datetime;
	
ALTER TABLE %TABLE_PREFIX%record_search_key 
    ADD COLUMN rek_end_date_xsdmf_id int(11), 
    ADD COLUMN rek_end_date datetime;

ALTER TABLE %TABLE_PREFIX%record_search_key
    ADD COLUMN rek_access_conditions_xsdmf_id int(11), 
    ADD COLUMN rek_access_conditions varchar(255); 
	
ALTER TABLE %TABLE_PREFIX%record_search_key
    ADD COLUMN rek_extent_xsdmf_id int(11), 
    ADD COLUMN rek_extent varchar(255); 	

