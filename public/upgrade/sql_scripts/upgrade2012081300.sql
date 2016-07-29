CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%record_search_key_doi ( 
     rek_doi_id INT(11) NOT NULL AUTO_INCREMENT, 
     rek_doi_pid VARCHAR(64) DEFAULT NULL, 
     rek_doi_xsdmf_id INT(11) DEFAULT NULL,
     rek_doi VARCHAR(255) DEFAULT NULL, 
     PRIMARY KEY (rek_doi_id), 
     KEY rek_doi (rek_doi), 
     KEY rek_doi_pid (rek_doi_pid)
) ENGINE=INNODB DEFAULT CHARSET=utf8;
