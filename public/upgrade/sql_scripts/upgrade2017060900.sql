ALTER TABLE %TABLE_PREFIX%record_search_key__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_abbreviated_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_abbreviated_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_access_conditions__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_access_conditions_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_acknowledgements__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_acknowledgements_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_additional_notes__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_additional_notes_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_adt_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_adt_id_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_advisory_statement__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_advisory_statement_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_alternate_genre__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_alternate_genre_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_alternative_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_alternative_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_ands_collection_type__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_ands_collection_type_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_architect_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_architect_id_version`,`rek_architect_id_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_architect_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_architect_name_version`,`rek_architect_name_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_architectural_features__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_architectural_features_version`,`rek_architectural_features_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_assigned_group_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_assigned_group_id_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_assigned_user_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_assigned_user_id_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_author__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_author_version`,`rek_author_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_author_count__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_author_count_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_author_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_author_id_version`,`rek_author_id_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_author_role__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_author_role_version`,`rek_author_role_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_book_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_book_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_building_materials__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_building_materials_version`,`rek_building_materials_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_category__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_category_version`,`rek_category_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_chapter_number__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_chapter_number_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_collection_year__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_collection_year_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_condition__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_condition_version`,`rek_condition_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_conference_dates__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_conference_dates_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_conference_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_conference_id_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_conference_location__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_conference_location_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_conference_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_conference_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_construction_date__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_construction_date_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_contact_details_email__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_contact_details_email_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_contributor__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_contributor_version`,`rek_contributor_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_contributor_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_contributor_id_version`,`rek_contributor_id_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_convener__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_convener_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_country_of_issue__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_country_of_issue_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_coverage_period__shadow` CHANGE `rek_coverage_period_id` `rek_coverage_period_id` INT(11) NOT NULL
ALTER TABLE %TABLE_PREFIX%record_search_key_coverage_period__shadow` ADD KEY `rek_coverage_period_order` (`rek_coverage_period_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_coverage_period__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_coverage_period_version`,`rek_coverage_period_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_creator_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_creator_id_version`,`rek_creator_id_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_creator_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_creator_name_version`,`rek_creator_name_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_data_volume__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_data_volume_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_datastream_policy__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_datastream_policy_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_date_available__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_date_available_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_date_photo_taken__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_date_photo_taken_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_date_recorded__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_date_recorded_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_date_scanned__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_date_scanned_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_description_of_resource__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_description_of_resource_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_doi__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_doi_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_edition__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_edition_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_embase_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_embase_id_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_end_date__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_end_date_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_end_page__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_end_page_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_extent__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_extent_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_fields_of_research__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_fields_of_research_version`,`rek_fields_of_research_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_file_attachment_content__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_file_attachment_content_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_file_attachment_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_file_attachment_name_version`,`rek_file_attachment_name_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_file_description__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_file_description_version`,`rek_file_description_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_first_author_in_document_derived__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_first_author_in_document_derived_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_first_author_in_fez_derived__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_first_author_in_fez_derived_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_follow_up_flags__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_follow_up_flags_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_follow_up_flags_imu__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_follow_up_flags_imu_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_funding_body__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_funding_body_version`,`rek_funding_body_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_geographic_area__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_geographic_area_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_grant_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_grant_id_version`,`rek_grant_id_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_herdc_code__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_herdc_code_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_herdc_status__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_herdc_status_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_identifier__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_identifier_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_institutional_status__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_institutional_status_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_interior_features__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_interior_features_version`,`rek_interior_features_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_isannotationof__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_isannotationof_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_isbn__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_isbn_version`,`rek_isbn_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_isdatacomponentof__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_isdatacomponentof_version`,`rek_isdatacomponentof_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_isdatasetof__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_isdatasetof_version`,`rek_isdatasetof_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_isderivationof__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_isderivationof_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_isi_loc__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_isi_loc_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_ismemberof__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_ismemberof_version`,`rek_ismemberof_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_issn__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_issn_version`,`rek_issn_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_issue_number__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_issue_number_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_job_number__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_job_number_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_journal_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_journal_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_keywords__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_keywords_version`,`rek_keywords_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_language__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_language_version`,`rek_language_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_language_of_book_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_language_of_book_title_version`,`rek_language_of_book_title_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_language_of_journal_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_language_of_journal_name_version`,`rek_language_of_journal_name_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_language_of_proceedings_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_language_of_proceedings_title_version`,`rek_language_of_proceedings_title_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_language_of_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_language_of_title_version`,`rek_language_of_title_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_length__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_length_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_license__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_license_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_link__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_link_version`,`rek_link_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_link_description__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_link_description_version`,`rek_link_description_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_location__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_location_version`,`rek_location_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_na_explanation__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_na_explanation_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_native_script_book_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_native_script_book_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_native_script_conference_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_native_script_conference_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_native_script_journal_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_native_script_journal_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_native_script_proceedings_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_native_script_proceedings_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_native_script_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_native_script_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_newspaper__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_newspaper_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_notes__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_notes_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_oa_compliance__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_oa_compliance_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_oa_embargo_days__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_oa_embargo_days_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_oa_notes__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_oa_notes_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_oa_status__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_oa_status_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_org_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_org_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_org_unit_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_org_unit_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_original_format__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_original_format_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_output_availability__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_output_availability_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_parent_publication__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_parent_publication_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_patent_number__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_patent_number_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_period__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_period_version`,`rek_period_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_book_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_phonetic_book_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_conference_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_phonetic_conference_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_journal_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_phonetic_journal_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_newspaper__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_phonetic_newspaper_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_phonetic_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_place_of_publication__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_place_of_publication_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_print_details__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_print_details_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_prn__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_prn_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_proceedings_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_proceedings_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_project_description__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_project_description_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_project_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_project_id_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_project_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_project_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_project_start_date__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_project_start_date_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_publisher__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_publisher_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_publisher_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_publisher_id_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_pubmed_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_pubmed_id_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_refereed__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_refereed_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_refereed_source__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_refereed_source_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_references__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_references_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_related_datasets__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_related_datasets_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_related_publications__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_related_publications_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_report_number__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_report_number_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_research_program__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_research_program_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_retracted__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_retracted_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_rights__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_rights_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_roman_script_book_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_roman_script_book_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_roman_script_conference_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_roman_script_conference_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_roman_script_journal_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_roman_script_journal_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_roman_script_proceedings_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_roman_script_proceedings_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_roman_script_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_roman_script_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_scale__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_scale_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_scopus_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_scopus_id_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_section__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_section_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_sensitivity_explanation__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_sensitivity_explanation_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_seo_code__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_seo_code_version`,`rek_seo_code_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_series__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_series_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_software_required__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_software_required_version`,`rek_software_required_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_source__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_source_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_start_date__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_start_date_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_start_page__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_start_page_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_structural_systems__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_structural_systems_version`,`rek_structural_systems_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_style__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_style_version`,`rek_style_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_subcategory__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_subcategory_version`,`rek_subcategory_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_subject__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_subject_version`,`rek_subject_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_supervisor__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_supervisor_version`,`rek_supervisor_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_supervisor_id__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_supervisor_id_version`,`rek_supervisor_id_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_surrounding_features__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_surrounding_features_version`,`rek_surrounding_features_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_time_period_end_date__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_time_period_end_date_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_time_period_start_date__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_time_period_start_date_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_total_chapters__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_total_chapters_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_total_pages__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_total_pages_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_total_pages_bw__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_total_pages_bw_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_total_pages_colour__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_total_pages_colour_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_transcript__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_transcript_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_translated_book_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_translated_book_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_translated_conference_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_translated_conference_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_translated_journal_name__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_translated_journal_name_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_translated_newspaper__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_translated_newspaper_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_translated_proceedings_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_translated_proceedings_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_translated_title__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_translated_title_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_type_of_data__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_type_of_data_version`,`rek_type_of_data_order`);
ALTER TABLE %TABLE_PREFIX%record_search_key_volume_number__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_volume_number_version`);
ALTER TABLE %TABLE_PREFIX%record_search_key_xsd_display_option__shadow` DROP PRIMARY KEY, ADD PRIMARY KEY(`rek_xsd_display_option_version`,`rek_xsd_display_option_order`);
