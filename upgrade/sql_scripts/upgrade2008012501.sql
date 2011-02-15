REPLACE INTO %TABLE_PREFIX%search_key VALUES (76,'Formatted Title','',0,0,0,999,'text','none','',450005,NULL,'text',0,''); 
ALTER TABLE %TABLE_PREFIX%record_search_key
  		add column rek_formatted_title_xsdmf_id int(11) NULL,
        add column rek_formatted_title text NULL  COMMENT 'Formatted Title';
REPLACE INTO %TABLE_PREFIX%search_key VALUES (77,'Formatted Abstract','',0,0,0,999,'text','none','',450005,NULL,'text',0,''); 
ALTER TABLE %TABLE_PREFIX%record_search_key
  		add column rek_formatted_abstract_xsdmf_id int(11) NULL,
        add column rek_formatted_abstract text NULL  COMMENT 'Formatted Abstract';

