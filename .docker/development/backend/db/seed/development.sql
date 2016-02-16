UPDATE fez_config
SET config_value = 'OFF'
WHERE config_name = 'app_analytics_switch';

UPDATE fez_config
SET config_value = 'ON'
WHERE config_name = 'app_solr_local_file';

UPDATE fez_config
SET config_value = '/var/cache/solr_upload/'
WHERE config_name = 'app_solr_local_file_path';

UPDATE fez_config
SET config_value = 'OFF'
WHERE config_name = 'app_piwik_switch';

UPDATE fez_config
SET config_value = 'fedoradb'
WHERE config_name = 'fedora_db_host';

UPDATE fez_config
SET config_value = 'fedora3'
WHERE config_name = 'fedora_db_database_name';

UPDATE fez_config
SET config_value = 'fedoraAdmin'
WHERE config_name = 'fedora_db_username';

UPDATE fez_config
SET config_value = 'fedoraAdmin'
WHERE config_name = 'fedora_db_passwd';

UPDATE fez_config
SET config_value = 'fedora:10081/fedora'
WHERE config_name = 'app_fedora_location';

UPDATE fez_config
SET config_value = 'fedoraAdmin'
WHERE config_name = 'app_fedora_username';

UPDATE fez_config
SET config_value = 'fedoraAdmin'
WHERE config_name = 'app_fedora_pwd';

UPDATE fez_config
SET config_value = '/data'
WHERE config_name = 'app_fedora_path_direct';

UPDATE fez_config
SET config_value = 'ON'
WHERE config_name = 'app_fedora_apia_direct';

UPDATE fez_config
SET config_value = 'dev-fez.library.uq.edu.au:8080'
WHERE config_name = 'app_hostname';

UPDATE fez_config
SET config_value = 'OFF'
WHERE config_name = 'app_https';

UPDATE fez_config
SET config_value = '8983'
WHERE config_name = 'app_solr_port';

UPDATE fez_config
SET config_value = 'solr'
WHERE config_name = 'app_solr_host';

UPDATE fez_config
SET config_value = 'OFF'
WHERE config_name = 'app_solr_index_datastreams';

UPDATE fez_config
SET config_value = '/solr/'
WHERE config_name = 'app_solr_path';

UPDATE fez_config
SET config_value = 'ON'
WHERE config_name = 'app_solr_switch';

UPDATE fez_config
SET config_value = 'ON'
WHERE config_name = 'app_solr_indexer';

UPDATE fez_config
SET config_value = 'ON'
WHERE config_name = 'app_solr_index_datastreams';

UPDATE fez_config
SET config_value = '/var/cache/file/'
WHERE config_name = 'app_filecache_dir';

UPDATE fez_config
SET config_value = '/var/cache/templates_c/'
WHERE config_name = 'app_template_compile_path';

UPDATE fez_config
SET config_value = 'OFF'
WHERE config_name = 'app_solr_slave_read';

UPDATE fez_config
SET config_value = 'OFF'
WHERE config_name = 'app_fedora_bypass';

UPDATE fez_config
SET config_value = '/usr/bin/gs'
WHERE config_name = 'ghostscript_pth';

UPDATE fez_config
SET config_value = '/usr/local/bin/dot'
WHERE config_name = 'app_dot_exec';

UPDATE fez_config
SET config_value = '/var/log/fez/backend/app/fez-%Ymd%.log'
WHERE config_name = 'app_log_location';

UPDATE fez_config
SET config_value = '/usr/bin/yamdi'
WHERE config_name = 'app_ffmpeg_yamdi_cmd';

UPDATE fez_config
SET config_value = '/usr/bin/php'
WHERE config_name = 'app_php_exec';

UPDATE fez_config
SET config_value = 'nosslall'
WHERE config_name = 'app_fedora_setup';

UPDATE fez_config
SET config_value = '3'
WHERE config_name = 'app_fedora_version';

UPDATE fez_config
SET config_value = 'ON'
WHERE config_name = 'app_xpath_switch';

UPDATE fez_config
SET config_value = '/var/cache/tmp/'
WHERE config_name = 'app_temp_dir';

REPLACE INTO fez_user (usr_id, usr_username, usr_password, usr_full_name, usr_administrator, usr_super_administrator,
usr_ldap_authentication, usr_email, usr_preferences) VALUES
(999999990, 'admin_test', md5('Ilovedonkey5'), 'Test Admin', true, false, false, 'uqckorte@uq.edu.au', 'a:14:{s:7:"updated";N;s:6:"closed";N;s:6:"emails";N;s:5:"files";N;s:19:"close_popup_windows";N;s:23:"receive_assigned_emails";N;s:18:"receive_new_emails";N;s:8:"timezone";s:18:"Australia/Brisbane";s:17:"list_refresh_rate";N;s:19:"emails_refresh_rate";N;s:15:"email_signature";N;s:10:"front_page";s:10:"front_page";s:15:"auto_append_sig";N;s:22:"remember_search_params";s:3:"yes";}');

