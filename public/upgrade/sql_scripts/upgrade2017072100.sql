UPDATE %TABLE_PREFIX%xsd_display_matchfields set xsdmf_sek_id = null, xsdmf_enabled = 0 where xsdmf_sek_id in (
  SELECT sek_id from %TABLE_PREFIX%search_key where sek_title in (
    'Abbreviated Title',
    'ADT ID',
    'Author Count',
    'Collection Year',
    'Description of Resource',
    'Embase ID',
    'Follow up Flags',
    'Follow up Flags IMU',
    'Funding Body',
    'GS Citation Count',
    'GS Cited By Link',
    'isAnnotationOf',
    'isDataComponentOf',
    'NA Explanation',
    'OA Compliance',
    'Output Availability',
    'Phonetic Book Title',
    'Phonetic Conference Name',
    'Phonetic Journal Name',
    'Phonetic Newspaper',
    'Phonetic Title',
    'Print Details',
    'PRN',
    'Publisher ID',
    'References',
    'Research Program',
    'Sensitivity Explanation',
    'Sequence',
    'Total Pages BW',
    'Total Pages Colour',
    'Views'
  )
);

DELETE FROM %TABLE_PREFIX%search_key where sek_title in (
    'Abbreviated Title',
    'ADT ID',
    'Author Count',
    'Collection Year',
    'Description of Resource',
    'Embase ID',
    'Follow up Flags',
    'Follow up Flags IMU',
    'Funding Body',
    'GS Citation Count',
    'GS Cited By Link',
    'isAnnotationOf',
    'isDataComponentOf',
    'NA Explanation',
    'OA Compliance',
    'Output Availability',
    'Phonetic Book Title',
    'Phonetic Conference Name',
    'Phonetic Journal Name',
    'Phonetic Newspaper',
    'Phonetic Title',
    'Print Details',
    'PRN',
    'Publisher ID',
    'References',
    'Research Program',
    'Sensitivity Explanation',
    'Sequence',
    'Total Pages BW',
    'Total Pages Colour',
    'Views'
  );

INSERT INTO %TABLE_PREFIX%record_search_key_grant_agency (rek_grant_agency_pid, rek_grant_agency, rek_grant_agency_order)
SELECT rek_funding_body_pid, rek_funding_body, rek_funding_body_order
FROM %TABLE_PREFIX%record_search_key_funding_body
INNER JOIN %TABLE_PREFIX%record_search_key on rek_pid = rek_funding_body_pid;

DROP TABLE %TABLE_PREFIX%record_search_key_abbreviated_title;
DROP TABLE %TABLE_PREFIX%record_search_key_abbreviated_title__shadow;
DROP TABLE %TABLE_PREFIX%record_search_key_embase_id;
DROP TABLE %TABLE_PREFIX%record_search_key_embase_id__shadow;
DROP TABLE %TABLE_PREFIX%record_search_key_description_of_resource;
DROP TABLE %TABLE_PREFIX%record_search_key_description_of_resource__shadow;
DROP TABLE %TABLE_PREFIX%record_search_key_na_explanation;
DROP TABLE %TABLE_PREFIX%record_search_key_na_explanation__shadow;
DROP TABLE %TABLE_PREFIX%record_search_key_output_availability;
DROP TABLE %TABLE_PREFIX%record_search_key_output_availability__shadow;
DROP TABLE %TABLE_PREFIX%record_search_key_research_program;
DROP TABLE %TABLE_PREFIX%record_search_key_research_program__shadow;
DROP TABLE %TABLE_PREFIX%record_search_key_sensitivity_explanation;
DROP TABLE %TABLE_PREFIX%record_search_key_sensitivity_explanation__shadow;
DROP TABLE %TABLE_PREFIX%record_search_key_isannotationof;
DROP TABLE %TABLE_PREFIX%record_search_key_isannotationof__shadow;
DROP TABLE %TABLE_PREFIX%record_search_key_publisher_id;
DROP TABLE %TABLE_PREFIX%record_search_key_publisher_id__shadow;

