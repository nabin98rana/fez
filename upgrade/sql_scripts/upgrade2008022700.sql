CREATE TABLE %TABLE_PREFIX%custom_views (
    cview_id int(11) unsigned auto_increment,
    cview_name varchar(100),
    cview_header_tpl varchar(100),
    cview_content_tpl varchar(100),
    cview_footer_tpl varchar(100),
    cview_css varchar(100),
    cview_folder varchar(255),
    PRIMARY KEY(cview_id)
);

CREATE TABLE %TABLE_PREFIX%custom_views_search_keys (
    cvsk_id int(11) unsigned auto_increment,
    cvsk_cview_id int(11) unsigned,
    cvsk_sek_id varchar(64),
    cvsk_sek_name varchar(100),
    cvsk_order mediumint,
    PRIMARY KEY(cvsk_id)
);

CREATE TABLE %TABLE_PREFIX%custom_views_community (
    cvcom_id int(11) unsigned auto_increment,
    cvcom_cview_id int(11) unsigned,
    cvcom_com_pid varchar(64),
    PRIMARY KEY(cvcom_id)
);
