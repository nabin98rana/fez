ALTER TABLE %TABLE_PREFIX%record_search_key_proceedings_title DROP INDEX rek_proceedings_title;
ALTER TABLE %TABLE_PREFIX%record_search_key_proceedings_title DROP INDEX unique_constraint;
ALTER TABLE %TABLE_PREFIX%record_search_key_proceedings_title CHANGE rek_proceedings_title rek_proceedings_title TEXT NULL;

UPDATE %TABLE_PREFIX%search_key
SET sek_html_input = 'textarea', sek_data_type = 'text'
WHERE sek_id in ('UQ_2');

ALTER TABLE %TABLE_PREFIX%record_search_key_conference_name DROP INDEX rek_conference_name;
ALTER TABLE %TABLE_PREFIX%record_search_key_conference_name DROP INDEX unique_constraint;
ALTER TABLE %TABLE_PREFIX%record_search_key_conference_name CHANGE rek_conference_name rek_conference_name TEXT NULL;

UPDATE %TABLE_PREFIX%search_key
SET sek_html_input = 'textarea', sek_data_type = 'text'
WHERE sek_id in ('core_36');

ALTER TABLE %TABLE_PREFIX%workflow_sessions CHANGE wfses_listing wfses_listing TEXT NULL;