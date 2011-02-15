ALTER TABLE %TABLE_PREFIX%search_key
		ADD COLUMN sek_cardinality tinyint(1) default 0;

UPDATE %TABLE_PREFIX%search_key
	SET sek_cardinality = 1
	WHERE sek_relationship = 1;

UPDATE %TABLE_PREFIX%search_key
	SET sek_cardinality = 0
	WHERE sek_relationship = 0;

UPDATE %TABLE_PREFIX%search_key
SET sek_cardinality = 0
WHERE sek_id IN ('UQ_2','UQ_3','UQ_4','UQ_5','core_29','core_32','core_33','core_34','core_35','core_36','core_37','core_39','core_40','core_41','core_42','core_43','core_44','core_45','core_46','core_47','core_48','core_49','core_50','core_51','core_52','core_53','core_54','core_55','core_56','core_57','core_58','core_59','core_60','core_61','core_62','core_63','core_64','core_65','core_66','core_67','core_68','core_69','core_70','core_71','core_72','core_78','core_79');

		
ALTER TABLE %TABLE_PREFIX%record_search_key_author		
		ADD COLUMN rek_author_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_subject
		ADD COLUMN rek_subject_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_file_attachment_name
		ADD COLUMN rek_file_attachment_name_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_file_attachment_content
		ADD COLUMN rek_file_attachment_content_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_ismemberof
		ADD COLUMN rek_ismemberof_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_keywords
		ADD COLUMN rek_keywords_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_notes
		ADD COLUMN rek_notes_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_xsd_display_option
		ADD COLUMN rek_xsd_display_option_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_research_program
		ADD COLUMN rek_research_program_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_isderivationof
		ADD COLUMN rek_isderivationof_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_assigned_user_id
		ADD COLUMN rek_assigned_user_id_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_assigned_group_id
		ADD COLUMN rek_assigned_group_id_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_isdatacomponentof
		ADD COLUMN rek_isdatacomponentof_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_isannotationof
		ADD COLUMN rek_isannotationof_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_author_id
		ADD COLUMN rek_author_id_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_alternative_title
		ADD COLUMN rek_alternative_title_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_contributor
		ADD COLUMN rek_contributor_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_contributor_id		
		ADD COLUMN rek_contributor_id_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_identifier		
		ADD COLUMN rek_identifier_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_link	
		ADD COLUMN rek_link_order int(11) default 1;
ALTER TABLE %TABLE_PREFIX%record_search_key_link_description
		ADD COLUMN rek_link_description_order int(11) default 1;


ALTER TABLE %TABLE_PREFIX%record_search_key_author	
		ADD INDEX rek_author_order (rek_author_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_subject
		ADD INDEX rek_subject_order (rek_subject_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_file_attachment_name
		ADD INDEX rek_file_attachment_name_order (rek_file_attachment_name_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_file_attachment_content
		ADD INDEX rek_file_attachment_content_order (rek_file_attachment_content_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_ismemberof
		ADD INDEX rek_ismemberof_order (rek_ismemberof_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_keywords
		ADD INDEX rek_keywords_order (rek_keywords_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_notes
		ADD INDEX rek_notes_order (rek_notes_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_xsd_display_option
		ADD INDEX rek_xsd_display_option_order (rek_xsd_display_option_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_research_program
		ADD INDEX rek_research_program_order (rek_research_program_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_isderivationof
		ADD INDEX rek_isderivationof_order (rek_isderivationof_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_assigned_user_id
		ADD INDEX rek_assigned_user_id_order (rek_assigned_user_id_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_assigned_group_id
		ADD INDEX rek_assigned_group_id_order (rek_assigned_group_id_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_isdatacomponentof
		ADD INDEX rek_isdatacomponentof_order (rek_isdatacomponentof_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_isannotationof
		ADD INDEX rek_isannotationof_order (rek_isannotationof_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_author_id
		ADD INDEX rek_author_id_order (rek_author_id_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_alternative_title
		ADD INDEX rek_alternative_title_order (rek_alternative_title_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_contributor
		ADD INDEX rek_contributor_order (rek_contributor_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_contributor_id		
		ADD INDEX rek_contributor_id_order (rek_contributor_id_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_identifier		
		ADD INDEX rek_identifier_order (rek_identifier_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_link	
		ADD INDEX rek_link_order (rek_link_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_link_description
		ADD INDEX rek_link_description_order (rek_link_description_order);




