




CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_datastream_index2_not_inherited__shadow (
  authdii_did varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  authdii_role int(11) unsigned NOT NULL DEFAULT '0',
  authdii_arg_id int(11) unsigned NOT NULL DEFAULT '0',
  authdii_edition_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (authdii_did,authdii_role,authdii_arg_id,authdii_edition_stamp),
  KEY authii_role_arg_id (authdii_role,authdii_arg_id),
  KEY authii_role (authdii_did,authdii_role),
  KEY authii_pid_arg_id (authdii_did,authdii_arg_id),
  KEY authii_pid (authdii_did),
  KEY authii_arg_id (authdii_arg_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%auth_index2_not_inherited__shadow (
  authii_pid varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  authii_role int(11) unsigned NOT NULL DEFAULT '0',
  authii_arg_id int(11) unsigned NOT NULL DEFAULT '0',
  authii_edition_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (authii_pid,authii_role,authii_arg_id,authii_edition_stamp),
  KEY authii_role_arg_id (authii_role,authii_arg_id),
  KEY authii_role (authii_pid,authii_role),
  KEY authii_pid_arg_id (authii_pid,authii_arg_id),
  KEY authii_pid (authii_pid),
  KEY authii_arg_id (authii_arg_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_abbreviated_title__shadow (
  rek_abbreviated_title_id int(11) NOT NULL,
  rek_abbreviated_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_abbreviated_title_xsdmf_id int(11) DEFAULT NULL,
  rek_abbreviated_title varchar(255) DEFAULT NULL,
  rek_abbreviated_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_abbreviated_title_pid,rek_abbreviated_title_stamp),
  KEY rek_abbreviated_title (rek_abbreviated_title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_access_conditions__shadow (
  rek_access_conditions_id int(11) NOT NULL,
  rek_access_conditions_pid varchar(64) NOT NULL DEFAULT '',
  rek_access_conditions_xsdmf_id int(11) DEFAULT NULL,
  rek_access_conditions varchar(255) DEFAULT NULL,
  rek_access_conditions_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_access_conditions_pid,rek_access_conditions_stamp),
  KEY rek_access_conditions (rek_access_conditions),
  KEY rek_access_conditions_pid (rek_access_conditions_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_acknowledgements__shadow (
  rek_acknowledgements_id int(11) NOT NULL,
  rek_acknowledgements_pid varchar(64) NOT NULL DEFAULT '',
  rek_acknowledgements_xsdmf_id int(11) DEFAULT NULL,
  rek_acknowledgements text,
  rek_acknowledgements_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_acknowledgements_pid,rek_acknowledgements_stamp),
  KEY rek_acknowledgements_pid (rek_acknowledgements_pid),
  FULLTEXT KEY rek_acknowledgements (rek_acknowledgements)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_additional_notes__shadow (
  rek_additional_notes_id int(11) NOT NULL,
  rek_additional_notes_pid varchar(64) NOT NULL DEFAULT '',
  rek_additional_notes_xsdmf_id int(11) DEFAULT NULL,
  rek_additional_notes text,
  rek_additional_notes_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_additional_notes_pid,rek_additional_notes_stamp),
  FULLTEXT KEY rek_additional_notes (rek_additional_notes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_adt_id__shadow (
  rek_adt_id_id int(11) NOT NULL,
  rek_adt_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_adt_id_xsdmf_id int(11) DEFAULT NULL,
  rek_adt_id varchar(255) DEFAULT NULL,
  rek_adt_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_adt_id_pid,rek_adt_id_stamp),
  KEY rek_adt_id (rek_adt_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_advisory_statement__shadow (
  rek_advisory_statement_id int(11) NOT NULL,
  rek_advisory_statement_pid varchar(64) NOT NULL DEFAULT '',
  rek_advisory_statement_xsdmf_id int(11) DEFAULT NULL,
  rek_advisory_statement text,
  rek_advisory_statement_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_advisory_statement_pid,rek_advisory_statement_stamp),
  KEY rek_advisory_statement_pid (rek_advisory_statement_pid),
  FULLTEXT KEY rek_advisory_statement (rek_advisory_statement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_alternate_genre__shadow (
  rek_alternate_genre_id int(11) NOT NULL,
  rek_alternate_genre_pid varchar(64) NOT NULL DEFAULT '',
  rek_alternate_genre_xsdmf_id int(11) DEFAULT NULL,
  rek_alternate_genre varchar(255) DEFAULT NULL,
  rek_alternate_genre_order int(11) NOT NULL DEFAULT '0',
  rek_alternate_genre_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_alternate_genre_pid,rek_alternate_genre_order,rek_alternate_genre_stamp),
  KEY rek_alternate_genre (rek_alternate_genre),
  KEY rek_alternate_genre_pid (rek_alternate_genre_pid),
  KEY rek_alternate_genre_order (rek_alternate_genre_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_alternative_title__shadow (
  rek_alternative_title_id int(11) NOT NULL,
  rek_alternative_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_alternative_title_xsdmf_id int(11) DEFAULT NULL,
  rek_alternative_title varchar(255) DEFAULT NULL,
  rek_alternative_title_order int(11) NOT NULL DEFAULT '1',
  rek_alternative_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_alternative_title_pid,rek_alternative_title_order,rek_alternative_title_stamp),
  KEY rek_alternative_title (rek_alternative_title),
  KEY rek_alternative_title_pid (rek_alternative_title_pid),
  KEY rek_alternative_title_order (rek_alternative_title_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_ands_collection_type__shadow (
  rek_ands_collection_type_id int(11) NOT NULL,
  rek_ands_collection_type_pid varchar(64) NOT NULL DEFAULT '',
  rek_ands_collection_type_xsdmf_id int(11) DEFAULT NULL,
  rek_ands_collection_type varchar(255) DEFAULT NULL,
  rek_ands_collection_type_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_ands_collection_type_pid,rek_ands_collection_type_stamp),
  KEY rek_ands_collection_type (rek_ands_collection_type),
  KEY rek_ands_collection_type_pid (rek_ands_collection_type_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_architectural_features__shadow (
  rek_architectural_features_id int(11) NOT NULL,
  rek_architectural_features_pid varchar(64) NOT NULL DEFAULT '',
  rek_architectural_features_xsdmf_id int(11) DEFAULT NULL,
  rek_architectural_features_order int(11) NOT NULL DEFAULT '1',
  rek_architectural_features varchar(255) DEFAULT NULL,
  rek_architectural_features_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_architectural_features_pid,rek_architectural_features_order,rek_architectural_features_stamp),
  KEY rek_architectural_features_pid (rek_architectural_features_pid),
  FULLTEXT KEY rek_architectural_features_ft (rek_architectural_features)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_assigned_group_id__shadow (
  rek_assigned_group_id_id int(11) NOT NULL,
  rek_assigned_group_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_assigned_group_id_xsdmf_id int(11) DEFAULT NULL,
  rek_assigned_group_id int(11) DEFAULT NULL,
  rek_assigned_group_id_order int(11) NOT NULL DEFAULT '1',
  rek_assigned_group_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_assigned_group_id_pid,rek_assigned_group_id_order,rek_assigned_group_id_stamp),
  KEY rek_assigned_group_id_pid (rek_assigned_group_id_pid),
  KEY rek_assigned_group_id (rek_assigned_group_id),
  KEY rek_assigned_group_id_order (rek_assigned_group_id_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_assigned_user_id__shadow (
  rek_assigned_user_id_id int(11) NOT NULL,
  rek_assigned_user_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_assigned_user_id_xsdmf_id int(11) DEFAULT NULL,
  rek_assigned_user_id int(11) DEFAULT NULL,
  rek_assigned_user_id_order int(11) NOT NULL DEFAULT '1',
  rek_assigned_user_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_assigned_user_id_pid,rek_assigned_user_id_order,rek_assigned_user_id_stamp),
  KEY rek_assigned_user_id_pid (rek_assigned_user_id_pid),
  KEY rek_assigned_user_id (rek_assigned_user_id),
  KEY rek_assigned_user_id_order (rek_assigned_user_id_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_author__shadow (
  rek_author_id int(11) NOT NULL,
  rek_author_pid varchar(64) NOT NULL DEFAULT '',
  rek_author_xsdmf_id int(11) DEFAULT NULL,
  rek_author varchar(255) DEFAULT NULL,
  rek_author_order int(11) NOT NULL DEFAULT '1',
  rek_author_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_author_pid,rek_author_order,rek_author_stamp),
  KEY rek_author_pid (rek_author_pid),
  KEY rek_author (rek_author),
  KEY rek_author_order (rek_author_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_author_count__shadow (
  rek_author_count_id int(11) NOT NULL,
  rek_author_count_pid varchar(64) NOT NULL DEFAULT '',
  rek_author_count_xsdmf_id int(11) DEFAULT NULL,
  rek_author_count int(11) DEFAULT NULL,
  rek_author_count_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_author_count_pid,rek_author_count_stamp),
  KEY rek_author_count (rek_author_count),
  KEY rek_author_count_pid (rek_author_count_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_author_id__shadow (
  rek_author_id_id int(11) NOT NULL,
  rek_author_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_author_id_xsdmf_id int(11) DEFAULT NULL,
  rek_author_id int(11) DEFAULT NULL,
  rek_author_id_order int(11) NOT NULL DEFAULT '1',
  rek_author_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_author_id_pid,rek_author_id_order,rek_author_id_stamp),
  KEY rek_author_id_pid (rek_author_id_pid),
  KEY rek_author_id (rek_author_id),
  KEY rek_author_id_order (rek_author_id_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_author_role__shadow (
  rek_author_role_id int(11) NOT NULL,
  rek_author_role_pid varchar(64) NOT NULL DEFAULT '',
  rek_author_role_xsdmf_id int(11) DEFAULT NULL,
  rek_author_role_order int(11) NOT NULL DEFAULT '1',
  rek_author_role varchar(255) DEFAULT NULL,
  rek_author_role_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_author_role_pid,rek_author_role_order,rek_author_role_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_book_title__shadow (
  rek_book_title_id int(11) NOT NULL,
  rek_book_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_book_title_xsdmf_id int(11) DEFAULT NULL,
  rek_book_title text,
  rek_book_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_book_title_pid,rek_book_title_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_building_materials__shadow (
  rek_building_materials_id int(11) NOT NULL,
  rek_building_materials_pid varchar(64) NOT NULL DEFAULT '',
  rek_building_materials_xsdmf_id int(11) DEFAULT NULL,
  rek_building_materials_order int(11) NOT NULL DEFAULT '1',
  rek_building_materials varchar(255) DEFAULT NULL,
  rek_building_materials_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_building_materials_pid,rek_building_materials_order,rek_building_materials_stamp),
  KEY rek_building_materials_pid (rek_building_materials_pid),
  FULLTEXT KEY rek_building_materials_ft (rek_building_materials)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_category__shadow (
  rek_category_id int(11) NOT NULL,
  rek_category_pid varchar(64) NOT NULL DEFAULT '',
  rek_category_xsdmf_id int(11) DEFAULT NULL,
  rek_category_order int(11) NOT NULL DEFAULT '1',
  rek_category text,
  rek_category_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_category_pid,rek_category_order,rek_category_stamp),
  KEY rek_category_pid (rek_category_pid),
  FULLTEXT KEY rek_category_ft (rek_category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_chapter_number__shadow (
  rek_chapter_number_id int(11) NOT NULL,
  rek_chapter_number_pid varchar(64) NOT NULL DEFAULT '',
  rek_chapter_number_xsdmf_id int(11) DEFAULT NULL,
  rek_chapter_number varchar(255) DEFAULT NULL,
  rek_chapter_number_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_chapter_number_pid,rek_chapter_number_stamp),
  KEY rek_chapter_number (rek_chapter_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_collection_year__shadow (
  rek_collection_year_id int(11) NOT NULL,
  rek_collection_year_pid varchar(64) NOT NULL DEFAULT '',
  rek_collection_year_xsdmf_id int(11) DEFAULT NULL,
  rek_collection_year datetime DEFAULT NULL,
  rek_collection_year_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_collection_year_pid,rek_collection_year_stamp),
  KEY rek_collection_year (rek_collection_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_condition__shadow (
  rek_condition_id int(11) NOT NULL,
  rek_condition_pid varchar(64) NOT NULL DEFAULT '',
  rek_condition_xsdmf_id int(11) DEFAULT NULL,
  rek_condition_order int(11) NOT NULL DEFAULT '1',
  rek_condition text,
  rek_condition_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_condition_pid,rek_condition_order,rek_condition_stamp),
  KEY rek_condition_pid (rek_condition_pid),
  FULLTEXT KEY rek_condition_ft (rek_condition)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_conference_dates__shadow (
  rek_conference_dates_id int(11) NOT NULL,
  rek_conference_dates_pid varchar(64) NOT NULL DEFAULT '',
  rek_conference_dates_xsdmf_id int(11) DEFAULT NULL,
  rek_conference_dates varchar(255) DEFAULT NULL,
  rek_conference_dates_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_conference_dates_pid,rek_conference_dates_stamp),
  KEY rek_conference_dates (rek_conference_dates)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_conference_id__shadow (
  rek_conference_id_id int(11) NOT NULL,
  rek_conference_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_conference_id_xsdmf_id int(11) DEFAULT NULL,
  rek_conference_id int(11) DEFAULT NULL,
  rek_conference_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_conference_id_pid,rek_conference_id_stamp),
  KEY rek_conference_id (rek_conference_id),
  KEY rek_conference_id_pid (rek_conference_id_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_conference_location__shadow (
  rek_conference_location_id int(11) NOT NULL,
  rek_conference_location_pid varchar(64) NOT NULL DEFAULT '',
  rek_conference_location_xsdmf_id int(11) DEFAULT NULL,
  rek_conference_location varchar(255) DEFAULT NULL,
  rek_conference_location_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_conference_location_pid,rek_conference_location_stamp),
  KEY rek_conference_location (rek_conference_location),
  FULLTEXT KEY rek_conference_location_ft (rek_conference_location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_conference_name__shadow (
  rek_conference_name_id int(11) NOT NULL,
  rek_conference_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_conference_name_xsdmf_id int(11) DEFAULT NULL,
  rek_conference_name text,
  rek_conference_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_conference_name_pid,rek_conference_name_stamp),
  FULLTEXT KEY rek_conference_name_ft (rek_conference_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_construction_date__shadow (
  rek_construction_date_id int(11) NOT NULL,
  rek_construction_date_pid varchar(64) NOT NULL DEFAULT '',
  rek_construction_date_xsdmf_id int(11) DEFAULT NULL,
  rek_construction_date varchar(255) DEFAULT NULL,
  rek_construction_date_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_construction_date_pid,rek_construction_date_stamp),
  KEY rek_construction_date (rek_construction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_contact_details_email__shadow (
  rek_contact_details_email_id int(11) NOT NULL,
  rek_contact_details_email_pid varchar(64) NOT NULL DEFAULT '',
  rek_contact_details_email_xsdmf_id int(11) DEFAULT NULL,
  rek_contact_details_email_order int(11) NOT NULL DEFAULT '1',
  rek_contact_details_email varchar(255) DEFAULT NULL,
  rek_contact_details_email_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_contact_details_email_pid,rek_contact_details_email_order,rek_contact_details_email_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_contributor__shadow (
  rek_contributor_id int(11) NOT NULL,
  rek_contributor_pid varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
  rek_contributor_xsdmf_id int(11) DEFAULT NULL,
  rek_contributor varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  rek_contributor_order int(11) NOT NULL DEFAULT '1',
  rek_contributor_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_contributor_pid,rek_contributor_order,rek_contributor_stamp),
  KEY rek_contributor_pid (rek_contributor_pid),
  KEY rek_contributor (rek_contributor),
  KEY rek_contributor_order (rek_contributor_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_contributor_id__shadow (
  rek_contributor_id_id int(11) NOT NULL,
  rek_contributor_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_contributor_id_xsdmf_id int(11) DEFAULT NULL,
  rek_contributor_id int(11) DEFAULT NULL,
  rek_contributor_id_order int(11) NOT NULL DEFAULT '1',
  rek_contributor_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_contributor_id_pid,rek_contributor_id_order,rek_contributor_id_stamp),
  KEY rek_contributor_id_pid (rek_contributor_id_pid),
  KEY rek_contributor_id (rek_contributor_id),
  KEY rek_contributor_id_order (rek_contributor_id_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_convener__shadow (
  rek_convener_id int(11) NOT NULL,
  rek_convener_pid varchar(64) NOT NULL DEFAULT '',
  rek_convener_xsdmf_id int(11) DEFAULT NULL,
  rek_convener varchar(255) DEFAULT NULL,
  rek_convener_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_convener_pid,rek_convener_stamp),
  KEY rek_convener (rek_convener)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_country_of_issue__shadow (
  rek_country_of_issue_id int(11) NOT NULL,
  rek_country_of_issue_pid varchar(64) NOT NULL DEFAULT '',
  rek_country_of_issue_xsdmf_id int(11) DEFAULT NULL,
  rek_country_of_issue varchar(255) DEFAULT NULL,
  rek_country_of_issue_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_country_of_issue_pid,rek_country_of_issue_stamp),
  KEY rek_country_of_issue (rek_country_of_issue),
  FULLTEXT KEY rek_country_of_issue_ft (rek_country_of_issue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_coverage_period__shadow (
  rek_coverage_period_id int(11) NOT NULL,
  rek_coverage_period_pid varchar(64) NOT NULL DEFAULT '',
  rek_coverage_period_xsdmf_id int(11) DEFAULT NULL,
  rek_coverage_period_order int(11) NOT NULL DEFAULT '1',
  rek_coverage_period varchar(255) DEFAULT NULL,
  rek_coverage_period_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_coverage_period_pid,rek_coverage_period_order,rek_coverage_period_stamp),
  KEY rek_coverage_period (rek_coverage_period),
  KEY rek_coverage_period_pid (rek_coverage_period_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_data_volume__shadow (
  rek_data_volume_id int(11) NOT NULL,
  rek_data_volume_pid varchar(64) NOT NULL DEFAULT '',
  rek_data_volume_xsdmf_id int(11) DEFAULT NULL,
  rek_data_volume varchar(255) DEFAULT NULL,
  rek_data_volume_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_data_volume_pid,rek_data_volume_stamp),
  KEY rek_data_volume (rek_data_volume),
  KEY rek_data_volume_pid (rek_data_volume_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_datastream_policy__shadow (
  rek_datastream_policy_id int(11) NOT NULL,
  rek_datastream_policy_pid varchar(64) NOT NULL DEFAULT '',
  rek_datastream_policy_xsdmf_id int(11) DEFAULT NULL,
  rek_datastream_policy varchar(255) DEFAULT NULL,
  rek_datastream_policy_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_datastream_policy_pid,rek_datastream_policy_stamp),
  KEY rek_datastream_policy (rek_datastream_policy),
  KEY rek_datastream_policy_pid (rek_datastream_policy_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_date_available__shadow (
  rek_date_available_id int(11) NOT NULL,
  rek_date_available_pid varchar(64) NOT NULL DEFAULT '',
  rek_date_available_xsdmf_id int(11) DEFAULT NULL,
  rek_date_available datetime DEFAULT NULL COMMENT 'Date Available',
  rek_date_available_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_date_available_pid,rek_date_available_stamp),
  KEY rek_date_available (rek_date_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_date_photo_taken__shadow (
  rek_date_photo_taken_id int(11) NOT NULL,
  rek_date_photo_taken_pid varchar(64) NOT NULL DEFAULT '',
  rek_date_photo_taken_xsdmf_id int(11) DEFAULT NULL,
  rek_date_photo_taken datetime DEFAULT NULL,
  rek_date_photo_taken_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_date_photo_taken_pid,rek_date_photo_taken_stamp),
  KEY rek_date_photo_taken (rek_date_photo_taken)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_date_recorded__shadow (
  rek_date_recorded_id int(11) NOT NULL,
  rek_date_recorded_pid varchar(64) NOT NULL DEFAULT '',
  rek_date_recorded_xsdmf_id int(11) DEFAULT NULL,
  rek_date_recorded datetime DEFAULT NULL,
  rek_date_recorded_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_date_recorded_pid,rek_date_recorded_stamp),
  KEY rek_date_recorded (rek_date_recorded),
  KEY rek_date_recorded_pid (rek_date_recorded_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_date_scanned__shadow (
  rek_date_scanned_id int(11) NOT NULL,
  rek_date_scanned_pid varchar(64) NOT NULL DEFAULT '',
  rek_date_scanned_xsdmf_id int(11) DEFAULT NULL,
  rek_date_scanned datetime DEFAULT NULL,
  rek_date_scanned_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_date_scanned_pid,rek_date_scanned_stamp),
  KEY rek_date_scanned (rek_date_scanned)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_description_of_resource__shadow (
  rek_description_of_resource_id int(11) NOT NULL,
  rek_description_of_resource_pid varchar(64) NOT NULL DEFAULT '',
  rek_description_of_resource_xsdmf_id int(11) DEFAULT NULL,
  rek_description_of_resource text,
  rek_description_of_resource_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_description_of_resource_pid,rek_description_of_resource_stamp),
  KEY rek_description_of_resource_pid (rek_description_of_resource_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_doi__shadow (
  rek_doi_id int(11) NOT NULL,
  rek_doi_pid varchar(64) NOT NULL DEFAULT '',
  rek_doi_xsdmf_id int(11) DEFAULT NULL,
  rek_doi varchar(255) DEFAULT NULL,
  rek_doi_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_doi_pid,rek_doi_stamp),
  KEY rek_doi (rek_doi),
  KEY rek_doi_pid (rek_doi_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_edition__shadow (
  rek_edition_id int(11) NOT NULL,
  rek_edition_pid varchar(64) NOT NULL DEFAULT '',
  rek_edition_xsdmf_id int(11) DEFAULT NULL,
  rek_edition varchar(255) DEFAULT NULL,
  rek_edition_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_edition_pid,rek_edition_stamp),
  KEY rek_edition (rek_edition)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_embase_id__shadow (
  rek_embase_id_id int(11) NOT NULL,
  rek_embase_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_embase_id_xsdmf_id int(11) DEFAULT NULL,
  rek_embase_id varchar(255) DEFAULT NULL,
  rek_embase_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_embase_id_pid,rek_embase_id_stamp),
  KEY rek_embase_id (rek_embase_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_end_date__shadow (
  rek_end_date_id int(11) NOT NULL,
  rek_end_date_pid varchar(64) NOT NULL DEFAULT '',
  rek_end_date_xsdmf_id int(11) DEFAULT NULL,
  rek_end_date datetime DEFAULT NULL,
  rek_end_date_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_end_date_pid,rek_end_date_stamp),
  KEY rek_end_date (rek_end_date),
  KEY rek_end_date_pid (rek_end_date_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_end_page__shadow (
  rek_end_page_id int(11) NOT NULL,
  rek_end_page_pid varchar(64) NOT NULL DEFAULT '',
  rek_end_page_xsdmf_id int(11) DEFAULT NULL,
  rek_end_page varchar(255) DEFAULT NULL,
  rek_end_page_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_end_page_pid,rek_end_page_stamp),
  KEY rek_end_page (rek_end_page)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_fields_of_research__shadow (
  rek_fields_of_research_id int(11) NOT NULL,
  rek_fields_of_research_pid varchar(64) NOT NULL DEFAULT '',
  rek_fields_of_research_xsdmf_id int(11) DEFAULT NULL,
  rek_fields_of_research int(11) DEFAULT NULL,
  rek_fields_of_research_order int(11) NOT NULL DEFAULT '1',
  rek_fields_of_research_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_fields_of_research_pid,rek_fields_of_research_order,rek_fields_of_research_stamp),
  KEY rek_fields_of_research_pid (rek_fields_of_research_pid),
  KEY rek_fields_of_research (rek_fields_of_research),
  KEY rek_fields_of_research_order (rek_fields_of_research_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_file_attachment_content__shadow (
  rek_file_attachment_content_id int(11) NOT NULL,
  rek_file_attachment_content_pid varchar(64) NOT NULL DEFAULT '',
  rek_file_attachment_content_xsdmf_id int(11) DEFAULT NULL,
  rek_file_attachment_content text,
  rek_file_attachment_content_order int(11) NOT NULL DEFAULT '1',
  rek_file_attachment_content_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_file_attachment_content_pid,rek_file_attachment_content_order,rek_file_attachment_content_stamp),
  KEY rek_file_attachment_content_order (rek_file_attachment_content_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_file_attachment_name__shadow (
  rek_file_attachment_name_id int(11) NOT NULL,
  rek_file_attachment_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_file_attachment_name_xsdmf_id int(11) DEFAULT NULL,
  rek_file_attachment_name varchar(255) DEFAULT NULL,
  rek_file_attachment_name_order int(11) NOT NULL DEFAULT '1',
  rek_file_attachment_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_file_attachment_name_pid,rek_file_attachment_name_order,rek_file_attachment_name_stamp),
  KEY rek_file_attachment_name_id (rek_file_attachment_name_pid),
  KEY rek_file_attachment_name (rek_file_attachment_name),
  KEY rek_file_attachment_name_order (rek_file_attachment_name_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_first_author_in_document_derived__shadow (
  rek_first_author_in_document_derived_id int(11) NOT NULL,
  rek_first_author_in_document_derived_pid varchar(64) NOT NULL DEFAULT '',
  rek_first_author_in_document_derived_xsdmf_id int(11) DEFAULT NULL,
  rek_first_author_in_document_derived varchar(255) DEFAULT NULL,
  rek_first_author_in_document_derived_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_first_author_in_document_derived_pid,rek_first_author_in_document_derived_stamp),
  KEY rek_first_author_in_document_derived (rek_first_author_in_document_derived)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_first_author_in_%TABLE_PREFIX%derived__shadow (
  rek_first_author_in_%TABLE_PREFIX%derived_id int(11) NOT NULL,
  rek_first_author_in_%TABLE_PREFIX%derived_pid varchar(64) NOT NULL DEFAULT '',
  rek_first_author_in_%TABLE_PREFIX%derived_xsdmf_id int(11) DEFAULT NULL,
  rek_first_author_in_%TABLE_PREFIX%derived varchar(255) DEFAULT NULL,
  rek_first_author_in_%TABLE_PREFIX%derived_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_first_author_in_%TABLE_PREFIX%derived_pid,rek_first_author_in_%TABLE_PREFIX%derived_stamp),
  KEY rek_first_author_in_%TABLE_PREFIX%derived (rek_first_author_in_%TABLE_PREFIX%derived)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_follow_up_flags__shadow (
  rek_follow_up_flags_id int(11) NOT NULL,
  rek_follow_up_flags_pid varchar(64) NOT NULL DEFAULT '',
  rek_follow_up_flags_xsdmf_id int(11) DEFAULT NULL,
  rek_follow_up_flags int(11) DEFAULT NULL,
  rek_follow_up_flags_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_follow_up_flags_pid,rek_follow_up_flags_stamp),
  KEY rek_follow_up_flags (rek_follow_up_flags)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_follow_up_flags_imu__shadow (
  rek_follow_up_flags_imu_id int(11) NOT NULL,
  rek_follow_up_flags_imu_pid varchar(64) NOT NULL DEFAULT '',
  rek_follow_up_flags_imu_xsdmf_id int(11) DEFAULT NULL,
  rek_follow_up_flags_imu int(11) DEFAULT NULL,
  rek_follow_up_flags_imu_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_follow_up_flags_imu_pid,rek_follow_up_flags_imu_stamp),
  KEY rek_follow_up_flags_imu (rek_follow_up_flags_imu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_funding_body__shadow (
  rek_funding_body_id int(11) NOT NULL,
  rek_funding_body_pid varchar(64) NOT NULL DEFAULT '',
  rek_funding_body_xsdmf_id int(11) DEFAULT NULL,
  rek_funding_body varchar(255) DEFAULT NULL,
  rek_funding_body_order int(11) NOT NULL DEFAULT '0',
  rek_funding_body_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_funding_body_pid,rek_funding_body_order,rek_funding_body_stamp),
  KEY rek_funding_body (rek_funding_body),
  KEY rek_funding_body_pid (rek_funding_body_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_geographic_area__shadow (
  rek_geographic_area_id int(11) NOT NULL,
  rek_geographic_area_pid varchar(64) NOT NULL DEFAULT '',
  rek_geographic_area_xsdmf_id int(11) DEFAULT NULL,
  rek_geographic_area_order int(11) NOT NULL DEFAULT '1',
  rek_geographic_area varchar(255) DEFAULT NULL,
  rek_geographic_area_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_geographic_area_pid,rek_geographic_area_order,rek_geographic_area_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_grant_id__shadow (
  rek_grant_id_id int(11) NOT NULL,
  rek_grant_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_grant_id_xsdmf_id int(11) DEFAULT NULL,
  rek_grant_id text,
  rek_grant_id_order int(11) NOT NULL DEFAULT '1',
  rek_grant_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_grant_id_pid,rek_grant_id_order,rek_grant_id_stamp),
  KEY rek_grant_id_pid (rek_grant_id_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_herdc_code__shadow (
  rek_herdc_code_id int(11) NOT NULL,
  rek_herdc_code_pid varchar(64) NOT NULL DEFAULT '',
  rek_herdc_code_xsdmf_id int(11) DEFAULT NULL,
  rek_herdc_code int(11) DEFAULT NULL,
  rek_herdc_code_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_herdc_code_pid,rek_herdc_code_stamp),
  KEY rek_herdc (rek_herdc_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_herdc_status__shadow (
  rek_herdc_status_id int(11) NOT NULL,
  rek_herdc_status_pid varchar(64) NOT NULL DEFAULT '',
  rek_herdc_status_xsdmf_id int(11) DEFAULT NULL,
  rek_herdc_status int(11) DEFAULT NULL,
  rek_herdc_status_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_herdc_status_pid,rek_herdc_status_stamp),
  KEY rek_herdc_status (rek_herdc_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_identifier__shadow (
  rek_identifier_id int(11) NOT NULL,
  rek_identifier_pid varchar(64) NOT NULL DEFAULT '',
  rek_identifier_xsdmf_id int(11) DEFAULT NULL,
  rek_identifier varchar(255) DEFAULT NULL,
  rek_identifier_order int(11) NOT NULL DEFAULT '1',
  rek_identifier_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_identifier_pid,rek_identifier_order,rek_identifier_stamp),
  KEY rek_identifier_pid (rek_identifier_pid),
  KEY rek_identifier (rek_identifier),
  KEY rek_identifier_order (rek_identifier_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_institutional_status__shadow (
  rek_institutional_status_id int(11) NOT NULL,
  rek_institutional_status_pid varchar(64) NOT NULL DEFAULT '',
  rek_institutional_status_xsdmf_id int(11) DEFAULT NULL,
  rek_institutional_status int(11) DEFAULT NULL,
  rek_institutional_status_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_institutional_status_pid,rek_institutional_status_stamp),
  KEY rek_institutional_status (rek_institutional_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_interior_features__shadow (
  rek_interior_features_id int(11) NOT NULL,
  rek_interior_features_pid varchar(64) NOT NULL DEFAULT '',
  rek_interior_features_xsdmf_id int(11) DEFAULT NULL,
  rek_interior_features_order int(11) NOT NULL DEFAULT '1',
  rek_interior_features varchar(255) DEFAULT NULL,
  rek_interior_features_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_interior_features_pid,rek_interior_features_order,rek_interior_features_stamp),
  KEY rek_interior_features_pid (rek_interior_features_pid),
  FULLTEXT KEY rek_interior_features_ft (rek_interior_features)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isannotationof__shadow (
  rek_isannotationof_id int(11) NOT NULL,
  rek_isannotationof_pid varchar(64) NOT NULL DEFAULT '',
  rek_isannotationof_xsdmf_id int(11) DEFAULT NULL,
  rek_isannotationof varchar(64) DEFAULT NULL,
  rek_isannotationof_order int(11) NOT NULL DEFAULT '1',
  rek_isannotationof_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_isannotationof_pid,rek_isannotationof_order,rek_isannotationof_stamp),
  KEY rek_isannotationof_order (rek_isannotationof_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isbn__shadow (
  rek_isbn_id int(11) NOT NULL,
  rek_isbn_pid varchar(64) NOT NULL DEFAULT '',
  rek_isbn_xsdmf_id int(11) DEFAULT NULL,
  rek_isbn varchar(255) DEFAULT NULL,
  rek_isbn_order int(11) NOT NULL DEFAULT '1',
  rek_isbn_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_isbn_pid,rek_isbn_order,rek_isbn_stamp),
  KEY isbn (rek_isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isdatacomponentof__shadow (
  rek_isdatacomponentof_id int(11) NOT NULL,
  rek_isdatacomponentof_pid varchar(64) NOT NULL DEFAULT '',
  rek_isdatacomponentof_xsdmf_id int(11) DEFAULT NULL,
  rek_isdatacomponentof varchar(64) DEFAULT NULL,
  rek_isdatacomponentof_order int(11) NOT NULL DEFAULT '1',
  rek_isdatacomponentof_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_isdatacomponentof_pid,rek_isdatacomponentof_order,rek_isdatacomponentof_stamp),
  KEY rek_isdatacomponentof_order (rek_isdatacomponentof_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isdatasetof__shadow (
  rek_isdatasetof_id int(11) NOT NULL,
  rek_isdatasetof_pid varchar(64) NOT NULL DEFAULT '',
  rek_isdatasetof_xsdmf_id int(11) DEFAULT NULL,
  rek_isdatasetof varchar(255) DEFAULT NULL,
  rek_isdatasetof_order int(11) NOT NULL DEFAULT '1',
  rek_isdatasetof_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_isdatasetof_pid,rek_isdatasetof_order,rek_isdatasetof_stamp),
  KEY rek_isdatasetof (rek_isdatasetof),
  KEY rek_isdatasetof_pid (rek_isdatasetof_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isderivationof__shadow (
  rek_isderivationof_id int(11) NOT NULL,
  rek_isderivationof_pid varchar(64) NOT NULL DEFAULT '',
  rek_isderivationof_xsdmf_id int(11) DEFAULT NULL,
  rek_isderivationof varchar(64) DEFAULT NULL,
  rek_isderivationof_order int(11) NOT NULL DEFAULT '1',
  rek_isderivationof_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_isderivationof_pid,rek_isderivationof_order,rek_isderivationof_stamp),
  KEY rek_isderivationof (rek_isderivationof),
  KEY rek_isderivationof_pid (rek_isderivationof_pid),
  KEY rek_isderivationof_order (rek_isderivationof_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_isi_loc__shadow (
  rek_isi_loc_id int(11) NOT NULL,
  rek_isi_loc_pid varchar(64) NOT NULL DEFAULT '',
  rek_isi_loc_xsdmf_id int(11) DEFAULT NULL,
  rek_isi_loc varchar(255) DEFAULT NULL,
  rek_isi_loc_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_isi_loc_pid,rek_isi_loc_stamp),
  KEY rek_isi_loc (rek_isi_loc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_ismemberof__shadow (
  rek_ismemberof_id int(11) NOT NULL,
  rek_ismemberof_pid varchar(64) NOT NULL DEFAULT '',
  rek_ismemberof_xsdmf_id int(11) DEFAULT NULL,
  rek_ismemberof varchar(64) DEFAULT NULL,
  rek_ismemberof_order int(11) NOT NULL DEFAULT '1',
  rek_ismemberof_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_ismemberof_pid,rek_ismemberof_order,rek_ismemberof_stamp),
  KEY rek_ismemberof_pid_value (rek_ismemberof_pid,rek_ismemberof),
  KEY rek_ismemberof_pid (rek_ismemberof),
  KEY rek_ismemberof_order (rek_ismemberof_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_issn__shadow (
  rek_issn_id int(11) NOT NULL,
  rek_issn_pid varchar(64) NOT NULL DEFAULT '',
  rek_issn_xsdmf_id int(11) DEFAULT NULL,
  rek_issn varchar(255) DEFAULT NULL,
  rek_issn_order int(11) NOT NULL DEFAULT '1',
  rek_issn_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_issn_pid,rek_issn_order,rek_issn_stamp),
  KEY rek_issn (rek_issn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_issue_number__shadow (
  rek_issue_number_id int(11) NOT NULL,
  rek_issue_number_pid varchar(64) NOT NULL DEFAULT '',
  rek_issue_number_xsdmf_id int(11) DEFAULT NULL,
  rek_issue_number varchar(255) DEFAULT NULL,
  rek_issue_number_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_issue_number_pid,rek_issue_number_stamp),
  KEY rek_issue_number (rek_issue_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_journal_name__shadow (
  rek_journal_name_id int(11) NOT NULL,
  rek_journal_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_journal_name_xsdmf_id int(11) DEFAULT NULL,
  rek_journal_name varchar(255) DEFAULT NULL,
  rek_journal_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_journal_name_pid,rek_journal_name_stamp),
  KEY rek_journal_name (rek_journal_name),
  FULLTEXT KEY rek_journal_name_ft (rek_journal_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_keywords__shadow (
  rek_keywords_id int(11) NOT NULL,
  rek_keywords_pid varchar(64) NOT NULL DEFAULT '',
  rek_keywords_xsdmf_id int(11) DEFAULT NULL,
  rek_keywords varchar(255) DEFAULT NULL,
  rek_keywords_order int(11) NOT NULL DEFAULT '1',
  rek_keywords_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_keywords_pid,rek_keywords_order,rek_keywords_stamp),
  KEY rek_keywords_pid (rek_keywords_pid),
  KEY rek_keywords (rek_keywords),
  KEY rek_keywords_order (rek_keywords_order),
  FULLTEXT KEY rek_keywords_ft (rek_keywords)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_language__shadow (
  rek_language_id int(11) NOT NULL,
  rek_language_pid varchar(64) NOT NULL DEFAULT '',
  rek_language_xsdmf_id int(11) DEFAULT NULL,
  rek_language varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  rek_language_order int(11) NOT NULL DEFAULT '1',
  rek_language_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_language_pid,rek_language_order,rek_language_stamp),
  KEY rek_language (rek_language),
  KEY rek_language_pid (rek_language_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_language_of_book_title__shadow (
  rek_language_of_book_title_id int(11) NOT NULL,
  rek_language_of_book_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_language_of_book_title_xsdmf_id int(11) DEFAULT NULL,
  rek_language_of_book_title_order int(11) NOT NULL DEFAULT '1',
  rek_language_of_book_title varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  rek_language_of_book_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_language_of_book_title_pid,rek_language_of_book_title_order,rek_language_of_book_title_stamp),
  KEY rek_language_of_book_title (rek_language_of_book_title),
  KEY rek_language_of_book_title_pid (rek_language_of_book_title_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_language_of_journal_name__shadow (
  rek_language_of_journal_name_id int(11) NOT NULL,
  rek_language_of_journal_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_language_of_journal_name_xsdmf_id int(11) DEFAULT NULL,
  rek_language_of_journal_name_order int(11) NOT NULL DEFAULT '1',
  rek_language_of_journal_name varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  rek_language_of_journal_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_language_of_journal_name_pid,rek_language_of_journal_name_order,rek_language_of_journal_name_stamp),
  KEY rek_language_of_journal_name (rek_language_of_journal_name),
  KEY rek_language_of_journal_name_pid (rek_language_of_journal_name_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_language_of_proceedings_title__shadow (
  rek_language_of_proceedings_title_id int(11) NOT NULL,
  rek_language_of_proceedings_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_language_of_proceedings_title_xsdmf_id int(11) DEFAULT NULL,
  rek_language_of_proceedings_title_order int(11) NOT NULL DEFAULT '1',
  rek_language_of_proceedings_title varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  rek_language_of_proceedings_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_language_of_proceedings_title_pid,rek_language_of_proceedings_title_order,rek_language_of_proceedings_title_stamp),
  KEY rek_language_of_proceedings_title (rek_language_of_proceedings_title),
  KEY rek_language_of_proceedings_title_pid (rek_language_of_proceedings_title_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_language_of_title__shadow (
  rek_language_of_title_id int(11) NOT NULL,
  rek_language_of_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_language_of_title_xsdmf_id int(11) DEFAULT NULL,
  rek_language_of_title varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  rek_language_of_title_order int(11) NOT NULL DEFAULT '1',
  rek_language_of_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_language_of_title_pid,rek_language_of_title_order,rek_language_of_title_stamp),
  KEY rek_language_of_title (rek_language_of_title),
  KEY rek_language_of_title_pid (rek_language_of_title_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_length__shadow (
  rek_length_id int(11) NOT NULL,
  rek_length_pid varchar(64) NOT NULL DEFAULT '',
  rek_length_xsdmf_id int(11) DEFAULT NULL,
  rek_length varchar(255) DEFAULT NULL,
  rek_length_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_length_pid,rek_length_stamp),
  KEY rek_length (rek_length),
  KEY rek_length_pid (rek_length_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_license__shadow (
  rek_license_id int(11) NOT NULL,
  rek_license_pid varchar(64) NOT NULL DEFAULT '',
  rek_license_xsdmf_id int(11) DEFAULT NULL,
  rek_license int(11) DEFAULT NULL,
  rek_license_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_license_pid,rek_license_stamp),
  KEY rek_license (rek_license),
  KEY rek_license_pid (rek_license_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_link__shadow (
  rek_link_id int(11) NOT NULL,
  rek_link_pid varchar(64) NOT NULL DEFAULT '',
  rek_link_xsdmf_id int(11) DEFAULT NULL,
  rek_link text,
  rek_link_order int(11) NOT NULL DEFAULT '1',
  rek_link_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_link_pid,rek_link_order,rek_link_stamp),
  KEY rek_link_pid (rek_link_pid),
  KEY rek_link_order (rek_link_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_link_description__shadow (
  rek_link_description_id int(11) NOT NULL,
  rek_link_description_pid varchar(64) NOT NULL DEFAULT '',
  rek_link_description_xsdmf_id int(11) DEFAULT NULL,
  rek_link_description text,
  rek_link_description_order int(11) NOT NULL DEFAULT '1',
  rek_link_description_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_link_description_pid,rek_link_description_order,rek_link_description_stamp),
  KEY rek_link_description_pid (rek_link_description_pid),
  KEY rek_link_description_order (rek_link_description_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_location__shadow (
  rek_location_id int(11) NOT NULL,
  rek_location_pid varchar(64) NOT NULL DEFAULT '',
  rek_location_xsdmf_id int(11) DEFAULT NULL,
  rek_location varchar(255) DEFAULT NULL,
  rek_location_order int(11) NOT NULL DEFAULT '0',
  rek_location_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_location_pid,rek_location_order,rek_location_stamp),
  KEY rek_location (rek_location),
  KEY rek_location_pid (rek_location_pid),
  FULLTEXT KEY rek_location_ft (rek_location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_na_explanation__shadow (
  rek_na_explanation_id int(11) NOT NULL,
  rek_na_explanation_pid varchar(64) NOT NULL DEFAULT '',
  rek_na_explanation_xsdmf_id int(11) DEFAULT NULL,
  rek_na_explanation text,
  rek_na_explanation_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_na_explanation_pid,rek_na_explanation_stamp),
  FULLTEXT KEY rek_na_explanation_ft (rek_na_explanation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_native_script_book_title__shadow (
  rek_native_script_book_title_id int(11) NOT NULL,
  rek_native_script_book_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_native_script_book_title_xsdmf_id int(11) DEFAULT NULL,
  rek_native_script_book_title varchar(255) DEFAULT NULL,
  rek_native_script_book_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_native_script_book_title_pid,rek_native_script_book_title_stamp),
  KEY rek_native_script_book_title (rek_native_script_book_title),
  KEY rek_native_script_book_title_pid (rek_native_script_book_title_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_native_script_conference_name__shadow (
  rek_native_script_conference_name_id int(11) NOT NULL,
  rek_native_script_conference_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_native_script_conference_name_xsdmf_id int(11) DEFAULT NULL,
  rek_native_script_conference_name varchar(255) DEFAULT NULL,
  rek_native_script_conference_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_native_script_conference_name_pid,rek_native_script_conference_name_stamp),
  KEY rek_native_script_conference_name (rek_native_script_conference_name),
  KEY rek_native_script_conference_name_pid (rek_native_script_conference_name_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_native_script_journal_name__shadow (
  rek_native_script_journal_name_id int(11) NOT NULL,
  rek_native_script_journal_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_native_script_journal_name_xsdmf_id int(11) DEFAULT NULL,
  rek_native_script_journal_name varchar(255) DEFAULT NULL,
  rek_native_script_journal_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_native_script_journal_name_pid,rek_native_script_journal_name_stamp),
  KEY rek_native_script_journal_name (rek_native_script_journal_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_native_script_proceedings_title__shadow (
  rek_native_script_proceedings_title_id int(11) NOT NULL,
  rek_native_script_proceedings_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_native_script_proceedings_title_xsdmf_id int(11) DEFAULT NULL,
  rek_native_script_proceedings_title varchar(255) DEFAULT NULL,
  rek_native_script_proceedings_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_native_script_proceedings_title_pid,rek_native_script_proceedings_title_stamp),
  KEY rek_native_script_proceedings_title (rek_native_script_proceedings_title),
  KEY rek_native_script_proceedings_title_pid (rek_native_script_proceedings_title_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_native_script_title__shadow (
  rek_native_script_title_id int(11) NOT NULL,
  rek_native_script_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_native_script_title_xsdmf_id int(11) DEFAULT NULL,
  rek_native_script_title varchar(255) DEFAULT NULL,
  rek_native_script_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_native_script_title_pid,rek_native_script_title_stamp),
  KEY rek_native_script_title (rek_native_script_title),
  KEY rek_native_script_title_pid (rek_native_script_title_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_newspaper__shadow (
  rek_newspaper_id int(11) NOT NULL,
  rek_newspaper_pid varchar(64) NOT NULL DEFAULT '',
  rek_newspaper_xsdmf_id int(11) DEFAULT NULL,
  rek_newspaper varchar(255) DEFAULT NULL,
  rek_newspaper_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_newspaper_pid,rek_newspaper_stamp),
  KEY rek_newspaper (rek_newspaper),
  FULLTEXT KEY rek_newspaper_ft (rek_newspaper)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_notes__shadow (
  rek_notes_id int(11) NOT NULL,
  rek_notes_pid varchar(64) NOT NULL DEFAULT '',
  rek_notes_xsdmf_id int(11) DEFAULT NULL,
  rek_notes text,
  rek_notes_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_notes_pid,rek_notes_stamp),
  FULLTEXT KEY rek_notes_ft (rek_notes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_oa_compliance__shadow (
  rek_oa_compliance_id int(11) NOT NULL,
  rek_oa_compliance_pid varchar(64) NOT NULL DEFAULT '',
  rek_oa_compliance_xsdmf_id int(11) DEFAULT NULL,
  rek_oa_compliance varchar(255) DEFAULT NULL,
  rek_oa_compliance_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_oa_compliance_pid,rek_oa_compliance_stamp),
  KEY rek_oa_compliance (rek_oa_compliance),
  KEY rek_oa_compliance_pid (rek_oa_compliance_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_oa_embargo_days__shadow (
  rek_oa_embargo_days_id int(11) NOT NULL,
  rek_oa_embargo_days_pid varchar(64) NOT NULL DEFAULT '',
  rek_oa_embargo_days_xsdmf_id int(11) DEFAULT NULL,
  rek_oa_embargo_days varchar(255) DEFAULT NULL,
  rek_oa_embargo_days_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_oa_embargo_days_pid,rek_oa_embargo_days_stamp),
  KEY rek_oa_embargo_days (rek_oa_embargo_days),
  KEY rek_oa_embargo_days_pid (rek_oa_embargo_days_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_oa_notes__shadow (
  rek_oa_notes_id int(11) NOT NULL,
  rek_oa_notes_pid varchar(64) NOT NULL DEFAULT '',
  rek_oa_notes_xsdmf_id int(11) DEFAULT NULL,
  rek_oa_notes text,
  rek_oa_notes_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_oa_notes_pid,rek_oa_notes_stamp),
  KEY rek_oa_notes_pid (rek_oa_notes_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_oa_status__shadow (
  rek_oa_status_id int(11) NOT NULL,
  rek_oa_status_pid varchar(64) NOT NULL DEFAULT '',
  rek_oa_status_xsdmf_id int(11) DEFAULT NULL,
  rek_oa_status int(11) DEFAULT NULL,
  rek_oa_status_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_oa_status_pid,rek_oa_status_stamp),
  KEY rek_oa_status (rek_oa_status),
  KEY rek_oa_status_pid (rek_oa_status_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_org_name__shadow (
  rek_org_name_id int(11) NOT NULL,
  rek_org_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_org_name_xsdmf_id int(11) DEFAULT NULL,
  rek_org_name varchar(255) DEFAULT NULL,
  rek_org_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_org_name_pid,rek_org_name_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_org_unit_name__shadow (
  rek_org_unit_name_id int(11) NOT NULL,
  rek_org_unit_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_org_unit_name_xsdmf_id int(11) DEFAULT NULL,
  rek_org_unit_name varchar(255) DEFAULT NULL,
  rek_org_unit_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_org_unit_name_pid,rek_org_unit_name_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_original_format__shadow (
  rek_original_format_id int(11) NOT NULL,
  rek_original_format_pid varchar(64) NOT NULL DEFAULT '',
  rek_original_format_xsdmf_id int(11) DEFAULT NULL,
  rek_original_format varchar(255) DEFAULT NULL,
  rek_original_format_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_original_format_pid,rek_original_format_stamp),
  KEY rek_original_format (rek_original_format)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_output_availability__shadow (
  rek_output_availability_id int(11) NOT NULL,
  rek_output_availability_pid varchar(64) NOT NULL DEFAULT '',
  rek_output_availability_xsdmf_id int(11) DEFAULT NULL,
  rek_output_availability varchar(1) DEFAULT NULL,
  rek_output_availability_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_output_availability_pid,rek_output_availability_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_parent_publication__shadow (
  rek_parent_publication_id int(11) NOT NULL,
  rek_parent_publication_pid varchar(64) NOT NULL DEFAULT '',
  rek_parent_publication_xsdmf_id int(11) DEFAULT NULL,
  rek_parent_publication varchar(255) DEFAULT NULL,
  rek_parent_publication_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_parent_publication_pid,rek_parent_publication_stamp),
  KEY rek_parent_publication (rek_parent_publication),
  FULLTEXT KEY rek_parent_publication_ft (rek_parent_publication)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_patent_number__shadow (
  rek_patent_number_id int(11) NOT NULL,
  rek_patent_number_pid varchar(64) NOT NULL DEFAULT '',
  rek_patent_number_xsdmf_id int(11) DEFAULT NULL,
  rek_patent_number varchar(255) DEFAULT NULL,
  rek_patent_number_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_patent_number_pid,rek_patent_number_stamp),
  KEY rek_patent_number (rek_patent_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_period__shadow (
  rek_period_id int(11) NOT NULL,
  rek_period_pid varchar(64) NOT NULL DEFAULT '',
  rek_period_xsdmf_id int(11) DEFAULT NULL,
  rek_period_order int(11) NOT NULL DEFAULT '1',
  rek_period text,
  rek_period_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_period_pid,rek_period_order,rek_period_stamp),
  KEY rek_period_pid (rek_period_pid),
  FULLTEXT KEY rek_period_ft (rek_period)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_phonetic_book_title__shadow (
  rek_phonetic_book_title_id int(11) NOT NULL,
  rek_phonetic_book_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_phonetic_book_title_xsdmf_id int(11) DEFAULT NULL,
  rek_phonetic_book_title varchar(255) DEFAULT NULL,
  rek_phonetic_book_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_phonetic_book_title_pid,rek_phonetic_book_title_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_phonetic_conference_name__shadow (
  rek_phonetic_conference_name_id int(11) NOT NULL,
  rek_phonetic_conference_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_phonetic_conference_name_xsdmf_id int(11) DEFAULT NULL,
  rek_phonetic_conference_name varchar(255) DEFAULT NULL,
  rek_phonetic_conference_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_phonetic_conference_name_pid,rek_phonetic_conference_name_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_phonetic_journal_name__shadow (
  rek_phonetic_journal_name_id int(11) NOT NULL,
  rek_phonetic_journal_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_phonetic_journal_name_xsdmf_id int(11) DEFAULT NULL,
  rek_phonetic_journal_name varchar(255) DEFAULT NULL,
  rek_phonetic_journal_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_phonetic_journal_name_pid,rek_phonetic_journal_name_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_phonetic_newspaper__shadow (
  rek_phonetic_newspaper_id int(11) NOT NULL,
  rek_phonetic_newspaper_pid varchar(64) NOT NULL DEFAULT '',
  rek_phonetic_newspaper_xsdmf_id int(11) DEFAULT NULL,
  rek_phonetic_newspaper varchar(255) DEFAULT NULL,
  rek_phonetic_newspaper_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_phonetic_newspaper_pid,rek_phonetic_newspaper_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_phonetic_title__shadow (
  rek_phonetic_title_id int(11) NOT NULL,
  rek_phonetic_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_phonetic_title_xsdmf_id int(11) DEFAULT NULL,
  rek_phonetic_title varchar(255) DEFAULT NULL,
  rek_phonetic_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_phonetic_title_pid,rek_phonetic_title_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_place_of_publication__shadow (
  rek_place_of_publication_id int(11) NOT NULL,
  rek_place_of_publication_pid varchar(64) NOT NULL DEFAULT '',
  rek_place_of_publication_xsdmf_id int(11) DEFAULT NULL,
  rek_place_of_publication varchar(255) DEFAULT NULL,
  rek_place_of_publication_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_place_of_publication_pid,rek_place_of_publication_stamp),
  KEY rek_place_of_publication (rek_place_of_publication),
  FULLTEXT KEY rek_place_of_publication_ft (rek_place_of_publication)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_print_details__shadow (
  rek_print_details_id int(11) NOT NULL,
  rek_print_details_pid varchar(64) NOT NULL DEFAULT '',
  rek_print_details_xsdmf_id int(11) DEFAULT NULL,
  rek_print_details text,
  rek_print_details_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_print_details_pid,rek_print_details_stamp),
  FULLTEXT KEY rek_print_details_ft (rek_print_details)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_prn__shadow (
  rek_prn_id int(11) NOT NULL,
  rek_prn_pid varchar(64) NOT NULL DEFAULT '',
  rek_prn_xsdmf_id int(11) DEFAULT NULL,
  rek_prn varchar(255) DEFAULT NULL,
  rek_prn_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_prn_pid,rek_prn_stamp),
  KEY rek_prn (rek_prn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_proceedings_title__shadow (
  rek_proceedings_title_id int(11) NOT NULL,
  rek_proceedings_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_proceedings_title_xsdmf_id int(11) DEFAULT NULL,
  rek_proceedings_title text,
  rek_proceedings_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_proceedings_title_pid,rek_proceedings_title_stamp),
  FULLTEXT KEY rek_proceedings_title_ft (rek_proceedings_title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_project_description__shadow (
  rek_project_description_id int(11) NOT NULL,
  rek_project_description_pid varchar(64) NOT NULL DEFAULT '',
  rek_project_description_xsdmf_id int(11) DEFAULT NULL,
  rek_project_description text,
  rek_project_description_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_project_description_pid,rek_project_description_stamp),
  KEY rek_project_description_pid (rek_project_description_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_project_id__shadow (
  rek_project_id_id int(11) NOT NULL,
  rek_project_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_project_id_xsdmf_id int(11) DEFAULT NULL,
  rek_project_id varchar(255) DEFAULT NULL,
  rek_project_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_project_id_pid,rek_project_id_stamp),
  KEY rek_project_id (rek_project_id),
  KEY rek_project_id_pid (rek_project_id_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_project_name__shadow (
  rek_project_name_id int(11) NOT NULL,
  rek_project_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_project_name_xsdmf_id int(11) DEFAULT NULL,
  rek_project_name varchar(255) DEFAULT NULL,
  rek_project_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_project_name_pid,rek_project_name_stamp),
  KEY rek_project_name (rek_project_name),
  KEY rek_project_name_pid (rek_project_name_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_publisher__shadow (
  rek_publisher_id int(11) NOT NULL,
  rek_publisher_pid varchar(64) NOT NULL DEFAULT '',
  rek_publisher_xsdmf_id int(11) DEFAULT NULL,
  rek_publisher varchar(255) DEFAULT NULL,
  rek_publisher_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_publisher_pid,rek_publisher_stamp),
  KEY rek_publisher (rek_publisher),
  FULLTEXT KEY rek_publisher_ft (rek_publisher)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_publisher_id__shadow (
  rek_publisher_id_id int(11) NOT NULL,
  rek_publisher_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_publisher_id_xsdmf_id int(11) DEFAULT NULL,
  rek_publisher_id int(11) DEFAULT NULL,
  rek_publisher_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_publisher_id_pid,rek_publisher_id_stamp),
  KEY rek_publisher_id (rek_publisher_id),
  KEY rek_publisher_id_pid (rek_publisher_id_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_pubmed_id__shadow (
  rek_pubmed_id_id int(11) NOT NULL,
  rek_pubmed_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_pubmed_id_xsdmf_id int(11) DEFAULT NULL,
  rek_pubmed_id varchar(255) DEFAULT NULL,
  rek_pubmed_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_pubmed_id_pid,rek_pubmed_id_stamp),
  KEY rek_pubmed_id (rek_pubmed_id),
  KEY rek_pubmed_id_pid (rek_pubmed_id_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_refereed__shadow (
  rek_refereed_id int(11) NOT NULL,
  rek_refereed_pid varchar(64) NOT NULL DEFAULT '',
  rek_refereed_xsdmf_id int(11) DEFAULT NULL,
  rek_refereed int(11) DEFAULT NULL,
  rek_refereed_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_refereed_pid,rek_refereed_stamp),
  KEY rek_refereed (rek_refereed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_refereed_source__shadow (
  rek_refereed_source_id int(11) NOT NULL,
  rek_refereed_source_pid varchar(64) NOT NULL DEFAULT '',
  rek_refereed_source_xsdmf_id int(11) DEFAULT NULL,
  rek_refereed_source varchar(255) DEFAULT NULL,
  rek_refereed_source_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_refereed_source_pid,rek_refereed_source_stamp),
  KEY rek_refereed_source (rek_refereed_source),
  KEY rek_refereed_source_pid (rek_refereed_source_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_references__shadow (
  rek_references_id int(11) NOT NULL,
  rek_references_pid varchar(64) NOT NULL DEFAULT '',
  rek_references_xsdmf_id int(11) DEFAULT NULL,
  rek_references text,
  rek_references_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_references_pid,rek_references_stamp),
  FULLTEXT KEY rek_references_ft (rek_references)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_related_datasets__shadow (
  rek_related_datasets_id int(11) NOT NULL,
  rek_related_datasets_pid varchar(64) NOT NULL DEFAULT '',
  rek_related_datasets_xsdmf_id int(11) DEFAULT NULL,
  rek_related_datasets varchar(255) DEFAULT NULL,
  rek_related_datasets_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_related_datasets_pid,rek_related_datasets_stamp),
  KEY rek_related_datasets (rek_related_datasets),
  KEY rek_related_datasets_pid (rek_related_datasets_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_related_publications__shadow (
  rek_related_publications_id int(11) NOT NULL,
  rek_related_publications_pid varchar(64) NOT NULL DEFAULT '',
  rek_related_publications_xsdmf_id int(11) DEFAULT NULL,
  rek_related_publications varchar(2047) DEFAULT NULL,
  rek_related_publications_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_related_publications_pid,rek_related_publications_stamp),
  KEY rek_related_publications (rek_related_publications(255)),
  KEY rek_related_publications_pid (rek_related_publications_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_report_number__shadow (
  rek_report_number_id int(11) NOT NULL,
  rek_report_number_pid varchar(64) NOT NULL DEFAULT '',
  rek_report_number_xsdmf_id int(11) DEFAULT NULL,
  rek_report_number varchar(255) DEFAULT NULL,
  rek_report_number_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_report_number_pid,rek_report_number_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_research_program__shadow (
  rek_research_program_id int(11) NOT NULL,
  rek_research_program_pid varchar(64) NOT NULL DEFAULT '',
  rek_research_program_xsdmf_id int(11) DEFAULT NULL,
  rek_research_program varchar(255) DEFAULT NULL,
  rek_research_program_order int(11) NOT NULL DEFAULT '1',
  rek_research_program_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_research_program_pid,rek_research_program_order,rek_research_program_stamp),
  KEY rek_research_program_pid (rek_research_program_pid),
  KEY rek_research_program (rek_research_program),
  KEY rek_research_program_order (rek_research_program_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_retracted__shadow (
  rek_retracted_id int(11) NOT NULL,
  rek_retracted_pid varchar(64) NOT NULL DEFAULT '',
  rek_retracted_xsdmf_id int(11) DEFAULT NULL,
  rek_retracted int(11) DEFAULT NULL,
  rek_retracted_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_retracted_pid,rek_retracted_stamp),
  KEY rek_retracted (rek_retracted),
  KEY rek_retracted_pid (rek_retracted_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_rights__shadow (
  rek_rights_id int(11) NOT NULL,
  rek_rights_pid varchar(64) NOT NULL DEFAULT '',
  rek_rights_xsdmf_id int(11) DEFAULT NULL,
  rek_rights text,
  rek_rights_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_rights_pid,rek_rights_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_roman_script_book_title__shadow (
  rek_roman_script_book_title_id int(11) NOT NULL,
  rek_roman_script_book_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_roman_script_book_title_xsdmf_id int(11) DEFAULT NULL,
  rek_roman_script_book_title varchar(255) DEFAULT NULL,
  rek_roman_script_book_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_roman_script_book_title_pid,rek_roman_script_book_title_stamp),
  KEY rek_roman_script_book_title (rek_roman_script_book_title),
  KEY rek_roman_script_book_title_pid (rek_roman_script_book_title_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_roman_script_conference_name__shadow (
  rek_roman_script_conference_name_id int(11) NOT NULL,
  rek_roman_script_conference_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_roman_script_conference_name_xsdmf_id int(11) DEFAULT NULL,
  rek_roman_script_conference_name varchar(255) DEFAULT NULL,
  rek_roman_script_conference_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_roman_script_conference_name_pid,rek_roman_script_conference_name_stamp),
  KEY rek_roman_script_conference_name (rek_roman_script_conference_name),
  KEY rek_roman_script_conference_name_pid (rek_roman_script_conference_name_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_roman_script_journal_name__shadow (
  rek_roman_script_journal_name_id int(11) NOT NULL,
  rek_roman_script_journal_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_roman_script_journal_name_xsdmf_id int(11) DEFAULT NULL,
  rek_roman_script_journal_name varchar(255) DEFAULT NULL,
  rek_roman_script_journal_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_roman_script_journal_name_pid,rek_roman_script_journal_name_stamp),
  KEY rek_roman_script_journal_name (rek_roman_script_journal_name),
  KEY rek_roman_script_journal_name_pid (rek_roman_script_journal_name_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_roman_script_proceedings_title__shadow (
  rek_roman_script_proceedings_title_id int(11) NOT NULL,
  rek_roman_script_proceedings_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_roman_script_proceedings_title_xsdmf_id int(11) DEFAULT NULL,
  rek_roman_script_proceedings_title varchar(255) DEFAULT NULL,
  rek_roman_script_proceedings_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_roman_script_proceedings_title_pid,rek_roman_script_proceedings_title_stamp),
  KEY rek_roman_script_proceedings_title (rek_roman_script_proceedings_title),
  KEY rek_roman_script_proceedings_title_pid (rek_roman_script_proceedings_title_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_roman_script_title__shadow (
  rek_roman_script_title_id int(11) NOT NULL,
  rek_roman_script_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_roman_script_title_xsdmf_id int(11) DEFAULT NULL,
  rek_roman_script_title varchar(255) DEFAULT NULL,
  rek_roman_script_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_roman_script_title_pid,rek_roman_script_title_stamp),
  KEY rek_roman_script_title (rek_roman_script_title),
  KEY rek_roman_script_title_pid (rek_roman_script_title_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_scopus_id__shadow (
  rek_scopus_id_id int(11) NOT NULL,
  rek_scopus_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_scopus_id_xsdmf_id int(11) DEFAULT NULL,
  rek_scopus_id varchar(255) DEFAULT NULL,
  rek_scopus_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_scopus_id_pid,rek_scopus_id_stamp),
  KEY rek_scopus_id (rek_scopus_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_section__shadow (
  rek_section_id int(11) NOT NULL,
  rek_section_pid varchar(64) NOT NULL DEFAULT '',
  rek_section_xsdmf_id int(11) DEFAULT NULL,
  rek_section varchar(255) DEFAULT NULL,
  rek_section_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_section_pid,rek_section_stamp),
  KEY rek_section (rek_section)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_sensitivity_explanation__shadow (
  rek_sensitivity_explanation_id int(11) NOT NULL,
  rek_sensitivity_explanation_pid varchar(64) NOT NULL DEFAULT '',
  rek_sensitivity_explanation_xsdmf_id int(11) DEFAULT NULL,
  rek_sensitivity_explanation text,
  rek_sensitivity_explanation_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_sensitivity_explanation_pid,rek_sensitivity_explanation_stamp),
  FULLTEXT KEY rek_sensitivity_explanation_ft (rek_sensitivity_explanation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_seo_code__shadow (
  rek_seo_code_id int(11) NOT NULL,
  rek_seo_code_pid varchar(64) NOT NULL DEFAULT '',
  rek_seo_code_xsdmf_id int(11) DEFAULT NULL,
  rek_seo_code int(11) DEFAULT NULL,
  rek_seo_code_order int(11) NOT NULL DEFAULT '1',
  rek_seo_code_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_seo_code_pid,rek_seo_code_order,rek_seo_code_stamp),
  KEY rek_seo_code_pid (rek_seo_code_pid),
  KEY rek_seo_code (rek_seo_code),
  KEY rek_seo_code_order (rek_seo_code_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_series__shadow (
  rek_series_id int(11) NOT NULL,
  rek_series_pid varchar(64) NOT NULL DEFAULT '',
  rek_series_xsdmf_id int(11) DEFAULT NULL,
  rek_series varchar(255) DEFAULT NULL,
  rek_series_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_series_pid,rek_series_stamp),
  KEY rek_series (rek_series),
  FULLTEXT KEY rek_series_ft (rek_series)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_software_required__shadow (
  rek_software_required_id int(11) NOT NULL,
  rek_software_required_pid varchar(64) NOT NULL DEFAULT '',
  rek_software_required_xsdmf_id int(11) DEFAULT NULL,
  rek_software_required varchar(255) DEFAULT NULL,
  rek_software_required_order int(11) NOT NULL DEFAULT '1',
  rek_software_required_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_software_required_pid,rek_software_required_order,rek_software_required_stamp),
  KEY rek_software_required (rek_software_required),
  KEY rek_software_required_pid (rek_software_required_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_source__shadow (
  rek_source_id int(11) NOT NULL,
  rek_source_pid varchar(64) NOT NULL DEFAULT '',
  rek_source_xsdmf_id int(11) DEFAULT NULL,
  rek_source varchar(255) DEFAULT NULL,
  rek_source_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_source_pid,rek_source_stamp),
  KEY rek_source (rek_source),
  KEY rek_source_pid (rek_source_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_start_date__shadow (
  rek_start_date_id int(11) NOT NULL,
  rek_start_date_pid varchar(64) NOT NULL DEFAULT '',
  rek_start_date_xsdmf_id int(11) DEFAULT NULL,
  rek_start_date datetime DEFAULT NULL,
  rek_start_date_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_start_date_pid,rek_start_date_stamp),
  KEY rek_start_date (rek_start_date),
  KEY rek_start_date_pid (rek_start_date_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_start_page__shadow (
  rek_start_page_id int(11) NOT NULL,
  rek_start_page_pid varchar(64) NOT NULL DEFAULT '',
  rek_start_page_xsdmf_id int(11) DEFAULT NULL,
  rek_start_page varchar(255) DEFAULT NULL,
  rek_start_page_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_start_page_pid,rek_start_page_stamp),
  KEY rek_start_page (rek_start_page)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_structural_systems__shadow (
  rek_structural_systems_id int(11) NOT NULL,
  rek_structural_systems_pid varchar(64) NOT NULL DEFAULT '',
  rek_structural_systems_xsdmf_id int(11) DEFAULT NULL,
  rek_structural_systems_order int(11) NOT NULL DEFAULT '1',
  rek_structural_systems text,
  rek_structural_systems_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_structural_systems_pid,rek_structural_systems_order,rek_structural_systems_stamp),
  KEY rek_structural_systems_pid (rek_structural_systems_pid),
  FULLTEXT KEY rek_structural_systems_ft (rek_structural_systems)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_style__shadow (
  rek_style_id int(11) NOT NULL,
  rek_style_pid varchar(64) NOT NULL DEFAULT '',
  rek_style_xsdmf_id int(11) DEFAULT NULL,
  rek_style_order int(11) NOT NULL DEFAULT '1',
  rek_style text,
  rek_style_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_style_pid,rek_style_order,rek_style_stamp),
  KEY rek_style_pid (rek_style_pid),
  FULLTEXT KEY rek_style_ft (rek_style)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_subcategory__shadow (
  rek_subcategory_id int(11) NOT NULL,
  rek_subcategory_pid varchar(64) NOT NULL DEFAULT '',
  rek_subcategory_xsdmf_id int(11) DEFAULT NULL,
  rek_subcategory_order int(11) NOT NULL DEFAULT '1',
  rek_subcategory varchar(255) DEFAULT NULL,
  rek_subcategory_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_subcategory_pid,rek_subcategory_order,rek_subcategory_stamp),
  KEY rek_subcategory_pid (rek_subcategory_pid),
  FULLTEXT KEY rek_subcategory_ft (rek_subcategory)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_subject__shadow (
  rek_subject_id int(11) NOT NULL,
  rek_subject_pid varchar(64) NOT NULL DEFAULT '',
  rek_subject_xsdmf_id int(11) DEFAULT NULL,
  rek_subject int(11) DEFAULT NULL,
  rek_subject_order int(11) NOT NULL DEFAULT '1',
  rek_subject_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_subject_pid,rek_subject_order,rek_subject_stamp),
  KEY rek_subject_pid (rek_subject_pid,rek_subject),
  KEY rek_subject (rek_subject),
  KEY rek_subject_order (rek_subject_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_supervisor__shadow (
  rek_supervisor_id int(11) NOT NULL,
  rek_supervisor_pid varchar(64) NOT NULL DEFAULT '',
  rek_supervisor_xsdmf_id int(11) DEFAULT NULL,
  rek_supervisor varchar(255) DEFAULT NULL,
  rek_supervisor_order int(11) NOT NULL DEFAULT '1',
  rek_supervisor_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_supervisor_pid,rek_supervisor_order,rek_supervisor_stamp),
  KEY rek_supervisor_pid (rek_supervisor_pid),
  KEY rek_supervisor (rek_supervisor),
  KEY rek_supervisor_order (rek_supervisor_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_supervisor_id__shadow (
  rek_supervisor_id_id int(11) NOT NULL,
  rek_supervisor_id_pid varchar(64) NOT NULL DEFAULT '',
  rek_supervisor_id_xsdmf_id int(11) DEFAULT NULL,
  rek_supervisor_id int(11) DEFAULT NULL,
  rek_supervisor_id_order int(11) NOT NULL DEFAULT '1',
  rek_supervisor_id_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_supervisor_id_pid,rek_supervisor_id_order,rek_supervisor_id_stamp),
  KEY rek_supervisor_id_pid (rek_supervisor_id_pid),
  KEY rek_supervisor_id (rek_supervisor_id),
  KEY rek_supervisor_id_order (rek_supervisor_id_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_surrounding_features__shadow (
  rek_surrounding_features_id int(11) NOT NULL,
  rek_surrounding_features_pid varchar(64) NOT NULL DEFAULT '',
  rek_surrounding_features_xsdmf_id int(11) DEFAULT NULL,
  rek_surrounding_features_order int(11) NOT NULL DEFAULT '1',
  rek_surrounding_features text,
  rek_surrounding_features_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_surrounding_features_pid,rek_surrounding_features_order,rek_surrounding_features_stamp),
  KEY rek_surrounding_features_pid (rek_surrounding_features_pid),
  FULLTEXT KEY rek_surrounding_features_ft (rek_surrounding_features)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_time_period_end_date__shadow (
  rek_time_period_end_date_id int(11) NOT NULL,
  rek_time_period_end_date_pid varchar(64) NOT NULL DEFAULT '',
  rek_time_period_end_date_xsdmf_id int(11) DEFAULT NULL,
  rek_time_period_end_date datetime DEFAULT NULL,
  rek_time_period_end_date_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_time_period_end_date_pid,rek_time_period_end_date_stamp),
  KEY rek_time_period_end_date (rek_time_period_end_date),
  KEY rek_time_period_end_date_pid (rek_time_period_end_date_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_time_period_start_date__shadow (
  rek_time_period_start_date_id int(11) NOT NULL,
  rek_time_period_start_date_pid varchar(64) NOT NULL DEFAULT '',
  rek_time_period_start_date_xsdmf_id int(11) DEFAULT NULL,
  rek_time_period_start_date datetime DEFAULT NULL,
  rek_time_period_start_date_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_time_period_start_date_pid,rek_time_period_start_date_stamp),
  KEY rek_time_period_start_date (rek_time_period_start_date),
  KEY rek_time_period_start_date_pid (rek_time_period_start_date_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_total_chapters__shadow (
  rek_total_chapters_id int(11) NOT NULL,
  rek_total_chapters_pid varchar(64) NOT NULL DEFAULT '',
  rek_total_chapters_xsdmf_id int(11) DEFAULT NULL,
  rek_total_chapters varchar(255) DEFAULT NULL,
  rek_total_chapters_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_total_chapters_pid,rek_total_chapters_stamp),
  KEY rek_total_chapters (rek_total_chapters)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_total_pages__shadow (
  rek_total_pages_id int(11) NOT NULL,
  rek_total_pages_pid varchar(64) NOT NULL DEFAULT '',
  rek_total_pages_xsdmf_id int(11) DEFAULT NULL,
  rek_total_pages varchar(255) DEFAULT NULL,
  rek_total_pages_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_total_pages_pid,rek_total_pages_stamp),
  KEY rek_total_pages (rek_total_pages)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_total_pages_bw__shadow (
  rek_total_pages_bw_id int(11) NOT NULL,
  rek_total_pages_bw_pid varchar(64) NOT NULL DEFAULT '',
  rek_total_pages_bw_xsdmf_id int(11) DEFAULT NULL,
  rek_total_pages_bw varchar(255) DEFAULT NULL,
  rek_total_pages_bw_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_total_pages_bw_pid,rek_total_pages_bw_stamp),
  KEY rek_total_pages_bw (rek_total_pages_bw)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_total_pages_colour__shadow (
  rek_total_pages_colour_id int(11) NOT NULL,
  rek_total_pages_colour_pid varchar(64) NOT NULL DEFAULT '',
  rek_total_pages_colour_xsdmf_id int(11) DEFAULT NULL,
  rek_total_pages_colour varchar(255) DEFAULT NULL,
  rek_total_pages_colour_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_total_pages_colour_pid,rek_total_pages_colour_stamp),
  KEY rek_total_pages_colour (rek_total_pages_colour)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_transcript__shadow (
  rek_transcript_id int(11) NOT NULL,
  rek_transcript_pid varchar(64) NOT NULL DEFAULT '',
  rek_transcript_xsdmf_id int(11) DEFAULT NULL,
  rek_transcript text,
  rek_transcript_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_transcript_pid,rek_transcript_stamp),
  KEY rek_transcript_pid (rek_transcript_pid),
  FULLTEXT KEY rek_transcript_ft (rek_transcript)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_translated_book_title__shadow (
  rek_translated_book_title_id int(11) NOT NULL,
  rek_translated_book_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_translated_book_title_xsdmf_id int(11) DEFAULT NULL,
  rek_translated_book_title varchar(255) DEFAULT NULL,
  rek_translated_book_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_translated_book_title_pid,rek_translated_book_title_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_translated_conference_name__shadow (
  rek_translated_conference_name_id int(11) NOT NULL,
  rek_translated_conference_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_translated_conference_name_xsdmf_id int(11) DEFAULT NULL,
  rek_translated_conference_name varchar(255) DEFAULT NULL,
  rek_translated_conference_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_translated_conference_name_pid,rek_translated_conference_name_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_translated_journal_name__shadow (
  rek_translated_journal_name_id int(11) NOT NULL,
  rek_translated_journal_name_pid varchar(64) NOT NULL DEFAULT '',
  rek_translated_journal_name_xsdmf_id int(11) DEFAULT NULL,
  rek_translated_journal_name varchar(255) DEFAULT NULL,
  rek_translated_journal_name_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_translated_journal_name_pid,rek_translated_journal_name_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_translated_newspaper__shadow (
  rek_translated_newspaper_id int(11) NOT NULL,
  rek_translated_newspaper_pid varchar(64) NOT NULL DEFAULT '',
  rek_translated_newspaper_xsdmf_id int(11) DEFAULT NULL,
  rek_translated_newspaper varchar(255) DEFAULT NULL,
  rek_translated_newspaper_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_translated_newspaper_pid,rek_translated_newspaper_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_translated_proceedings_title__shadow (
  rek_translated_proceedings_title_id int(11) NOT NULL,
  rek_translated_proceedings_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_translated_proceedings_title_xsdmf_id int(11) DEFAULT NULL,
  rek_translated_proceedings_title varchar(255) DEFAULT NULL,
  rek_translated_proceedings_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_translated_proceedings_title_pid,rek_translated_proceedings_title_stamp),
  KEY rek_translated_proceedings_title (rek_translated_proceedings_title),
  KEY rek_translated_proceedings_title_pid (rek_translated_proceedings_title_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_translated_title__shadow (
  rek_translated_title_id int(11) NOT NULL,
  rek_translated_title_pid varchar(64) NOT NULL DEFAULT '',
  rek_translated_title_xsdmf_id int(11) DEFAULT NULL,
  rek_translated_title varchar(255) DEFAULT NULL,
  rek_translated_title_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_translated_title_pid,rek_translated_title_stamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_volume_number__shadow (
  rek_volume_number_id int(11) NOT NULL,
  rek_volume_number_pid varchar(64) NOT NULL DEFAULT '',
  rek_volume_number_xsdmf_id int(11) DEFAULT NULL,
  rek_volume_number varchar(255) DEFAULT NULL,
  rek_volume_number_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_volume_number_pid,rek_volume_number_stamp),
  KEY rek_volume_number (rek_volume_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;








CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_xsd_display_option__shadow (
  rek_xsd_display_option_id int(11) NOT NULL,
  rek_xsd_display_option_pid varchar(64) NOT NULL DEFAULT '',
  rek_xsd_display_option_xsdmf_id int(11) DEFAULT NULL,
  rek_xsd_display_option int(11) DEFAULT NULL,
  rek_xsd_display_option_order int(11) NOT NULL DEFAULT '1',
  rek_xsd_display_option_stamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rek_xsd_display_option_pid,rek_xsd_display_option_order,rek_xsd_display_option_stamp),
  KEY rek_xsd_display_option_pid (rek_xsd_display_option_pid),
  KEY rek_xsd_display_option (rek_xsd_display_option),
  KEY rek_xsd_display_option_order (rek_xsd_display_option_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%pid_index (
  pid_number int(10) unsigned NOT NULL,
  PRIMARY KEY (pid_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;