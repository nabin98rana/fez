ALTER TABLE %TABLE_PREFIX%config
  CHANGE COLUMN config_value config_value varchar(512) default NULL;
