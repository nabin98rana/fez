ALTER TABLE %TABLE_PREFIX%search_key
ADD COLUMN `sek_desc`  text DEFAULT NULL;
ALTER TABLE %TABLE_PREFIX%custom_views_search_keys
ADD COLUMN `cvsk_sek_desc`  text DEFAULT NULL;
