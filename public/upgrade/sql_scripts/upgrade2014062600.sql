ALTER TABLE  %TABLE_PREFIX%statistics_all add index pid_ds_id_counter (stl_pid, stl_dsid, stl_counter_bad);
ALTER TABLE  %TABLE_PREFIX%statistics_all add index pid_dsid_date_counter (stl_request_date, stl_pid,stl_dsid, stl_counter_bad);
