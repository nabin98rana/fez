ALTER TABLE %TABLE_PREFIX%datastream_info ADD dsi_mimetype VARCHAR(128) NULL;
ALTER TABLE %TABLE_PREFIX%datastream_info ADD dsi_url TEXT;
ALTER TABLE %TABLE_PREFIX%datastream_info ADD dsi_copyright CHAR(1) NULL;
ALTER TABLE %TABLE_PREFIX%datastream_info ADD dsi_watermark CHAR(1) NULL;
ALTER TABLE %TABLE_PREFIX%datastream_info ADD dsi_security_inherited CHAR(1) NULL;
ALTER TABLE %TABLE_PREFIX%datastream_info ADD dsi_state CHAR(1) NULL;
