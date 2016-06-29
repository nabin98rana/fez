ALTER TABLE %TABLE_PREFIX%record_search_key_grant_id ADD COLUMN rek_grant_id_order INT(11) DEFAULT 1 NULL AFTER rek_grant_id;
CREATE UNIQUE INDEX unique_constraint_pid_order ON %TABLE_PREFIX%record_search_key_grant_id (rek_grant_id_pid, rek_grant_id_order);
UPDATE %TABLE_PREFIX%search_key SET sek_cardinality = '1' WHERE sek_id = 'core_115';