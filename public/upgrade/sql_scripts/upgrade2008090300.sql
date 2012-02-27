UPDATE %TABLE_PREFIX%record_search_key r1
SET rek_file_downloads = (
SELECT COUNT(*) FROM %TABLE_PREFIX%statistics_all
WHERE stl_dsid <> '' AND stl_pid = r1.rek_pid AND stl_counter_bad = 0),
rek_views = (
SELECT COUNT(*) FROM %TABLE_PREFIX%statistics_all
WHERE stl_dsid = '' AND stl_pid = r1.rek_pid AND stl_counter_bad = 0);