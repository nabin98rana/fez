CREATE TABLE %TABLE_PREFIX%record_search_key_oa_notes (
  rek_oa_notes_id int(11) NOT NULL AUTO_INCREMENT,
  rek_oa_notes_pid varchar(64) DEFAULT NULL,
  rek_oa_notes_xsdmf_id int(11) DEFAULT NULL,
  rek_oa_notes varchar(255) DEFAULT NULL,
  PRIMARY KEY (rek_oa_notes_id),
  KEY rek_oa_notes (rek_oa_notes),
  KEY rek_oa_notes_pid (rek_oa_notes_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%record_search_key_oa_compliance (
  rek_oa_compliance_id int(11) NOT NULL AUTO_INCREMENT,
  rek_oa_compliance_pid varchar(64) DEFAULT NULL,
  rek_oa_compliance_xsdmf_id int(11) DEFAULT NULL,
  rek_oa_compliance varchar(255) DEFAULT NULL,
  PRIMARY KEY (rek_oa_compliance_id),
  KEY rek_oa_compliance (rek_oa_compliance),
  KEY rek_oa_compliance_pid (rek_oa_compliance_pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into %TABLE_PREFIX%search_key (sek_id, sek_namespace, sek_incr_id, sek_title, sek_alt_title, sek_desc, sek_adv_visible, sek_simple_used, sek_myfez_visible, sek_order, sek_html_input, sek_fez_variable, sek_smarty_variable, sek_cvo_id, sek_lookup_function, sek_data_type, sek_relationship, sek_meta_header, sek_cardinality, sek_suggest_function, sek_comment_function, sek_faceting, sek_derived_function, sek_lookup_id_function, sek_bulkchange)
values('UQ_54','UQ','54','OA Compliance','','','0','0','0','999','contvocab','none','','453570','','varchar','1','','0','','','0','','','0');
insert into %TABLE_PREFIX%search_key (sek_id, sek_namespace, sek_incr_id, sek_title, sek_alt_title, sek_desc, sek_adv_visible, sek_simple_used, sek_myfez_visible, sek_order, sek_html_input, sek_fez_variable, sek_smarty_variable, sek_cvo_id, sek_lookup_function, sek_data_type, sek_relationship, sek_meta_header, sek_cardinality, sek_suggest_function, sek_comment_function, sek_faceting, sek_derived_function, sek_lookup_id_function, sek_bulkchange)
values('UQ_55','UQ','55','OA Notes','','','0','0','0','999','text','none','','453235','','varchar','1','','0','','','0','','','0');
