ALTER TABLE %TABLE_PREFIX%datastream_info__shadow ADD COLUMN dsi_shadow_version varchar(100) NOT NULL AFTER dsi_stamp;