REPLACE INTO fez_user (usr_id, usr_username, usr_password, usr_full_name, usr_administrator, usr_super_administrator,
usr_ldap_authentication, usr_email, usr_preferences) VALUES
(999999991, 'superadmin_test', md5('Ilovedonkey5'), 'Test Super Admin', true, true, false, 'uqckorte@uq.edu.au', 'a:14:{s:7:"updated";N;s:6:"closed";N;s:6:"emails";N;s:5:"files";N;s:19:"close_popup_windows";N;s:23:"receive_assigned_emails";N;s:18:"receive_new_emails";N;s:8:"timezone";s:18:"Australia/Brisbane";s:17:"list_refresh_rate";N;s:19:"emails_refresh_rate";N;s:15:"email_signature";N;s:10:"front_page";s:10:"front_page";s:15:"auto_append_sig";N;s:22:"remember_search_params";s:3:"yes";}');

REPLACE INTO fez_user (usr_id, usr_username, usr_password, usr_full_name, usr_administrator, usr_super_administrator,
usr_ldap_authentication, usr_email, usr_preferences) VALUES
(999999992, 'upo_test', md5('Ilovedonkey5'), 'Test UPO User', false, false, false, 'uqckorte@uq.edu.au', 'a:14:{s:7:"updated";N;s:6:"closed";N;s:6:"emails";N;s:5:"files";N;s:19:"close_popup_windows";N;s:23:"receive_assigned_emails";N;s:18:"receive_new_emails";N;s:8:"timezone";s:18:"Australia/Brisbane";s:17:"list_refresh_rate";N;s:19:"emails_refresh_rate";N;s:15:"email_signature";N;s:10:"front_page";s:10:"front_page";s:15:"auto_append_sig";N;s:22:"remember_search_params";s:3:"yes";}');

REPLACE INTO fez_group_user (gpu_grp_id, gpu_usr_id) VALUES (87, 999999992);

REPLACE INTO fez_user (usr_id, usr_username, usr_password, usr_full_name, usr_administrator, usr_super_administrator,
usr_ldap_authentication, usr_email, usr_preferences) VALUES
(999999993, 'user_test', md5('Ilovedonkey5'), 'Test User', false, false, false, 'uqckorte@uq.edu.au', 'a:14:{s:7:"updated";N;s:6:"closed";N;s:6:"emails";N;s:5:"files";N;s:19:"close_popup_windows";N;s:23:"receive_assigned_emails";N;s:18:"receive_new_emails";N;s:8:"timezone";s:18:"Australia/Brisbane";s:17:"list_refresh_rate";N;s:19:"emails_refresh_rate";N;s:15:"email_signature";N;s:10:"front_page";s:10:"front_page";s:15:"auto_append_sig";N;s:22:"remember_search_params";s:3:"yes";}');

REPLACE INTO fez_user (usr_id, usr_username, usr_password, usr_full_name, usr_administrator, usr_super_administrator,
usr_ldap_authentication, usr_email, usr_preferences) VALUES
(999999994, 'thesisofficer_test', md5('Ilovedonkey5'), 'Test Thesis Officer', false, false, false, 'uqckorte@uq.edu.au', 'a:14:{s:7:"updated";N;s:6:"closed";N;s:6:"emails";N;s:5:"files";N;s:19:"close_popup_windows";N;s:23:"receive_assigned_emails";N;s:18:"receive_new_emails";N;s:8:"timezone";s:18:"Australia/Brisbane";s:17:"list_refresh_rate";N;s:19:"emails_refresh_rate";N;s:15:"email_signature";N;s:10:"front_page";s:10:"front_page";s:15:"auto_append_sig";N;s:22:"remember_search_params";s:3:"yes";}');

REPLACE INTO fez_group_user (gpu_grp_id, gpu_usr_id) VALUES (70, 999999994);

DELETE FROM fez_fulltext_queue;
DELETE FROM fez_fulltext_locks;

INSERT INTO fez_fulltext_queue (ftq_pid, ftq_op)
SELECT rek_pid, 'I'
FROM fez_record_search_key WHERE rek_updated_date
BETWEEN DATE_SUB(NOW(), INTERVAL 2 DAY) AND NOW(); 

insert ignore into fez_config (config_name, config_module, config_value) values ('tag_upload_files','core','ON');

UPDATE fez_xsd_display_matchfields SET xsdmf_invisible = '1' WHERE xsdmf_title = 'Description for File Upload';

INSERT INTO fez_group_user (gpu_grp_id, gpu_usr_id) VALUES (90, 999999991);

UPDATE fez_config
SET config_value = ''
WHERE config_value = "'";
