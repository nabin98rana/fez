-- add derived function column to search keys
ALTER TABLE %TABLE_PREFIX%search_key add column sek_derived_function varchar(255);

-- add two search keys that use derived functions to generate their values
INSERT INTO %TABLE_PREFIX%search_key (sek_id, sek_namespace, sek_incr_id, sek_title, sek_adv_visible, sek_simple_used, sek_myfez_visible, sek_order, sek_html_input, sek_fez_variable, sek_data_type, sek_relationship, sek_cardinality, sek_faceting, sek_derived_function)
values 
    ('core_90', 'core', 99, 'First Author in Document derived', 0, 0, 0, 999, 'text', 'none', 'varchar', 1, 0, 0, 'Author::getFirstAuthorInDocument'),
    ('core_91', 'core', 100, 'First Author in Fez derived', 0, 0, 0, 999, 'text', 'none', 'varchar', 1, 0, 0, 'Author::getFirstAuthorInFez')
    ;


-- add the two tables that these new search keys will use
CREATE TABLE %TABLE_PREFIX%record_search_key_first_author_in_document_derived ( 
     rek_first_author_in_document_derived_id int(11) NOT NULL auto_increment, 
     rek_first_author_in_document_derived_pid varchar(64) default NULL, 
     rek_first_author_in_document_derived_xsdmf_id int(11) default NULL,
      rek_first_author_in_document_derived varchar(255) default NULL, 
     PRIMARY KEY (rek_first_author_in_document_derived_id), 
     KEY rek_first_author_in_document_derived (rek_first_author_in_document_derived), 
     KEY rek_first_author_in_document_derived_pid (rek_first_author_in_document_derived_pid) 
);

CREATE TABLE %TABLE_PREFIX%record_search_key_first_author_in_fez_derived ( 
     rek_first_author_in_fez_derived_id int(11) NOT NULL auto_increment, 
     rek_first_author_in_fez_derived_pid varchar(64) default NULL, 
     rek_first_author_in_fez_derived_xsdmf_id int(11) default NULL,
      rek_first_author_in_fez_derived varchar(255) default NULL, 
     PRIMARY KEY (rek_first_author_in_fez_derived_id), 
     KEY rek_first_author_in_fez_derived (rek_first_author_in_fez_derived), 
     KEY rek_first_author_in_fez_derived_pid (rek_first_author_in_fez_derived_pid) 
);