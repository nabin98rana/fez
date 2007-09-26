ALTER TABLE  %TABLE_PREFIX%record_search_key drop key `rek_downloads`;
ALTER TABLE %TABLE_PREFIX%record_search_key change `rek_downloads` `rek_file_downloads` int(11) NULL  DEFAULT '0' COMMENT 'Sum of all binary M datastream downloads';
ALTER TABLE  %TABLE_PREFIX%record_search_key add index `rek_file_downloads` (`rek_file_downloads`);
REPLACE INTO %TABLE_PREFIX%search_key VALUES (16,'File Downloads','',0,0,0,999,'text','none','',450005,NULL,'int',0,'');	
