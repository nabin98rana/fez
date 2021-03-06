ALTER TABLE %TABLE_PREFIX%config
  CHANGE COLUMN config_value config_value varchar(256) default NULL;

replace into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('1','datamodel_version','core','2007102500');
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('2','webserver_log_statistics','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('3','webserver_log_dir','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('4','webserver_log_file','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('5','app_geoip_path','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('6','shib_switch','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('7','shib_direct_login','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('8','shib_federation_name','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('9','shib_survey','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('10','shib_federation','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('11','shib_home_sp','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('12','shib_home_idp','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('13','shib_wayf_metadata_location','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('14','app_fedora_version','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('15','app_fedora_username','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('16','app_fedora_pwd','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('17','fedora_db_host','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('18','fedora_db_type','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('19','fedora_db_database_name','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('20','fedora_db_username','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('21','fedora_db_passwd','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('22','fedora_db_port','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('23','app_shaded_bar','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('24','app_cell_color','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('25','app_value_color','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('26','app_light_color','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('27','app_selected_color','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('28','app_middle_color','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('29','app_dark_color','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('30','app_heading_color','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('31','app_cycle_color_one','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('32','app_internal_color','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('33','app_fedora_setup','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('34','app_fedora_location','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('35','app_fedora_ssl_location','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('36','ldap_switch','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('37','ldap_organisation','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('38','ldap_root_dn','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('39','ldap_prefix','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('40','ldap_server','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('41','ldap_port','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('42','eprints_oai','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('43','eprints_username','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('44','eprints_passwd','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('45','eprints_subject_authority','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('46','eprints_db_host','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('47','eprints_db_type','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('48','eprints_db_database_name','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('49','eprints_db_username','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('50','eprints_db_passwd','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('51','eprints_import_users','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('52','self_registration','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('53','app_hostname','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('54','app_name','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('55','app_admin_email','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('56','app_org_name','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('57','app_short_org_name','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('58','app_pid_namespace','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('59','app_url','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('60','app_relative_url','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('61','app_image_preview_max_width','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('62','app_image_preview_max_height','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('63','app_https','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('64','app_disable_password_checking','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('65','app_debug_level','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('66','app_display_error_level','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('67','app_display_errors_user','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('70','app_system_user_id','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('71','app_email_system_from_address','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('72','app_email_smtp','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('73','app_watermark','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('74','app_thumbnail_width','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('75','app_thumbnail_height','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('76','app_image_web_max_width','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('77','app_image_web_max_height','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('78','app_default_user_timezone','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('79','app_cycle_color_two','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('80','app_san_import_dir','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('81','app_default_refresh_rate','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('82','app_temp_dir','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('83','app_convert_cmd','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('84','app_composite_cmd','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('85','app_identify_cmd','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('86','app_jhove_dir','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('87','app_dot_exec','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('88','app_php_exec','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('89','app_pdftotext_exec','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('90','app_sql_cache','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('91','app_default_pager_size','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('92','app_version','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('93','app_cookie','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('94','app_https_curl_check_cert','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('95','batch_import_type','core',NULL);
insert into %TABLE_PREFIX%config (config_id, config_name, config_module, config_value) values('96','app_link_prefix','core',NULL);
