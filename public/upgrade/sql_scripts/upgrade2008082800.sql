ALTER TABLE %TABLE_PREFIX%statistics_all
ADD COLUMN stl_usr_id  int(11) unsigned DEFAULT NULL;

CREATE TABLE %TABLE_PREFIX%statistics_buffer (
str_id int(11) unsigned NOT NULL auto_increment,
str_ip varchar(64) character set utf8 default NULL,
str_usr_id int(11) default NULL,
str_request_date datetime default NULL,
str_pid varchar(255) character set utf8 default NULL,
str_dsid varchar(255) character set utf8 default NULL,
PRIMARY KEY  (str_id)
);
