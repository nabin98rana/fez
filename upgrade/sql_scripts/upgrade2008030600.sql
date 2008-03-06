CREATE TABLE `%TABLE_PREFIX%fulltext_cache` (
	`ftc_id` int(11) NOT NULL auto_increment,
	`ftc_pid` varchar(64) default NULL,
	`rek_file_attachment_content_xsdmf_id` int(11) default NULL,
	`ftc_content` mediumtext,
	`ftc_dsid` varchar(64) NOT NULL default '',
	PRIMARY KEY  (`ftc_id`),
	UNIQUE KEY `ftc_key` USING BTREE (`ftc_pid`,`ftc_dsid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%TABLE_PREFIX%fulltext_locks` (
	`ftl_name` varchar(8) NOT NULL,
	`ftl_value` int(10) unsigned NOT NULL,
	`ftl_pid` int(10) unsigned default NULL,
	PRIMARY KEY  USING BTREE (`ftl_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE  `%TABLE_PREFIX%fulltext_queue` (
	`ftq_key` int(10) unsigned NOT NULL auto_increment,
	`ftq_pid` varchar(128) NOT NULL default '',
	`ftq_op` varchar(5) NOT NULL default '',
	PRIMARY KEY  (`ftq_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert ignore into %TABLE_PREFIX%config (`config_name`, `config_module`, `config_value`) 
values ('app_solr_switch','core','OFF'),('app_solr_host','core',''),('app_solr_port','core',''),('app_solr_path','core','');