ALTER TABLE %TABLE_PREFIX%record_search_key DROP COLUMN rek_gs_citation_count, DROP COLUMN rek_sequence, DROP COLUMN rek_sequence_xsdmf_id, DROP COLUMN rek_gs_cited_by_link, DROP COLUMN rek_views;
ALTER TABLE %TABLE_PREFIX%record_search_key__shadow DROP COLUMN rek_gs_citation_count, DROP COLUMN rek_sequence, DROP COLUMN rek_sequence_xsdmf_id, DROP COLUMN rek_gs_cited_by_link, DROP COLUMN rek_views;

ALTER TABLE %TABLE_PREFIX%record_search_key_adt_id DROP FOREIGN KEY `rek_adtid_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_adt_id__shadow DROP FOREIGN KEY `rek_adtid__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_author_count DROP FOREIGN KEY `rek_autco_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_author_count__shadow DROP FOREIGN KEY `rek_autco__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_collection_year DROP FOREIGN KEY `rek_colye_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_collection_year__shadow DROP FOREIGN KEY `rek_colye__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_follow_up_flags DROP FOREIGN KEY `rek_folup_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_follow_up_flags__shadow DROP FOREIGN KEY `rek_folup__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_follow_up_flags_imu DROP FOREIGN KEY `rek_folupi_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_follow_up_flags_imu__shadow DROP FOREIGN KEY `rek_folupi__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_funding_body DROP FOREIGN KEY `rek_funbo_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_funding_body__shadow DROP FOREIGN KEY `rek_funbo__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_isdatacomponentof DROP FOREIGN KEY `rek_isda_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_isdatacomponentof__shadow DROP FOREIGN KEY `rek_isda__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_oa_compliance DROP FOREIGN KEY `rek_oacom_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_oa_compliance__shadow DROP FOREIGN KEY `rek_oacom__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_book_title DROP FOREIGN KEY `rek_phobo_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_book_title__shadow DROP FOREIGN KEY `rek_phobo__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_conference_name DROP FOREIGN KEY `rek_phoco_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_conference_name__shadow DROP FOREIGN KEY `rek_phoco__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_journal_name DROP FOREIGN KEY `rek_phojo_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_journal_name__shadow DROP FOREIGN KEY `rek_phojo__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_newspaper DROP FOREIGN KEY `rek_phone_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_newspaper__shadow DROP FOREIGN KEY `rek_phone__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_title DROP FOREIGN KEY `rek_photi_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_phonetic_title__shadow DROP FOREIGN KEY `rek_photi__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_print_details DROP FOREIGN KEY `rek_pride_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_print_details__shadow DROP FOREIGN KEY `rek_pride__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_prn DROP FOREIGN KEY `rek_prn_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_prn__shadow DROP FOREIGN KEY `rek_prn__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_references DROP FOREIGN KEY `rek_refer_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_references__shadow DROP FOREIGN KEY `rek_refer__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_total_pages_bw DROP FOREIGN KEY `rek_totpab_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_total_pages_bw__shadow DROP FOREIGN KEY `rek_totpab__foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_total_pages_colour DROP FOREIGN KEY `rek_totpac_foreign`;
ALTER TABLE %TABLE_PREFIX%record_search_key_total_pages_colour__shadow DROP FOREIGN KEY `rek_totpac__foreign`;

