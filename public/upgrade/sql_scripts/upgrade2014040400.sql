-- Altmetric default configurations.
INSERT IGNORE INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('altmetric_api_enabled', 'core', 'false'); 
INSERT IGNORE INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('altmetric_api_url', 'core', 'http://api.altmetric.com/v1'); 
INSERT IGNORE INTO %TABLE_PREFIX%config (config_name, config_module) VALUES ('altmetric_api_key', 'core'); 
