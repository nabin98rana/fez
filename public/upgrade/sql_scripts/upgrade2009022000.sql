INSERT ignore INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('rid_dl_service_username','core','your_username_here');
INSERT ignore INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('rid_dl_service_password','core','your_password_here');
INSERT ignore INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('rid_dl_service_url','core','http://rid-dl-request.isiknowledge.com/esti/xrpc');
INSERT ignore INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('rid_dl_service_request_xsd','core','/path/to/download-request.xsd');
INSERT ignore INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('rid_dl_service_response_xsd','core','/path/to/download-response.xsd');
INSERT ignore INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('rid_ul_service_username','core','your_username_here');
INSERT ignore INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('rid_ul_service_password','core','your_password_here');
INSERT ignore INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('rid_ul_service_url','core','https://wok-ws.isiknowledge.com/esti/xrpc');
INSERT ignore INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('rid_ul_service_profiles_xsd','core','/path/to/Researcher-Bulk-Profiles-schema.xsd');
INSERT ignore INTO %TABLE_PREFIX%config (config_name, config_module, config_value) VALUES ('rid_ul_service_publications_xsd','core','/path/to/Researcher-Bulk-Publications-schema.xsd');
CREATE TABLE %TABLE_PREFIX%rid_jobs (
  rij_id int(11) unsigned NOT NULL auto_increment,
  rij_ticketno varchar(50) default NULL,
  rij_lastcheck timestamp NULL default NULL,
  rij_status varchar(15) default NULL,
  rij_count int(11) default NULL,
  rij_timestarted timestamp NULL default NULL,
  rij_timefinished timestamp NULL default NULL,
  rij_downloadrequest text,
  rij_lastresponse text,
  PRIMARY KEY  (rij_id)
);