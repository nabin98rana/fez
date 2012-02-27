REPLACE INTO %TABLE_PREFIX%search_key VALUES (73,'Sequence','',0,0,0,999,'text','none','',450005,NULL,'int',0,''); 
ALTER TABLE %TABLE_PREFIX%record_search_key
        add column rek_sequence int(11) NULL  DEFAULT 0 COMMENT 'Sequence order in a parent object';
ALTER TABLE  %TABLE_PREFIX%record_search_key add index rek_sequence (rek_sequence);
