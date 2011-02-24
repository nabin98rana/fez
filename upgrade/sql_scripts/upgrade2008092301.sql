CREATE TABLE %TABLE_PREFIX%record_search_key_scopus_id ( 
     rek_scopus_id_id int(11) NOT NULL auto_increment, 
     rek_scopus_id_pid varchar(64) default NULL, 
     rek_scopus_id_xsdmf_id int(11) default NULL,
      rek_scopus_id varchar(255) default NULL, 
     PRIMARY KEY (rek_scopus_id_id), 
     KEY rek_scopus_id (rek_scopus_id), 
     KEY rek_scopus_id_pid (rek_scopus_id_pid) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
