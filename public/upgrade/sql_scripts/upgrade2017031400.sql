INSERT INTO %TABLE_PREFIX%config (config_name, config_module, config_value ) VALUES ('s3_last_synced', 'core', '');
CREATE INDEX dsi_stamp ON %TABLE_PREFIX%datastream_info__shadow (dsi_stamp);
CREATE INDEX dsi_shadow_version ON %TABLE_PREFIX%datastream_info__shadow (dsi_shadow_version);
CREATE INDEX rek_source ON %TABLE_PREFIX%record_search_key__shadow (rek_source);
