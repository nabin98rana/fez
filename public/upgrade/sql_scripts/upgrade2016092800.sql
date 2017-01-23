insert ignore into %TABLE_PREFIX%config (config_name, config_module, config_value)
values ('app_es_switch','core','OFF'),
('app_es_host','core',''),
('app_es_indexer','core','OFF'),
('app_es_index_name','core','fez'),
('app_es_index_datastreams','core','ON'),
('app_es_facet_mincount', 'core', '2'),
('app_es_facet_limit', 'core', '5'),
('app_es_slave_host', 'core', ''),
('app_es_slave_read', 'core', 'OFF');