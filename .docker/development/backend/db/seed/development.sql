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
WHERE config_name = 'app_analytics_switch';

UPDATE fez_config
SET config_value = 'OFF'
WHERE config_name = 'app_piwik_switch';

UPDATE fez_config
SET config_value = 'fezdb'
WHERE config_name = 'fedora_db_host';

UPDATE fez_config
SET config_value = 'fez'
WHERE config_name = 'fedora_db_database_name';

UPDATE fez_config
SET config_value = 'fez'
WHERE config_name = 'fedora_db_username';

UPDATE fez_config
SET config_value = 'fez'
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
SET config_value = 'OFF'
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

UPDATE fez_config
SET config_value = '/var/cache/dstree/'
WHERE config_name = 'app_dstree_path';

UPDATE fez_config
SET config_value = '/espace_san/incoming/'
WHERE config_name = 'app_san_import_dir';

REPLACE INTO fez_user (usr_id, usr_username, usr_password, usr_full_name, usr_administrator, usr_super_administrator,
usr_ldap_authentication, usr_email, usr_preferences) VALUES
(999999990, 'admin_test', md5('Ilovedonkey5'), 'Test Admin', true, false, false, 'uqckorte@uq.edu.au', 'a:14:{s:7:"updated";N;s:6:"closed";N;s:6:"emails";N;s:5:"files";N;s:19:"close_popup_windows";N;s:23:"receive_assigned_emails";N;s:18:"receive_new_emails";N;s:8:"timezone";s:18:"Australia/Brisbane";s:17:"list_refresh_rate";N;s:19:"emails_refresh_rate";N;s:15:"email_signature";N;s:10:"front_page";s:10:"front_page";s:15:"auto_append_sig";N;s:22:"remember_search_params";s:3:"yes";}'),
(999999991, 'superadmin_test', md5('Ilovedonkey5'), 'Test Super Admin', true, true, false, 'uqckorte@uq.edu.au', 'a:14:{s:7:"updated";N;s:6:"closed";N;s:6:"emails";N;s:5:"files";N;s:19:"close_popup_windows";N;s:23:"receive_assigned_emails";N;s:18:"receive_new_emails";N;s:8:"timezone";s:18:"Australia/Brisbane";s:17:"list_refresh_rate";N;s:19:"emails_refresh_rate";N;s:15:"email_signature";N;s:10:"front_page";s:10:"front_page";s:15:"auto_append_sig";N;s:22:"remember_search_params";s:3:"yes";}'),
(999999992, 'upo_test', md5('Ilovedonkey5'), 'Test UPO User', false, false, false, 'uqckorte@uq.edu.au', 'a:14:{s:7:"updated";N;s:6:"closed";N;s:6:"emails";N;s:5:"files";N;s:19:"close_popup_windows";N;s:23:"receive_assigned_emails";N;s:18:"receive_new_emails";N;s:8:"timezone";s:18:"Australia/Brisbane";s:17:"list_refresh_rate";N;s:19:"emails_refresh_rate";N;s:15:"email_signature";N;s:10:"front_page";s:10:"front_page";s:15:"auto_append_sig";N;s:22:"remember_search_params";s:3:"yes";}'),
(999999993, 'user_test', md5('Ilovedonkey5'), 'Test User', false, false, false, 'uqckorte@uq.edu.au', 'a:14:{s:7:"updated";N;s:6:"closed";N;s:6:"emails";N;s:5:"files";N;s:19:"close_popup_windows";N;s:23:"receive_assigned_emails";N;s:18:"receive_new_emails";N;s:8:"timezone";s:18:"Australia/Brisbane";s:17:"list_refresh_rate";N;s:19:"emails_refresh_rate";N;s:15:"email_signature";N;s:10:"front_page";s:10:"front_page";s:15:"auto_append_sig";N;s:22:"remember_search_params";s:3:"yes";}'),
(999999994, 'thesisofficer_test', md5('Ilovedonkey5'), 'Test Thesis Officer', false, false, false, 'uqckorte@uq.edu.au', 'a:14:{s:7:"updated";N;s:6:"closed";N;s:6:"emails";N;s:5:"files";N;s:19:"close_popup_windows";N;s:23:"receive_assigned_emails";N;s:18:"receive_new_emails";N;s:8:"timezone";s:18:"Australia/Brisbane";s:17:"list_refresh_rate";N;s:19:"emails_refresh_rate";N;s:15:"email_signature";N;s:10:"front_page";s:10:"front_page";s:15:"auto_append_sig";N;s:22:"remember_search_params";s:3:"yes";}');

