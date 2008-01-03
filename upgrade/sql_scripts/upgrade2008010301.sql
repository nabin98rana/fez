REPLACE INTO %TABLE_PREFIX%search_key VALUES (75,'Genre Type','',0,0,0,999,'text','none','',450005,NULL,'varchar',0,''); 
ALTER TABLE %TABLE_PREFIX%record_search_key
  		add column `rek_genre_type_xsdmf_id` int(11) NULL,
        add column `rek_genre_type` varchar(255) NULL  COMMENT 'Genre Type';
ALTER TABLE  %TABLE_PREFIX%record_search_key add index `rek_genre_type` (`rek_genre_type`);
