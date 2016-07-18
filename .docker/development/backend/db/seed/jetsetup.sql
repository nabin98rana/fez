UPDATE fez_config SET config_value = 'fez' WHERE config_name = 'app_hostname';
UPDATE fez_config SET config_value = 'false' WHERE config_name = 'app_solr_switch';
UPDATE fez_config SET config_value = 'false' WHERE config_name = 'app_solr_indexer';