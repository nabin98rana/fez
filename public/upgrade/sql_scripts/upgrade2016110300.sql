ALTER TABLE %TABLE_PREFIX%datastream_info ADD COLUMN dsi_cached TEXT DEFAULT NULL AFTER dsi_label;
ALTER TABLE %TABLE_PREFIX%datastream_info__shadow ADD COLUMN dsi_cached TEXT DEFAULT NULL AFTER dsi_label;
