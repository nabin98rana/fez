ALTER TABLE %TABLE_PREFIX%rid_profile_uploads 
ADD `rpu_response_status` VARCHAR(255)  NOT NULL  AFTER `rpu_response`;

ALTER TABLE %TABLE_PREFIX%rid_profile_uploads 
ADD `rpu_response_info` BLOB  NOT NULL  AFTER `rpu_response_status`;

ALTER TABLE %TABLE_PREFIX%rid_profile_uploads 
ADD `rpu_aut_org_username` VARCHAR(255)  NOT NULL  AFTER `rpu_response_info`;

