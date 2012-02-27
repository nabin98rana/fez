REPLACE INTO %TABLE_PREFIX%config (config_name,config_module,config_value) VALUES ('shib_wayf_url', 'core', 'https://ds.test.aaf.edu.au/discovery/DS');
REPLACE INTO %TABLE_PREFIX%config (config_name,config_module,config_value) VALUES ('shib_version', 'core', '2');
REPLACE INTO %TABLE_PREFIX%config (config_name,config_module,config_value) VALUES ('shib_wayf_js', 'core', 'https://ds.test.aaf.edu.au/discovery/DS/embedded-wayf.js');
REPLACE INTO %TABLE_PREFIX%config (config_name,config_module,config_value) VALUES ('shib_nonjs_url', 'core', '/Shibboleth.sso/DS?target=https://manager.aaf.edu.au/rr/');
REPLACE INTO %TABLE_PREFIX%config (config_name,config_module,config_value) VALUES ('shib_cache_attribs', 'core', 'OFF');
