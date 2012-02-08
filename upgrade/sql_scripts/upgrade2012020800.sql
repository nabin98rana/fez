-- add config values used by Data Collection doc type (ANDS/RIF-CS). 
INSERT IGNORE INTO %TABLE_PREFIX%config (config_name, config_module, config_value ) VALUES ('app_org_address_postal','core','');
INSERT IGNORE INTO %TABLE_PREFIX%config (config_name, config_module, config_value ) VALUES ('handle_naming_authority_prefix','core','');