ALTER TABLE %TABLE_PREFIX%premis_event ADD fulltext pre_detail (pre_detail);
ALTER TABLE %TABLE_PREFIX%premis_event ADD INDEX pre_usr_id (pre_usr_id);
ALTER TABLE %TABLE_PREFIX%user ADD COLUMN usr_real_last_login_date datetime DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE %TABLE_PREFIX%user ADD INDEX usr_real_last_login_date (usr_real_last_login_date);