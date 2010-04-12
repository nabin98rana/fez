-- clearing some meta headers that shouldn't be set
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = NULL WHERE sek_id = 'core_11'; -- display type
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = NULL WHERE sek_id = 'core_84'; -- scopus id (should be null instead of 0)
-- adding the google scholar citations to existing meta headers
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'DC.Title|citation_title' WHERE sek_id = 'core_2';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'DC.Publisher|citation_publisher' WHERE sek_id = 'core_29';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'DC.Creator|citation_authors' WHERE sek_id = 'core_3';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'DC.Description|citation_abstract' WHERE sek_id = 'core_5';
-- adding new google scholar citations
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_pdf_url' WHERE sek_id = 'core_6';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_language' WHERE sek_id = 'core_51';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_issue' WHERE sek_id = 'core_44';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_issn' WHERE sek_id = 'core_63';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_isbn' WHERE sek_id = 'core_64';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'DC.Date|citation_date' WHERE sek_id = 'core_14';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_journal_title' WHERE sek_id = 'core_34';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_volume' WHERE sek_id = 'core_45';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_firstpage' WHERE sek_id = 'core_41';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_lastpage' WHERE sek_id = 'core_42';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_keywords' WHERE sek_id = 'core_12';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_conference' WHERE sek_id = 'core_36';
UPDATE %TABLE_PREFIX%search_key SET sek_meta_header = 'citation_technical_report_number' WHERE sek_id = 'core_72';