RENAME TABLE %TABLE_PREFIX%record_search_key_adt_id TO zzz_%TABLE_PREFIX%record_search_key_adt_id;
RENAME TABLE %TABLE_PREFIX%record_search_key_adt_id__shadow TO zzz_%TABLE_PREFIX%record_search_key_adt_id__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_author_count TO zzz_%TABLE_PREFIX%record_search_key_record_search_key_author_count;
RENAME TABLE %TABLE_PREFIX%record_search_key_author_count__shadow TO zzz_%TABLE_PREFIX%record_search_key_author_count__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_collection_year TO zzz_%TABLE_PREFIX%record_search_key_collection_year;
RENAME TABLE %TABLE_PREFIX%record_search_key_collection_year__shadow TO zzz_%TABLE_PREFIX%record_search_key_collection_year__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_follow_up_flags TO zzz_%TABLE_PREFIX%record_search_key_follow_up_flags;
RENAME TABLE %TABLE_PREFIX%record_search_key_follow_up_flags__shadow TO zzz_%TABLE_PREFIX%record_search_key_follow_up_flags__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_follow_up_flags_imu TO zzz_%TABLE_PREFIX%record_search_key_follow_up_flags_imu;
RENAME TABLE %TABLE_PREFIX%record_search_key_follow_up_flags_imu__shadow TO zzz_%TABLE_PREFIX%record_search_key_follow_up_flags_imu__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_funding_body TO zzz_%TABLE_PREFIX%record_search_key_funding_body;
RENAME TABLE %TABLE_PREFIX%record_search_key_funding_body__shadow TO zzz_%TABLE_PREFIX%record_search_key_funding__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_isdatacomponentof TO zzz_%TABLE_PREFIX%record_search_key_isdatacomponentof;
RENAME TABLE %TABLE_PREFIX%record_search_key_isdatacomponentof__shadow TO zzz_%TABLE_PREFIX%record_search_key_isdatacomponentof__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_oa_compliance TO zzz_%TABLE_PREFIX%record_search_key_oa_compliance;
RENAME TABLE %TABLE_PREFIX%record_search_key_oa_compliance__shadow TO zzz_%TABLE_PREFIX%record_search_key_oa_compliance__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_phonetic_book_title TO zzz_%TABLE_PREFIX%record_search_key_phonetic_book_title;
RENAME TABLE %TABLE_PREFIX%record_search_key_phonetic_book_title__shadow TO zzz_%TABLE_PREFIX%record_search_key_phonetic_book_title__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_phonetic_conference_name TO zzz_%TABLE_PREFIX%record_search_key_phonetic_conference_name;
RENAME TABLE %TABLE_PREFIX%record_search_key_phonetic_conference_name__shadow TO zzz_%TABLE_PREFIX%record_search_key_phonetic_conference_name__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_phonetic_journal_name TO zzz_%TABLE_PREFIX%record_search_key_phonetic_journal_name;
RENAME TABLE %TABLE_PREFIX%record_search_key_phonetic_journal_name__shadow TO zzz_%TABLE_PREFIX%record_search_key_phonetic_journal_name__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_phonetic_newspaper TO zzz_%TABLE_PREFIX%record_search_key_phonetic_newspaper;
RENAME TABLE %TABLE_PREFIX%record_search_key_phonetic_newspaper__shadow TO zzz_%TABLE_PREFIX%record_search_key_phonetic_newspaper__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_phonetic_title TO zzz_%TABLE_PREFIX%record_search_key_phonetic_title;
RENAME TABLE %TABLE_PREFIX%record_search_key_phonetic_title__shadow TO zzz_%TABLE_PREFIX%record_search_key_phonetic_title__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_print_details TO zzz_%TABLE_PREFIX%record_search_key_print_details;
RENAME TABLE %TABLE_PREFIX%record_search_key_print_details__shadow TO zzz_%TABLE_PREFIX%record_search_key_print_details__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_prn TO zzz_%TABLE_PREFIX%record_search_key_prn;
RENAME TABLE %TABLE_PREFIX%record_search_key_prn__shadow TO zzz_%TABLE_PREFIX%record_search_key_prn__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_references TO zzz_%TABLE_PREFIX%record_search_key_references;
RENAME TABLE %TABLE_PREFIX%record_search_key_references__shadow TO zzz_%TABLE_PREFIX%record_search_key_references__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_total_pages_bw TO zzz_%TABLE_PREFIX%record_search_key_total_pages_bw;
RENAME TABLE %TABLE_PREFIX%record_search_key_total_pages_bw__shadow TO zzz_%TABLE_PREFIX%record_search_key_total_pages_bw__shadow;
RENAME TABLE %TABLE_PREFIX%record_search_key_total_pages_colour TO zzz_%TABLE_PREFIX%record_search_key_total_pages_colour;
RENAME TABLE %TABLE_PREFIX%record_search_key_total_pages_colour__shadow TO zzz_%TABLE_PREFIX%record_search_key_total_pages_colour__shadow;