DELETE FROM fez_fulltext_queue;
DELETE FROM fez_fulltext_locks;

INSERT INTO fez_fulltext_queue (ftq_pid, ftq_op)
SELECT rek_pid, 'I'
FROM fez_record_search_key WHERE rek_updated_date
BETWEEN DATE_SUB(NOW(), INTERVAL 2 DAY) AND NOW();

insert ignore into fez_config (config_name, config_module, config_value) values ('tag_upload_files','core','ON');

UPDATE fez_xsd_display_matchfields SET xsdmf_invisible = '1' WHERE xsdmf_title = 'Description for File Upload';

UPDATE fez_config
SET config_value = ''
WHERE config_value = "'";

REPLACE INTO fez_group (grp_id, grp_title, grp_status, grp_created_date)
VALUES
	(1, 'Masqueraders', 'active', '2011-03-25 12:00:00'),
	(2, 'Thesis officers', 'active', '2011-03-25 12:00:00'),
	(3, 'UPOs', 'active', '2011-03-25 12:00:00');

REPLACE INTO fez_group_user (gpu_id, gpu_grp_id, gpu_usr_id)
VALUES
	(1, 2, 999999994),
	(2, 3, 999999992);

REPLACE INTO fez_auth_quick_template (qat_id, qat_title, qat_value)
VALUES
	(1, 'Masqueraders only', '<FezACML>\r\n  <rule>\r\n    <role name=\"Viewer\">\r\n      <in_AD>off</in_AD>\r\n      <in_Fez>off</in_Fez>\r\n      <eduPersonScopedAffiliation>masqueraders@example.com</eduPersonScopedAffiliation>\r\n      <Fez_Group>1</Fez_Group>\r\n    </role>\r\n  </rule>\r\n  <inherit_security>off</inherit_security>\r\n</FezACML>\r\n  '),
	(2, 'Thesis officers only', '<FezACML>\r\n  <rule>\r\n    <role name=\"Viewer\">\r\n      <in_AD>off</in_AD>\r\n      <in_Fez>off</in_Fez>\r\n      <eduPersonScopedAffiliation>thesisofficers@example.com</eduPersonScopedAffiliation>\r\n      <Fez_Group>2</Fez_Group>\r\n    </role>\r\n  </rule>\r\n  <inherit_security>off</inherit_security>\r\n</FezACML>\r\n  '),
	(3, 'UPOs only', '<FezACML>\r\n  <rule>\r\n    <role name=\"Viewer\">\r\n      <in_AD>off</in_AD>\r\n      <in_Fez>off</in_Fez>\r\n      <eduPersonScopedAffiliation>upos@example.com</eduPersonScopedAffiliation>\r\n      <Fez_Group>3</Fez_Group>\r\n    </role>\r\n  </rule>\r\n  <inherit_security>off</inherit_security>\r\n</FezACML>\r\n  ');

REPLACE INTO fez_author (aut_id, aut_org_username, aut_org_staff_id, aut_org_student_id, aut_display_name, aut_fname, aut_mname, aut_lname, aut_title, aut_position, aut_homepage_link, aut_created_date, aut_update_date, aut_external_id, aut_ref_num, aut_email, aut_mypub_url, aut_researcher_id, aut_scopus_id, aut_rid_password, aut_description, aut_people_australia_id, aut_orcid_id, aut_google_scholar_id, aut_rid_last_updated, aut_publons_id)
VALUES
	(1, NULL, NULL, NULL, 'UQ Author', 'UQ', NULL, 'Author', '', NULL, NULL, '2011-08-25', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
