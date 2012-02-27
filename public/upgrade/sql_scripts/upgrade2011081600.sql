DELETE FROM %TABLE_PREFIX%search_key WHERE sek_id = 'core_92';
INSERT INTO %TABLE_PREFIX%search_key (sek_id,sek_namespace,sek_incr_id,sek_title,sek_alt_title,sek_desc,sek_adv_visible,sek_simple_used,sek_myfez_visible,sek_order,sek_html_input,sek_fez_variable,sek_smarty_variable,sek_cvo_id,sek_lookup_function,sek_data_type,sek_relationship,sek_meta_header,sek_cardinality,sek_suggest_function,sek_faceting, sek_derived_function, sek_bulkchange, sek_lookup_id_function) VALUES ('core_92','core','92','Subtype','','0','0','0','0','0','text','none','450005','451780','','int','0','0','0','','0','','0','');

ALTER TABLE %TABLE_PREFIX%record_search_key
    ADD COLUMN rek_subtype_xsdmf_id int(11), 
    ADD COLUMN rek_subtype varchar(255);