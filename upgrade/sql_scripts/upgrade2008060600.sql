ALTER TABLE %TABLE_PREFIX%custom_views_community ADD COLUMN cvcom_hostname varchar(255) COLLATE utf8_general_ci;
ALTER TABLE %TABLE_PREFIX%custom_views 
DROP COLUMN cview_header_tpl,
DROP COLUMN cview_content_tpl,
DROP COLUMN cview_css,
DROP COLUMN cview_footer_tpl;
