ALTER TABLE %TABLE_PREFIX%xsd_display_matchfields
ADD COLUMN xsdmf_show_simple_create  tinyint(1) DEFAULT 1,
ADD COLUMN xsdmf_xpath TEXT DEFAULT NULL;
