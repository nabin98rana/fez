CREATE TABLE %TABLE_PREFIX%auth_quick_template (
    qat_id int(11) unsigned NOT NULL auto_increment,
    qat_title varchar(100) character set utf8 default NULL,
    qat_value text character set utf8,
    PRIMARY KEY(qat_id)
);

