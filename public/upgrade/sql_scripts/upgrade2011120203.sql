INSERT INTO %TABLE_PREFIX%search_key
VALUES
 ('UQ_27','UQ','27','Scopus Doc Type','','','1','0','0','100','combo','none','$scopus_doctypes','453226','Scopus::getAssocDocTypes','varchar','0','','0','','1','','','1'),
 ('UQ_28','UQ','28','WoK Doc Type','','','1','0','0','100','combo','none','$wok_doctypes','453226','Wok::getAssocDocTypes','varchar','0','','0','','0','','','1');

ALTER TABLE %TABLE_PREFIX%record_search_key
    ADD COLUMN rek_scopus_doc_type_xsdmf_id int(11),
    ADD COLUMN rek_scopus_doc_type varchar(255),
    ADD COLUMN rek_wok_doc_type_xsdmf_id int(11),
    ADD COLUMN rek_wok_doc_type varchar(255);