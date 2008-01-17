CREATE TABLE %TABLE_PREFIX%user_shibboleth_attribs (
    usa_usr_id      int(11) unsigned,
    usa_shib_name   varchar(100),
    usa_shib_value  varchar(255),
    PRIMARY KEY(usa_usr_id, usa_shib_name)
);