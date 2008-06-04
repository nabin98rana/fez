DELETE FROM %TABLE_PREFIX%statistics_all WHERE stl_dsid LIKE 'thumbnail_%';
DELETE FROM %TABLE_PREFIX%statistics_all WHERE stl_dsid LIKE 'preview_%';
DELETE FROM %TABLE_PREFIX%statistics_all WHERE stl_dsid LIKE 'presmd_%';
UPDATE %TABLE_PREFIX%record_search_key r1 SET rek_file_downloads = (SELECT COUNT(*) FROM %TABLE_PREFIX%statistics_all WHERE stl_dsid <> '' AND stl_pid = r1.rek_pid);