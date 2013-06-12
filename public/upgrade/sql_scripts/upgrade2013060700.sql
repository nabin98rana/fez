ALTER TABLE %TABLE_PREFIX%record_search_key_isbn ADD COLUMN rek_isbn_order INT(11) DEFAULT 1 NULL AFTER rek_isbn;
CREATE UNIQUE INDEX unique_constraint_pid_order ON %TABLE_PREFIX%record_search_key_isbn (rek_isbn_pid, rek_isbn_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_isbn DROP INDEX rek_isbn_pid;
UPDATE %TABLE_PREFIX%search_key SET sek_cardinality = '1' WHERE sek_id = 'core_64';

ALTER TABLE %TABLE_PREFIX%record_search_key_issn ADD COLUMN rek_issn_order INT(11) DEFAULT 1 NULL AFTER rek_issn;
CREATE UNIQUE INDEX unique_constraint_pid_order ON %TABLE_PREFIX%record_search_key_issn (rek_issn_pid, rek_issn_order);
ALTER TABLE %TABLE_PREFIX%record_search_key_issn DROP INDEX rek_issn_pid;
UPDATE %TABLE_PREFIX%search_key SET sek_cardinality = '1' WHERE sek_id = 'core_65';
