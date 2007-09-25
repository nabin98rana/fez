ALTER TABLE %TABLE_PREFIX%record_search_key 
	add column `rek_downloads` int(11) NULL  DEFAULT '0' COMMENT 'Sum of all binary M datastream downloads', 
	add column `rek_views` int(11) NULL  DEFAULT '0' COMMENT 'Sum of all metadata views';
	
ALTER TABLE  %TABLE_PREFIX%record_search_key add index `rek_downloads` (`rek_downloads`);	
ALTER TABLE  %TABLE_PREFIX%record_search_key add index `rek_views` (`rek_views`);	