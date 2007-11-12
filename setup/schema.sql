/*Table structure for table `%TABLE_PREFIX%auth_index2` */

CREATE TABLE `%TABLE_PREFIX%auth_index2` (
  `authi_pid` varchar(64) character set utf8 collate utf8_bin NOT NULL default '',
  `authi_role` int(11) unsigned NOT NULL default '0',
  `authi_arg_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`authi_pid`,`authi_role`,`authi_arg_id`),
  KEY `authi_role_arg_id` (`authi_role`,`authi_arg_id`),
  KEY `authi_role` (`authi_pid`,`authi_role`),
  KEY `authi_pid_arg_id` (`authi_pid`,`authi_arg_id`),
  KEY `authi_pid` (`authi_pid`),
  KEY `authi_arg_id` (`authi_arg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%auth_index2_lister` */

CREATE TABLE `%TABLE_PREFIX%auth_index2_lister` (
  `authi_pid` varchar(64) character set utf8 collate utf8_bin NOT NULL,
  `authi_arg_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`authi_pid`,`authi_arg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%auth_index2_pre_fez2_upgrade` */

CREATE TABLE `%TABLE_PREFIX%auth_index2_pre_fez2_upgrade` (
  `authi_id` int(11) NOT NULL auto_increment,
  `authi_pid` varchar(64) NOT NULL,
  `authi_role` varchar(64) NOT NULL,
  `authi_arg_id` int(11) NOT NULL,
  `authi_pid_num` int(11) NOT NULL,
  PRIMARY KEY  (`authi_id`),
  KEY `authi_pid` (`authi_pid`),
  KEY `authi_role` (`authi_role`),
  KEY `authi_arg_id` (`authi_arg_id`),
  KEY `authi_role_pid` (`authi_pid`,`authi_role`)
) ENGINE=MyISAM AUTO_INCREMENT=393 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%auth_roles` */

CREATE TABLE `%TABLE_PREFIX%auth_roles` (
  `aro_id` int(11) unsigned NOT NULL auto_increment,
  `aro_role` varchar(64) NOT NULL,
  `aro_ranking` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`aro_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%auth_rule_group_rules` */

CREATE TABLE `%TABLE_PREFIX%auth_rule_group_rules` (
  `argr_arg_id` int(11) NOT NULL,
  `argr_ar_id` int(11) NOT NULL,
  KEY `argr_arg_id` (`argr_arg_id`),
  KEY `argr_ar_id` (`argr_ar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%auth_rule_group_users` */

CREATE TABLE `%TABLE_PREFIX%auth_rule_group_users` (
  `argu_id` int(11) NOT NULL auto_increment,
  `argu_usr_id` int(11) NOT NULL,
  `argu_arg_id` int(11) NOT NULL,
  PRIMARY KEY  (`argu_id`),
  KEY `argu_usr_id` (`argu_usr_id`),
  KEY `argu_arg_id` (`argu_arg_id`)
) ENGINE=MyISAM AUTO_INCREMENT=64 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%auth_rule_groups` */

CREATE TABLE `%TABLE_PREFIX%auth_rule_groups` (
  `arg_id` int(11) NOT NULL auto_increment,
  `arg_md5` varchar(128) NOT NULL,
  PRIMARY KEY  (`arg_id`),
  KEY `arg_md5` (`arg_md5`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%auth_rules` */

CREATE TABLE `%TABLE_PREFIX%auth_rules` (
  `ar_id` int(11) NOT NULL auto_increment,
  `ar_rule` varchar(64) NOT NULL,
  `ar_value` varchar(255) NOT NULL,
  PRIMARY KEY  (`ar_id`),
  KEY `ar_value` (`ar_value`),
  FULLTEXT KEY `ar_rule` (`ar_rule`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%author` */

CREATE TABLE `%TABLE_PREFIX%author` (
  `aut_id` int(11) unsigned NOT NULL auto_increment,
  `aut_org_username` varchar(255) character set utf8 default NULL,
  `aut_org_staff_id` varchar(255) character set utf8 default NULL,
  `aut_display_name` varchar(255) character set utf8 default NULL,
  `aut_fname` varchar(255) character set utf8 default NULL,
  `aut_mname` varchar(255) character set utf8 default NULL,
  `aut_lname` varchar(255) character set utf8 default NULL,
  `aut_title` varchar(255) character set utf8 default NULL,
  `aut_position` varchar(255) character set utf8 default NULL,
  `aut_function` varchar(255) character set utf8 default NULL,
  `aut_cv_link` varchar(255) character set utf8 default NULL,
  `aut_homepage_link` varchar(255) character set utf8 default NULL,
  `aut_assessed` varchar(1) default NULL,
  `aut_created_date` date default NULL,
  `aut_update_date` date default NULL,
  `aut_external_id` varchar(50) default NULL,
  PRIMARY KEY  (`aut_id`),
  UNIQUE KEY `aut_org_staff_id` (`aut_org_staff_id`),
  FULLTEXT KEY `aut_fname` (`aut_fname`,`aut_lname`),
  FULLTEXT KEY `aut_display_name` (`aut_display_name`)
) ENGINE=MyISAM AUTO_INCREMENT=112250 DEFAULT CHARSET=latin1;

/*Table structure for table `%TABLE_PREFIX%author_affiliation` */

CREATE TABLE `%TABLE_PREFIX%author_affiliation` (
  `af_id` int(10) unsigned NOT NULL auto_increment,
  `af_pid` varchar(32) NOT NULL,
  `af_author_id` int(11) NOT NULL,
  `af_percent_affiliation` int(11) NOT NULL,
  `af_org_id` int(11) NOT NULL,
  PRIMARY KEY  (`af_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%author_classification` */

CREATE TABLE `%TABLE_PREFIX%author_classification` (
  `cla_id` int(11) unsigned NOT NULL auto_increment,
  `cla_title` varchar(64) default NULL,
  PRIMARY KEY  (`cla_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%author_function` */

CREATE TABLE `%TABLE_PREFIX%author_function` (
  `fun_id` int(11) unsigned NOT NULL auto_increment,
  `fun_title` varchar(64) default NULL,
  PRIMARY KEY  (`fun_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%author_org_structure` */

CREATE TABLE `%TABLE_PREFIX%author_org_structure` (
  `auo_id` int(11) unsigned NOT NULL auto_increment,
  `auo_org_id` int(11) unsigned default NULL,
  `auo_aut_id` int(11) unsigned default NULL,
  `auo_cla_id` int(11) unsigned default NULL,
  `auo_fun_id` int(11) unsigned default NULL,
  `auo_assessed` varchar(1) default NULL,
  `auo_assessed_year` varchar(11) default NULL,
  PRIMARY KEY  (`auo_id`),
  UNIQUE KEY `support_unique_key` (`auo_org_id`,`auo_aut_id`,`auo_cla_id`,`auo_fun_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `%TABLE_PREFIX%background_process` */

CREATE TABLE `%TABLE_PREFIX%background_process` (
  `bgp_id` int(11) NOT NULL auto_increment,
  `bgp_status_message` text,
  `bgp_progress` int(11) default NULL,
  `bgp_usr_id` varchar(255) default NULL,
  `bgp_state` int(11) default NULL,
  `bgp_heartbeat` datetime default NULL,
  `bgp_serialized` text,
  `bgp_include` varchar(255) default NULL,
  `bgp_name` varchar(255) default NULL,
  `bgp_started` datetime default NULL,
  `bgp_filename` varchar(255) default NULL,
  `bgp_headers` text,
  PRIMARY KEY  (`bgp_id`),
  KEY `bgp_started` (`bgp_started`),
  KEY `bgp_state` (`bgp_state`),
  KEY `bgp_usr_id` (`bgp_usr_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3011 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%citation` */

CREATE TABLE `%TABLE_PREFIX%citation` (
  `cit_id` int(11) NOT NULL auto_increment,
  `cit_xdis_id` int(11) NOT NULL,
  `cit_template` text NOT NULL,
  `cit_type` varchar(10) NOT NULL,
  PRIMARY KEY  (`cit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=latin1;

/*Table structure for table `%TABLE_PREFIX%config` */

CREATE TABLE `%TABLE_PREFIX%config` (
  `config_id` int(11) NOT NULL auto_increment,
  `config_name` varchar(32) NOT NULL,
  `config_module` varchar(32) NOT NULL,
  `config_value` varchar(512) default NULL,
  PRIMARY KEY  (`config_id`),
  UNIQUE KEY `config_name` (`config_name`,`config_module`)
) ENGINE=MyISAM AUTO_INCREMENT=99 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%controlled_vocab` */

CREATE TABLE `%TABLE_PREFIX%controlled_vocab` (
  `cvo_id` int(11) unsigned NOT NULL auto_increment,
  `cvo_title` varchar(255) default NULL,
  `cvo_desc` varchar(255) default NULL,
  `cvo_image_filename` varchar(64) default NULL,
  `cvo_external_id` int(11) default NULL,
  PRIMARY KEY  (`cvo_id`),
  UNIQUE KEY `cvo_id` (`cvo_id`),
  KEY `cvo_title` (`cvo_title`)
) ENGINE=MyISAM AUTO_INCREMENT=450779 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%controlled_vocab_relationship` */

CREATE TABLE `%TABLE_PREFIX%controlled_vocab_relationship` (
  `cvr_id` int(11) unsigned NOT NULL auto_increment,
  `cvr_parent_cvo_id` int(11) unsigned NOT NULL default '0',
  `cvr_child_cvo_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cvr_id`),
  UNIQUE KEY `cvr_parent_cvo_id` (`cvr_parent_cvo_id`,`cvr_child_cvo_id`,`cvr_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1913 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%eprints_import_pids` */

CREATE TABLE `%TABLE_PREFIX%eprints_import_pids` (
  `epr_eprints_id` int(11) NOT NULL,
  `epr_fez_pid` varchar(255) NOT NULL,
  `epr_date_added` datetime default NULL,
  PRIMARY KEY  (`epr_eprints_id`,`epr_fez_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%fulltext_engine` */

CREATE TABLE `%TABLE_PREFIX%fulltext_engine` (
  `fte_id` int(11) NOT NULL auto_increment,
  `fte_fti_id` mediumint(9) NOT NULL default '0',
  `fte_key_id` mediumint(9) NOT NULL default '0',
  `fte_weight` smallint(4) NOT NULL default '0',
  PRIMARY KEY  (`fte_id`),
  KEY `key_id` (`fte_key_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4926 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%fulltext_index` */

CREATE TABLE `%TABLE_PREFIX%fulltext_index` (
  `fti_id` int(11) NOT NULL auto_increment,
  `fti_pid` varchar(64) NOT NULL,
  `fti_dsid` varchar(128) NOT NULL,
  `fti_indexed` datetime NOT NULL,
  PRIMARY KEY  (`fti_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%fulltext_keywords` */

CREATE TABLE `%TABLE_PREFIX%fulltext_keywords` (
  `ftk_id` int(11) NOT NULL auto_increment,
  `ftk_twoletters` char(2) NOT NULL,
  `ftk_word` varchar(64) NOT NULL,
  PRIMARY KEY  (`ftk_id`),
  UNIQUE KEY `ftk_word` (`ftk_word`),
  KEY `ftk_twoletters` (`ftk_twoletters`)
) ENGINE=MyISAM AUTO_INCREMENT=986 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%group` */

CREATE TABLE `%TABLE_PREFIX%group` (
  `grp_id` int(11) unsigned NOT NULL auto_increment,
  `grp_title` varchar(30) default NULL,
  `grp_status` set('active','archived') NOT NULL default 'active',
  `grp_created_date` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`grp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%group_user` */

CREATE TABLE `%TABLE_PREFIX%group_user` (
  `gpu_id` int(11) unsigned NOT NULL auto_increment,
  `gpu_grp_id` int(11) unsigned NOT NULL default '0',
  `gpu_usr_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`gpu_id`),
  KEY `pru_col_id` (`gpu_grp_id`,`gpu_usr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%mail_queue` */

CREATE TABLE `%TABLE_PREFIX%mail_queue` (
  `maq_id` int(11) unsigned NOT NULL auto_increment,
  `maq_queued_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `maq_status` varchar(8) NOT NULL default 'pending',
  `maq_save_copy` tinyint(1) NOT NULL default '1',
  `maq_sender_ip_address` varchar(15) NOT NULL default '',
  `maq_recipient` varchar(255) NOT NULL default '',
  `maq_headers` text NOT NULL,
  `maq_body` longtext NOT NULL,
  PRIMARY KEY  (`maq_id`),
  KEY `maq_status` (`maq_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%mail_queue_log` */

CREATE TABLE `%TABLE_PREFIX%mail_queue_log` (
  `mql_id` int(11) unsigned NOT NULL auto_increment,
  `mql_maq_id` int(11) unsigned NOT NULL default '0',
  `mql_created_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `mql_status` varchar(8) NOT NULL default 'error',
  `mql_server_message` text,
  PRIMARY KEY  (`mql_id`),
  KEY `mql_maq_id` (`mql_maq_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%news` */

CREATE TABLE `%TABLE_PREFIX%news` (
  `nws_id` int(11) unsigned NOT NULL auto_increment,
  `nws_usr_id` int(11) unsigned NOT NULL default '0',
  `nws_created_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `nws_title` varchar(255) NOT NULL default '',
  `nws_message` text NOT NULL,
  `nws_status` varchar(8) NOT NULL default 'active',
  `nws_published_date` datetime default '0000-00-00 00:00:00',
  `nws_updated_date` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`nws_id`),
  UNIQUE KEY `nws_title` (`nws_title`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%object_type` */

CREATE TABLE `%TABLE_PREFIX%object_type` (
  `ret_id` int(11) unsigned NOT NULL default '0',
  `ret_title` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`ret_id`),
  UNIQUE KEY `htt_name` (`ret_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%org_structure` */

CREATE TABLE `%TABLE_PREFIX%org_structure` (
  `org_id` int(11) unsigned NOT NULL auto_increment,
  `org_extdb_name` varchar(20) default NULL,
  `org_extdb_id` int(11) default NULL,
  `org_ext_table` varchar(100) default NULL,
  `org_title` varchar(255) default NULL,
  `org_is_current` int(1) default '1',
  PRIMARY KEY  (`org_id`)
) ENGINE=MyISAM AUTO_INCREMENT=786 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%org_structure_relationship` */

CREATE TABLE `%TABLE_PREFIX%org_structure_relationship` (
  `orr_id` int(11) unsigned NOT NULL auto_increment,
  `orr_parent_org_id` int(11) default NULL,
  `orr_child_org_id` int(11) default NULL,
  PRIMARY KEY  (`orr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%premis_event` */

CREATE TABLE `%TABLE_PREFIX%premis_event` (
  `pre_id` int(11) unsigned NOT NULL auto_increment,
  `pre_wfl_id` int(11) default NULL,
  `pre_date` datetime default NULL,
  `pre_detail` text,
  `pre_outcome` varchar(50) default NULL,
  `pre_outcomeDetail` text,
  `pre_usr_id` int(11) default NULL,
  `pre_pid` varchar(255) default NULL,
  `pre_is_hidden` tinyint(1) default '0',
  PRIMARY KEY  (`pre_id`)
) ENGINE=MyISAM AUTO_INCREMENT=377 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_locks` */

CREATE TABLE `%TABLE_PREFIX%record_locks` (
  `rl_id` int(11) NOT NULL auto_increment,
  `rl_pid` varchar(64) NOT NULL,
  `rl_usr_id` int(11) NOT NULL,
  `rl_context_type` int(11) NOT NULL,
  `rl_context_value` int(11) NOT NULL,
  PRIMARY KEY  (`rl_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_matching_field` */

CREATE TABLE `%TABLE_PREFIX%record_matching_field` (
  `rmf_id` int(11) unsigned NOT NULL auto_increment,
  `rmf_rec_pid_num` int(11) NOT NULL,
  `rmf_rec_pid` varchar(64) character set utf8 NOT NULL default '',
  `rmf_dsid` varchar(255) character set utf8 default NULL,
  `rmf_xsdmf_id` int(11) unsigned default NULL,
  `rmf_varchar` varchar(255) character set utf8 default NULL,
  `rmf_date` datetime default NULL,
  `rmf_int` int(11) unsigned default NULL,
  PRIMARY KEY  (`rmf_id`),
  KEY `rmf_xsdmf_id` (`rmf_xsdmf_id`),
  KEY `rmf_date` (`rmf_date`),
  KEY `rmf_rec_pid_num` (`rmf_rec_pid_num`),
  KEY `rmf_int` (`rmf_int`),
  KEY `rmf_rec_pid` (`rmf_rec_pid`),
  KEY `combo_pid_xsdmf` (`rmf_rec_pid`,`rmf_xsdmf_id`),
  KEY `combo_pid_num_xsdmf` (`rmf_rec_pid_num`,`rmf_xsdmf_id`),
  FULLTEXT KEY `rmf_varchar` (`rmf_varchar`)
) ENGINE=MyISAM AUTO_INCREMENT=4255 DEFAULT CHARSET=latin1;

/*Table structure for table `%TABLE_PREFIX%record_search_key` */

CREATE TABLE `%TABLE_PREFIX%record_search_key` (
  `rek_pid` varchar(64) NOT NULL COMMENT 'PID',
  `rek_title_xsdmf_id` int(11) default NULL,
  `rek_title` varchar(255) default NULL COMMENT 'Title',
  `rek_description_xsdmf_id` int(11) default NULL,
  `rek_description` text COMMENT 'Description',
  `rek_display_type_xsdmf_id` int(11) default NULL,
  `rek_display_type` int(11) default NULL COMMENT 'Display Type',
  `rek_status_xsdmf_id` int(11) default NULL,
  `rek_status` int(11) default NULL COMMENT 'Status',
  `rek_date_xsdmf_id` int(11) default NULL,
  `rek_date` datetime default NULL COMMENT 'Date',
  `rek_object_type_xsdmf_id` int(11) default NULL,
  `rek_object_type` int(11) default NULL COMMENT 'Object Type',
  `rek_depositor_xsdmf_id` int(11) default NULL,
  `rek_depositor` int(11) default NULL COMMENT 'Depositor',
  `rek_created_date_xsdmf_id` int(11) default NULL,
  `rek_created_date` datetime default NULL COMMENT 'Created Date',
  `rek_updated_date_xsdmf_id` int(11) default NULL,
  `rek_updated_date` datetime default NULL COMMENT 'Updated Date',
  `rek_file_downloads` int(11) default '0' COMMENT 'Sum of all binary M datastream downloads',
  `rek_views` int(11) default '0' COMMENT 'Sum of all metadata views',
  `rek_citation` text character set utf8 collate utf8_bin,
  `rek_sequence` int(11) default '0' COMMENT 'Sequence order in a parent object',
  `rek_sequence_xsdmf_id` int(11) default NULL,
  PRIMARY KEY  (`rek_pid`),
  KEY `rek_display_type` (`rek_display_type`),
  KEY `rek_status` (`rek_status`),
  KEY `rek_date` (`rek_date`),
  KEY `rek_object_type` (`rek_object_type`),
  KEY `rek_depositor` (`rek_depositor`),
  KEY `rek_created_date` (`rek_created_date`),
  KEY `rek_updated_date` (`rek_updated_date`),
  KEY `rek_title` (`rek_title`),
  KEY `rek_views` (`rek_views`),
  KEY `rek_file_downloads` (`rek_file_downloads`),
  KEY `rek_sequence` (`rek_sequence`),
  FULLTEXT KEY `rek_description` (`rek_description`),
  FULLTEXT KEY `rek_fulltext` (`rek_title`,`rek_description`),
  FULLTEXT KEY `rek_fulltext_all` (`rek_pid`,`rek_title`,`rek_description`),
  FULLTEXT KEY `rek_title_ft` (`rek_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_alternative_title` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_alternative_title` (
  `rek_alternative_title_id` int(11) NOT NULL auto_increment,
  `rek_alternative_title_pid` varchar(64) default NULL,
  `rek_alternative_title_xsdmf_id` int(11) default NULL,
  `rek_alternative_title` varchar(255) default NULL,
  PRIMARY KEY  (`rek_alternative_title_id`),
  KEY `rek_alternative_title` (`rek_alternative_title`),
  KEY `rek_alternative_title_pid` (`rek_alternative_title_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_anglicised_publisher` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_anglicised_publisher` (
  `rek_anglicised_publisher_id` int(11) NOT NULL auto_increment,
  `rek_anglicised_publisher_pid` varchar(64) default NULL,
  `rek_anglicised_publisher_xsdmf_id` int(11) default NULL,
  `rek_anglicised_publisher` varchar(255) default NULL,
  PRIMARY KEY  (`rek_anglicised_publisher_id`),
  KEY `rek_anglicised_publisher` (`rek_anglicised_publisher`),
  KEY `rek_anglicised_publisher_pid` (`rek_anglicised_publisher_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_anglicised_title` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_anglicised_title` (
  `rek_anglicised_title_id` int(11) NOT NULL auto_increment,
  `rek_anglicised_title_pid` varchar(64) default NULL,
  `rek_anglicised_title_xsdmf_id` int(11) default NULL,
  `rek_anglicised_title` varchar(255) default NULL,
  PRIMARY KEY  (`rek_anglicised_title_id`),
  KEY `rek_anglicised_title` (`rek_anglicised_title`),
  KEY `rek_anglicised_title_pid` (`rek_anglicised_title_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_assigned_group_id` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_assigned_group_id` (
  `rek_assigned_group_id_id` int(11) NOT NULL auto_increment,
  `rek_assigned_group_id_pid` varchar(64) default NULL,
  `rek_assigned_group_id_xsdmf_id` int(11) default NULL,
  `rek_assigned_group_id` int(11) default NULL,
  PRIMARY KEY  (`rek_assigned_group_id_id`),
  KEY `rek_assigned_group_id_pid` (`rek_assigned_group_id_pid`),
  KEY `rek_assigned_group_id` (`rek_assigned_group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_assigned_user_id` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_assigned_user_id` (
  `rek_assigned_user_id_id` int(11) NOT NULL auto_increment,
  `rek_assigned_user_id_pid` varchar(64) default NULL,
  `rek_assigned_user_id_xsdmf_id` int(11) default NULL,
  `rek_assigned_user_id` int(11) default NULL,
  PRIMARY KEY  (`rek_assigned_user_id_id`),
  KEY `rek_assigned_user_id_pid` (`rek_assigned_user_id_pid`),
  KEY `rek_assigned_user_id` (`rek_assigned_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_author` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_author` (
  `rek_author_id` int(11) NOT NULL auto_increment,
  `rek_author_pid` varchar(64) default NULL,
  `rek_author_xsdmf_id` int(11) default NULL,
  `rek_author` varchar(255) default NULL,
  PRIMARY KEY  (`rek_author_id`),
  KEY `rek_author_pid` (`rek_author_pid`),
  KEY `rek_author` (`rek_author`)
) ENGINE=MyISAM AUTO_INCREMENT=131684 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_author_id` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_author_id` (
  `rek_author_id_id` int(11) NOT NULL auto_increment,
  `rek_author_id_pid` varchar(64) default NULL,
  `rek_author_id_xsdmf_id` int(11) default NULL,
  `rek_author_id` int(11) default NULL,
  PRIMARY KEY  (`rek_author_id_id`),
  KEY `rek_author_id_pid` (`rek_author_id_pid`),
  KEY `rek_author_id` (`rek_author_id`)
) ENGINE=MyISAM AUTO_INCREMENT=72370 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_book_title` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_book_title` (
  `rek_book_title_id` int(11) NOT NULL auto_increment,
  `rek_book_title_pid` varchar(64) default NULL,
  `rek_book_title_xsdmf_id` int(11) default NULL,
  `rek_book_title` varchar(255) default NULL,
  PRIMARY KEY  (`rek_book_title_id`),
  KEY `rek_book_title` (`rek_book_title`),
  KEY `rek_book_title_pid` (`rek_book_title_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=997 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_chapter_number` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_chapter_number` (
  `rek_chapter_number_id` int(11) NOT NULL auto_increment,
  `rek_chapter_number_pid` varchar(64) default NULL,
  `rek_chapter_number_xsdmf_id` int(11) default NULL,
  `rek_chapter_number` varchar(255) default NULL,
  PRIMARY KEY  (`rek_chapter_number_id`),
  KEY `rek_chapter_number` (`rek_chapter_number`),
  KEY `rek_chapter_number_pid` (`rek_chapter_number_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=194 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_conference_dates` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_conference_dates` (
  `rek_conference_dates_id` int(11) NOT NULL auto_increment,
  `rek_conference_dates_pid` varchar(64) default NULL,
  `rek_conference_dates_xsdmf_id` int(11) default NULL,
  `rek_conference_dates` varchar(255) default NULL,
  PRIMARY KEY  (`rek_conference_dates_id`),
  KEY `rek_conference_dates` (`rek_conference_dates`),
  KEY `rek_conference_dates_pid` (`rek_conference_dates_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_conference_location` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_conference_location` (
  `rek_conference_location_id` int(11) NOT NULL auto_increment,
  `rek_conference_location_pid` varchar(64) default NULL,
  `rek_conference_location_xsdmf_id` int(11) default NULL,
  `rek_conference_location` varchar(255) default NULL,
  PRIMARY KEY  (`rek_conference_location_id`),
  KEY `rek_conference_location` (`rek_conference_location`),
  KEY `rek_conference_location_pid` (`rek_conference_location_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=2929 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_conference_name` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_conference_name` (
  `rek_conference_name_id` int(11) NOT NULL auto_increment,
  `rek_conference_name_pid` varchar(64) default NULL,
  `rek_conference_name_xsdmf_id` int(11) default NULL,
  `rek_conference_name` varchar(255) default NULL,
  PRIMARY KEY  (`rek_conference_name_id`),
  KEY `rek_conference_name_pid` (`rek_conference_name_pid`),
  KEY `rek_conference_name` (`rek_conference_name`)
) ENGINE=MyISAM AUTO_INCREMENT=6284 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_contributor` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_contributor` (
  `rek_contributor_id` int(11) NOT NULL auto_increment,
  `rek_contributor_pid` varchar(64) default NULL,
  `rek_contributor_xsdmf_id` int(11) default NULL,
  `rek_contributor` varchar(255) default NULL,
  PRIMARY KEY  (`rek_contributor_id`),
  KEY `rek_contributor_pid` (`rek_contributor_pid`),
  KEY `rek_contributor` (`rek_contributor`)
) ENGINE=MyISAM AUTO_INCREMENT=18152 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_contributor_id` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_contributor_id` (
  `rek_contributor_id_id` int(11) NOT NULL auto_increment,
  `rek_contributor_id_pid` varchar(64) default NULL,
  `rek_contributor_id_xsdmf_id` int(11) default NULL,
  `rek_contributor_id` int(11) default NULL,
  PRIMARY KEY  (`rek_contributor_id_id`),
  KEY `rek_contributor_id_pid` (`rek_contributor_id_pid`),
  KEY `rek_contributor_id` (`rek_contributor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1155 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_country_of_issue` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_country_of_issue` (
  `rek_country_of_issue_id` int(11) NOT NULL auto_increment,
  `rek_country_of_issue_pid` varchar(64) default NULL,
  `rek_country_of_issue_xsdmf_id` int(11) default NULL,
  `rek_country_of_issue` varchar(255) default NULL,
  PRIMARY KEY  (`rek_country_of_issue_id`),
  KEY `rek_country_of_issue` (`rek_country_of_issue`),
  KEY `rek_country_of_issue_pid` (`rek_country_of_issue_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_date_available` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_date_available` (
  `rek_date_available_id` int(11) NOT NULL auto_increment,
  `rek_date_available_pid` varchar(64) default NULL,
  `rek_date_available_xsdmf_id` int(11) default NULL,
  `rek_date_available` datetime default NULL COMMENT 'Date Available',
  PRIMARY KEY  (`rek_date_available_id`),
  KEY `rek_date_available` (`rek_date_available`),
  KEY `rek_date_available_pid` (`rek_date_available_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=98 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_edition` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_edition` (
  `rek_edition_id` int(11) NOT NULL auto_increment,
  `rek_edition_pid` varchar(64) default NULL,
  `rek_edition_xsdmf_id` int(11) default NULL,
  `rek_edition` varchar(255) default NULL,
  PRIMARY KEY  (`rek_edition_id`),
  KEY `rek_edition` (`rek_edition`),
  KEY `rek_edition_pid` (`rek_edition_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_end_page` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_end_page` (
  `rek_end_page_id` int(11) NOT NULL auto_increment,
  `rek_end_page_pid` varchar(64) default NULL,
  `rek_end_page_xsdmf_id` int(11) default NULL,
  `rek_end_page` varchar(255) default NULL,
  PRIMARY KEY  (`rek_end_page_id`),
  KEY `rek_end_page` (`rek_end_page`),
  KEY `rek_end_page_pid` (`rek_end_page_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=7061 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_english_publisher` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_english_publisher` (
  `rek_english_publisher_id` int(11) NOT NULL auto_increment,
  `rek_english_publisher_pid` varchar(64) default NULL,
  `rek_english_publisher_xsdmf_id` int(11) default NULL,
  `rek_english_publisher` varchar(255) default NULL,
  PRIMARY KEY  (`rek_english_publisher_id`),
  KEY `rek_english_publisher` (`rek_english_publisher`),
  KEY `rek_english_publisher_pid` (`rek_english_publisher_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_english_title` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_english_title` (
  `rek_english_title_id` int(11) NOT NULL auto_increment,
  `rek_english_title_pid` varchar(64) default NULL,
  `rek_english_title_xsdmf_id` int(11) default NULL,
  `rek_english_title` varchar(255) default NULL,
  PRIMARY KEY  (`rek_english_title_id`),
  KEY `rek_english_title` (`rek_english_title`),
  KEY `rek_english_title_pid` (`rek_english_title_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_file_attachment_content` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_file_attachment_content` (
  `rek_file_attachment_content_id` int(11) NOT NULL auto_increment,
  `rek_file_attachment_content_pid` varchar(64) default NULL,
  `rek_file_attachment_content_xsdmf_id` int(11) default NULL,
  `rek_file_attachment_content` text,
  PRIMARY KEY  (`rek_file_attachment_content_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_file_attachment_name` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_file_attachment_name` (
  `rek_file_attachment_name_id` int(11) NOT NULL auto_increment,
  `rek_file_attachment_name_pid` varchar(64) default NULL,
  `rek_file_attachment_name_xsdmf_id` int(11) default NULL,
  `rek_file_attachment_name` varchar(255) default NULL,
  PRIMARY KEY  (`rek_file_attachment_name_id`),
  UNIQUE KEY `rek_file_attachment_name_pid_unique` (`rek_file_attachment_name_pid`,`rek_file_attachment_name`),
  KEY `rek_file_attachment_name_id` (`rek_file_attachment_name_pid`),
  KEY `rek_file_attachment_name` (`rek_file_attachment_name`)
) ENGINE=MyISAM AUTO_INCREMENT=58506 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_file_downloads` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_file_downloads` (
  `rek_file_downloads_id` int(11) NOT NULL auto_increment,
  `rek_file_downloads_pid` varchar(64) default NULL,
  `rek_file_downloads_xsdmf_id` int(11) default NULL,
  `rek_file_downloads` int(11) default NULL,
  PRIMARY KEY  (`rek_file_downloads_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_identifier` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_identifier` (
  `rek_identifier_id` int(11) NOT NULL auto_increment,
  `rek_identifier_pid` varchar(64) default NULL,
  `rek_identifier_xsdmf_id` int(11) default NULL,
  `rek_identifier` varchar(255) default NULL,
  PRIMARY KEY  (`rek_identifier_id`),
  KEY `rek_identifier_pid` (`rek_identifier_pid`),
  KEY `rek_identifier` (`rek_identifier`)
) ENGINE=MyISAM AUTO_INCREMENT=2172 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_isannotationof` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_isannotationof` (
  `rek_isannotationof_id` int(11) NOT NULL auto_increment,
  `rek_isannotationof_pid` varchar(64) default NULL,
  `rek_isannotationof_xsdmf_id` int(11) default NULL,
  `rek_isannotationof` varchar(64) default NULL,
  PRIMARY KEY  (`rek_isannotationof_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_isbn` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_isbn` (
  `rek_isbn_id` int(11) NOT NULL auto_increment,
  `rek_isbn_pid` varchar(64) default NULL,
  `rek_isbn_xsdmf_id` int(11) default NULL,
  `rek_isbn` varchar(255) default NULL,
  PRIMARY KEY  (`rek_isbn_id`),
  KEY `rek_isbn_pid` (`rek_isbn_pid`,`rek_isbn`)
) ENGINE=MyISAM AUTO_INCREMENT=123 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_isdatacomponentof` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_isdatacomponentof` (
  `rek_isdatacomponentof_id` int(11) NOT NULL auto_increment,
  `rek_isdatacomponentof_pid` varchar(64) default NULL,
  `rek_isdatacomponentof_xsdmf_id` int(11) default NULL,
  `rek_isdatacomponentof` varchar(64) default NULL,
  PRIMARY KEY  (`rek_isdatacomponentof_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_isderivationof` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_isderivationof` (
  `rek_isderivationof_id` int(11) NOT NULL auto_increment,
  `rek_isderivationof_pid` varchar(64) default NULL,
  `rek_isderivationof_xsdmf_id` int(11) default NULL,
  `rek_isderivationof` varchar(64) default NULL,
  PRIMARY KEY  (`rek_isderivationof_id`),
  KEY `rek_isderivationof` (`rek_isderivationof`),
  KEY `rek_isderivationof_pid` (`rek_isderivationof_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=218 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_isi_loc` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_isi_loc` (
  `rek_isi_loc_id` int(11) NOT NULL auto_increment,
  `rek_isi_loc_pid` varchar(64) default NULL,
  `rek_isi_loc_xsdmf_id` int(11) default NULL,
  `rek_isi_loc` varchar(255) default NULL,
  PRIMARY KEY  (`rek_isi_loc_id`),
  KEY `rek_isi_loc_pid` (`rek_isi_loc_pid`,`rek_isi_loc`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_ismemberof` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_ismemberof` (
  `rek_ismemberof_id` int(11) NOT NULL auto_increment,
  `rek_ismemberof_pid` varchar(64) default NULL,
  `rek_ismemberof_xsdmf_id` int(11) default NULL,
  `rek_ismemberof` varchar(64) default NULL,
  PRIMARY KEY  (`rek_ismemberof_id`),
  KEY `rek_ismemberof_pid_value` (`rek_ismemberof_pid`,`rek_ismemberof`),
  KEY `rek_ismemberof_pid` (`rek_ismemberof`)
) ENGINE=MyISAM AUTO_INCREMENT=44452 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_issn` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_issn` (
  `rek_issn_id` int(11) NOT NULL auto_increment,
  `rek_issn_pid` varchar(64) default NULL,
  `rek_issn_xsdmf_id` int(11) default NULL,
  `rek_issn` varchar(255) default NULL,
  PRIMARY KEY  (`rek_issn_id`),
  KEY `rek_issn_pid` (`rek_issn_pid`,`rek_issn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_issue_number` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_issue_number` (
  `rek_issue_number_id` int(11) NOT NULL auto_increment,
  `rek_issue_number_pid` varchar(64) default NULL,
  `rek_issue_number_xsdmf_id` int(11) default NULL,
  `rek_issue_number` varchar(255) default NULL,
  PRIMARY KEY  (`rek_issue_number_id`),
  KEY `rek_issue_number` (`rek_issue_number`),
  KEY `rek_issue_number_pid` (`rek_issue_number_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=4322 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_journal_name` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_journal_name` (
  `rek_journal_name_id` int(11) NOT NULL auto_increment,
  `rek_journal_name_pid` varchar(64) default NULL,
  `rek_journal_name_xsdmf_id` int(11) default NULL,
  `rek_journal_name` varchar(255) default NULL,
  PRIMARY KEY  (`rek_journal_name_id`),
  KEY `rek_journal_name_pid` (`rek_journal_name_pid`),
  KEY `rek_journal_name` (`rek_journal_name`)
) ENGINE=MyISAM AUTO_INCREMENT=28654 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_keywords` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_keywords` (
  `rek_keywords_id` int(11) NOT NULL auto_increment,
  `rek_keywords_pid` varchar(64) default NULL,
  `rek_keywords_xsdmf_id` int(11) default NULL,
  `rek_keywords` varchar(255) default NULL,
  PRIMARY KEY  (`rek_keywords_id`),
  KEY `rek_keywords_pid` (`rek_keywords_pid`),
  KEY `rek_keywords` (`rek_keywords`),
  FULLTEXT KEY `rek_keywords_fulltext` (`rek_keywords`)
) ENGINE=MyISAM AUTO_INCREMENT=200668 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_language` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_language` (
  `rek_language_id` int(11) NOT NULL auto_increment,
  `rek_language_pid` varchar(64) default NULL,
  `rek_language_xsdmf_id` int(11) default NULL,
  `rek_language` varchar(255) default NULL,
  PRIMARY KEY  (`rek_language_id`),
  KEY `rek_language` (`rek_language`),
  KEY `rek_language_pid` (`rek_language_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_language_of_title` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_language_of_title` (
  `rek_language_of_title_id` int(11) NOT NULL auto_increment,
  `rek_language_of_title_pid` varchar(64) default NULL,
  `rek_language_of_title_xsdmf_id` int(11) default NULL,
  `rek_language_of_title` varchar(255) default NULL,
  PRIMARY KEY  (`rek_language_of_title_id`),
  KEY `rek_language_of_title` (`rek_language_of_title`),
  KEY `rek_language_of_title_pid` (`rek_language_of_title_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_na_explanation` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_na_explanation` (
  `rek_na_explanation_id` int(11) NOT NULL auto_increment,
  `rek_na_explanation_pid` varchar(64) default NULL,
  `rek_na_explanation_xsdmf_id` int(11) default NULL,
  `rek_na_explanation` text,
  PRIMARY KEY  (`rek_na_explanation_id`),
  KEY `rek_na_explanation_pid` (`rek_na_explanation_pid`),
  FULLTEXT KEY `rek_na_explanation` (`rek_na_explanation`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_newspaper` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_newspaper` (
  `rek_newspaper_id` int(11) NOT NULL auto_increment,
  `rek_newspaper_pid` varchar(64) default NULL,
  `rek_newspaper_xsdmf_id` int(11) default NULL,
  `rek_newspaper` varchar(255) default NULL,
  PRIMARY KEY  (`rek_newspaper_id`),
  KEY `rek_newspaper_pid` (`rek_newspaper_pid`),
  KEY `rek_newspaper` (`rek_newspaper`)
) ENGINE=MyISAM AUTO_INCREMENT=190 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_notes` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_notes` (
  `rek_notes_id` int(11) NOT NULL auto_increment,
  `rek_notes_pid` varchar(64) default NULL,
  `rek_notes_xsdmf_id` int(11) default NULL,
  `rek_notes` text,
  PRIMARY KEY  (`rek_notes_id`),
  KEY `rek_notes_pid` (`rek_notes_pid`),
  FULLTEXT KEY `rek_notes` (`rek_notes`)
) ENGINE=MyISAM AUTO_INCREMENT=13430 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_org_name` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_org_name` (
  `rek_org_name_id` int(11) NOT NULL auto_increment,
  `rek_org_name_pid` varchar(64) default NULL,
  `rek_org_name_xsdmf_id` int(11) default NULL,
  `rek_org_name` varchar(255) default NULL,
  PRIMARY KEY  (`rek_org_name_id`),
  KEY `rek_org_name_pid` (`rek_org_name_pid`,`rek_org_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_org_unit_name` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_org_unit_name` (
  `rek_org_unit_name_id` int(11) NOT NULL auto_increment,
  `rek_org_unit_name_pid` varchar(64) default NULL,
  `rek_org_unit_name_xsdmf_id` int(11) default NULL,
  `rek_org_unit_name` varchar(255) default NULL,
  PRIMARY KEY  (`rek_org_unit_name_id`),
  KEY `rek_org_unit_name_pid` (`rek_org_unit_name_pid`,`rek_org_unit_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_output_availability` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_output_availability` (
  `rek_output_availability_id` int(11) NOT NULL auto_increment,
  `rek_output_availability_pid` varchar(64) default NULL,
  `rek_output_availability_xsdmf_id` int(11) default NULL,
  `rek_output_availability` varchar(1) default NULL,
  PRIMARY KEY  (`rek_output_availability_id`),
  KEY `rek_output_availability_pid` (`rek_output_availability_pid`,`rek_output_availability`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_patent_number` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_patent_number` (
  `rek_patent_number_id` int(11) NOT NULL auto_increment,
  `rek_patent_number_pid` varchar(64) default NULL,
  `rek_patent_number_xsdmf_id` int(11) default NULL,
  `rek_patent_number` varchar(255) default NULL,
  PRIMARY KEY  (`rek_patent_number_id`),
  KEY `rek_patent_number` (`rek_patent_number`),
  KEY `rek_patent_number_pid` (`rek_patent_number_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=112 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_phonetic_book_title` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_phonetic_book_title` (
  `rek_phonetic_book_title_id` int(11) NOT NULL auto_increment,
  `rek_phonetic_book_title_pid` varchar(64) default NULL,
  `rek_phonetic_book_title_xsdmf_id` int(11) default NULL,
  `rek_phonetic_book_title` varchar(255) default NULL,
  PRIMARY KEY  (`rek_phonetic_book_title_id`),
  KEY `rek_phonetic_book_title_pid` (`rek_phonetic_book_title_pid`,`rek_phonetic_book_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_phonetic_conference_name` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_phonetic_conference_name` (
  `rek_phonetic_conference_name_id` int(11) NOT NULL auto_increment,
  `rek_phonetic_conference_name_pid` varchar(64) default NULL,
  `rek_phonetic_conference_name_xsdmf_id` int(11) default NULL,
  `rek_phonetic_conference_name` varchar(255) default NULL,
  PRIMARY KEY  (`rek_phonetic_conference_name_id`),
  KEY `rek_phonetic_conference_name_pid` (`rek_phonetic_conference_name_pid`,`rek_phonetic_conference_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_phonetic_journal_name` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_phonetic_journal_name` (
  `rek_phonetic_journal_name_id` int(11) NOT NULL auto_increment,
  `rek_phonetic_journal_name_pid` varchar(64) default NULL,
  `rek_phonetic_journal_name_xsdmf_id` int(11) default NULL,
  `rek_phonetic_journal_name` varchar(255) default NULL,
  PRIMARY KEY  (`rek_phonetic_journal_name_id`),
  KEY `rek_phonetic_journal_name_pid` (`rek_phonetic_journal_name_pid`,`rek_phonetic_journal_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_phonetic_newspaper` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_phonetic_newspaper` (
  `rek_phonetic_newspaper_id` int(11) NOT NULL auto_increment,
  `rek_phonetic_newspaper_pid` varchar(64) default NULL,
  `rek_phonetic_newspaper_xsdmf_id` int(11) default NULL,
  `rek_phonetic_newspaper` varchar(255) default NULL,
  PRIMARY KEY  (`rek_phonetic_newspaper_id`),
  KEY `rek_phonetic_newspaper_pid` (`rek_phonetic_newspaper_pid`,`rek_phonetic_newspaper`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_phonetic_title` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_phonetic_title` (
  `rek_phonetic_title_id` int(11) NOT NULL auto_increment,
  `rek_phonetic_title_pid` varchar(64) default NULL,
  `rek_phonetic_title_xsdmf_id` int(11) default NULL,
  `rek_phonetic_title` varchar(255) default NULL,
  PRIMARY KEY  (`rek_phonetic_title_id`),
  KEY `rek_phonetic_title_pid` (`rek_phonetic_title_pid`,`rek_phonetic_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_place_of_publication` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_place_of_publication` (
  `rek_place_of_publication_id` int(11) NOT NULL auto_increment,
  `rek_place_of_publication_pid` varchar(64) default NULL,
  `rek_place_of_publication_xsdmf_id` int(11) default NULL,
  `rek_place_of_publication` varchar(255) default NULL,
  PRIMARY KEY  (`rek_place_of_publication_id`),
  KEY `rek_place_of_publication` (`rek_place_of_publication`),
  KEY `rek_place_of_publication_pid` (`rek_place_of_publication_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=159 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_prn` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_prn` (
  `rek_prn_id` int(11) NOT NULL auto_increment,
  `rek_prn_pid` varchar(64) default NULL,
  `rek_prn_xsdmf_id` int(11) default NULL,
  `rek_prn` varchar(255) default NULL,
  PRIMARY KEY  (`rek_prn_id`),
  KEY `rek_prn_pid` (`rek_prn_pid`,`rek_prn`)
) ENGINE=MyISAM AUTO_INCREMENT=136 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_publisher` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_publisher` (
  `rek_publisher_id` int(11) NOT NULL auto_increment,
  `rek_publisher_pid` varchar(64) default NULL,
  `rek_publisher_xsdmf_id` int(11) default NULL,
  `rek_publisher` varchar(255) default NULL,
  PRIMARY KEY  (`rek_publisher_id`),
  KEY `rek_publisher_pid` (`rek_publisher_pid`),
  KEY `rek_publisher` (`rek_publisher`)
) ENGINE=MyISAM AUTO_INCREMENT=2192 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_refereed` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_refereed` (
  `rek_refereed_id` int(11) NOT NULL auto_increment,
  `rek_refereed_pid` varchar(64) default NULL,
  `rek_refereed_xsdmf_id` int(11) default NULL,
  `rek_refereed` int(11) default NULL,
  PRIMARY KEY  (`rek_refereed_id`),
  KEY `rek_refereed_pid` (`rek_refereed_pid`),
  KEY `rek_refereed` (`rek_refereed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_report_number` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_report_number` (
  `rek_report_number_id` int(11) NOT NULL auto_increment,
  `rek_report_number_pid` varchar(64) default NULL,
  `rek_report_number_xsdmf_id` int(11) default NULL,
  `rek_report_number` varchar(255) default NULL,
  PRIMARY KEY  (`rek_report_number_id`),
  KEY `rek_report_number_pid` (`rek_report_number_pid`,`rek_report_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_research_program` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_research_program` (
  `rek_research_program_id` int(11) NOT NULL auto_increment,
  `rek_research_program_pid` varchar(64) default NULL,
  `rek_research_program_xsdmf_id` int(11) default NULL,
  `rek_research_program` varchar(255) default NULL,
  PRIMARY KEY  (`rek_research_program_id`),
  KEY `rek_research_program_pid` (`rek_research_program_pid`),
  KEY `rek_research_program` (`rek_research_program`)
) ENGINE=MyISAM AUTO_INCREMENT=90 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_sensitivity_explanation` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_sensitivity_explanation` (
  `rek_sensitivity_explanation_id` int(11) NOT NULL auto_increment,
  `rek_sensitivity_explanation_pid` varchar(64) default NULL,
  `rek_sensitivity_explanation_xsdmf_id` int(11) default NULL,
  `rek_sensitivity_explanation` text,
  PRIMARY KEY  (`rek_sensitivity_explanation_id`),
  KEY `rek_sensitivity_explanation_pid` (`rek_sensitivity_explanation_pid`),
  FULLTEXT KEY `rek_sensitivity_explanation` (`rek_sensitivity_explanation`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_series` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_series` (
  `rek_series_id` int(11) NOT NULL auto_increment,
  `rek_series_pid` varchar(64) default NULL,
  `rek_series_xsdmf_id` int(11) default NULL,
  `rek_series` varchar(255) default NULL,
  PRIMARY KEY  (`rek_series_id`),
  KEY `rek_series_pid` (`rek_series_pid`),
  KEY `rek_series` (`rek_series`)
) ENGINE=MyISAM AUTO_INCREMENT=112 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_start_page` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_start_page` (
  `rek_start_page_id` int(11) NOT NULL auto_increment,
  `rek_start_page_pid` varchar(64) default NULL,
  `rek_start_page_xsdmf_id` int(11) default NULL,
  `rek_start_page` varchar(255) default NULL,
  PRIMARY KEY  (`rek_start_page_id`),
  KEY `rek_start_page` (`rek_start_page`),
  KEY `rek_start_page_pid` (`rek_start_page_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=7065 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_subject` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_subject` (
  `rek_subject_id` int(11) NOT NULL auto_increment,
  `rek_subject_pid` varchar(64) default NULL,
  `rek_subject_xsdmf_id` int(11) default NULL,
  `rek_subject` int(11) default NULL,
  PRIMARY KEY  (`rek_subject_id`),
  KEY `rek_subject_pid` (`rek_subject_pid`,`rek_subject`)
) ENGINE=MyISAM AUTO_INCREMENT=70849 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_translated_book_title` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_translated_book_title` (
  `rek_translated_book_title_id` int(11) NOT NULL auto_increment,
  `rek_translated_book_title_pid` varchar(64) default NULL,
  `rek_translated_book_title_xsdmf_id` int(11) default NULL,
  `rek_translated_book_title` varchar(255) default NULL,
  PRIMARY KEY  (`rek_translated_book_title_id`),
  KEY `rek_translated_book_title_pid` (`rek_translated_book_title_pid`,`rek_translated_book_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_translated_conference_name` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_translated_conference_name` (
  `rek_translated_conference_name_id` int(11) NOT NULL auto_increment,
  `rek_translated_conference_name_pid` varchar(64) default NULL,
  `rek_translated_conference_name_xsdmf_id` int(11) default NULL,
  `rek_translated_conference_name` varchar(255) default NULL,
  PRIMARY KEY  (`rek_translated_conference_name_id`),
  KEY `rek_translated_conference_name_pid` (`rek_translated_conference_name_pid`,`rek_translated_conference_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_translated_journal_name` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_translated_journal_name` (
  `rek_translated_journal_name_id` int(11) NOT NULL auto_increment,
  `rek_translated_journal_name_pid` varchar(64) default NULL,
  `rek_translated_journal_name_xsdmf_id` int(11) default NULL,
  `rek_translated_journal_name` varchar(255) default NULL,
  PRIMARY KEY  (`rek_translated_journal_name_id`),
  KEY `rek_translated_journal_name_pid` (`rek_translated_journal_name_pid`,`rek_translated_journal_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_translated_newspaper` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_translated_newspaper` (
  `rek_translated_newspaper_id` int(11) NOT NULL auto_increment,
  `rek_translated_newspaper_pid` varchar(64) default NULL,
  `rek_translated_newspaper_xsdmf_id` int(11) default NULL,
  `rek_translated_newspaper` varchar(255) default NULL,
  PRIMARY KEY  (`rek_translated_newspaper_id`),
  KEY `rek_translated_newspaper_pid` (`rek_translated_newspaper_pid`,`rek_translated_newspaper`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_translated_title` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_translated_title` (
  `rek_translated_title_id` int(11) NOT NULL auto_increment,
  `rek_translated_title_pid` varchar(64) default NULL,
  `rek_translated_title_xsdmf_id` int(11) default NULL,
  `rek_translated_title` varchar(255) default NULL,
  PRIMARY KEY  (`rek_translated_title_id`),
  KEY `rek_translated_title_pid` (`rek_translated_title_pid`,`rek_translated_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_volume_number` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_volume_number` (
  `rek_volume_number_id` int(11) NOT NULL auto_increment,
  `rek_volume_number_pid` varchar(64) default NULL,
  `rek_volume_number_xsdmf_id` int(11) default NULL,
  `rek_volume_number` varchar(255) default NULL,
  PRIMARY KEY  (`rek_volume_number_id`),
  KEY `rek_volume_number` (`rek_volume_number`),
  KEY `rek_volume_number_pid` (`rek_volume_number_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=5653 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%record_search_key_xsd_display_option` */

CREATE TABLE `%TABLE_PREFIX%record_search_key_xsd_display_option` (
  `rek_xsd_display_option_id` int(11) NOT NULL auto_increment,
  `rek_xsd_display_option_pid` varchar(64) default NULL,
  `rek_xsd_display_option_xsdmf_id` int(11) default NULL,
  `rek_xsd_display_option` int(11) default NULL,
  PRIMARY KEY  (`rek_xsd_display_option_id`),
  KEY `rek_xsd_display_option_pid` (`rek_xsd_display_option_pid`),
  KEY `rek_xsd_display_option` (`rek_xsd_display_option`)
) ENGINE=MyISAM AUTO_INCREMENT=1217 DEFAULT CHARSET=utf8;

CREATE TABLE `%TABLE_PREFIX%search_key` (
  `sek_id` int(11) unsigned NOT NULL auto_increment,
  `sek_title` varchar(64) default NULL,
  `sek_alt_title` varchar(64) default NULL,
  `sek_adv_visible` tinyint(1) default '0',
  `sek_simple_used` tinyint(1) default '0',
  `sek_myfez_visible` tinyint(1) default NULL,
  `sek_order` int(11) default '999',
  `sek_html_input` varchar(64) default NULL,
  `sek_fez_variable` varchar(64) default NULL,
  `sek_smarty_variable` varchar(64) default NULL,
  `sek_cvo_id` int(11) unsigned default NULL,
  `sek_lookup_function` varchar(255) default NULL,
  `sek_data_type` varchar(10) default NULL,
  `sek_relationship` tinyint(1) default '0' COMMENT '0 is 1-1, 1 is 1-M',
  `sek_meta_header` varchar(64) default NULL,
  PRIMARY KEY  (`sek_id`)
) ENGINE=MyISAM AUTO_INCREMENT=74 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%statistics_all` */

CREATE TABLE `%TABLE_PREFIX%statistics_all` (
  `stl_id` int(11) NOT NULL auto_increment,
  `stl_archive_name` varchar(255) default NULL,
  `stl_ip` varchar(15) default NULL,
  `stl_hostname` varchar(255) default NULL,
  `stl_request_date` timestamp NULL default NULL,
  `stl_country_code` varchar(4) default NULL,
  `stl_country_name` varchar(100) default NULL,
  `stl_region` varchar(100) default NULL,
  `stl_city` varchar(100) default NULL,
  `stl_pid` varchar(255) default NULL,
  `stl_pid_num` int(11) NOT NULL,
  `stl_dsid` varchar(255) default NULL,
  PRIMARY KEY  (`stl_id`),
  KEY `stl_pid` (`stl_pid`),
  KEY `stl_dsid` (`stl_dsid`),
  KEY `stl_pid_num` (`stl_pid_num`)
) ENGINE=MyISAM AUTO_INCREMENT=92 DEFAULT CHARSET=latin1;

/*Table structure for table `%TABLE_PREFIX%statistics_proc` */

CREATE TABLE `%TABLE_PREFIX%statistics_proc` (
  `stp_id` int(11) unsigned NOT NULL auto_increment,
  `stp_latestlog` timestamp NULL default NULL,
  `stp_lastproc` date default NULL,
  `stp_count` int(11) default NULL,
  `stp_count_inserted` int(11) default NULL,
  `stp_timestarted` timestamp NULL default NULL,
  `stp_timefinished` timestamp NULL default NULL,
  PRIMARY KEY  (`stp_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

/*Table structure for table `%TABLE_PREFIX%statistics_robots` */

CREATE TABLE `%TABLE_PREFIX%statistics_robots` (
  `str_id` int(11) NOT NULL auto_increment,
  `str_ip` varchar(15) default NULL,
  `str_hostname` varchar(255) default NULL,
  `str_date_added` date default NULL,
  PRIMARY KEY  (`str_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `%TABLE_PREFIX%status` */

CREATE TABLE `%TABLE_PREFIX%status` (
  `sta_id` int(11) unsigned NOT NULL auto_increment,
  `sta_title` varchar(255) default NULL,
  `sta_order` int(11) unsigned default NULL,
  `sta_color` varchar(255) default NULL,
  PRIMARY KEY  (`sta_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%survey` */

CREATE TABLE `%TABLE_PREFIX%survey` (
  `sur_id` int(11) unsigned NOT NULL auto_increment,
  `sur_usr_id` int(11) default NULL,
  `sur_experience` tinyint(1) default NULL,
  `sur_external_freq` tinyint(1) default NULL,
  `sur_3_cat` tinyint(1) default NULL,
  `sur_3_elearn` tinyint(1) default NULL,
  `sur_3_journals` tinyint(1) default NULL,
  `sur_3_blackboard` tinyint(1) default NULL,
  `sur_3_lecture` tinyint(1) default NULL,
  `sur_3_instrumentation` tinyint(1) default NULL,
  `sur_3_datasets` tinyint(1) default NULL,
  `sur_3_remotedb` tinyint(1) default NULL,
  `sur_3_extcom` tinyint(1) default NULL,
  `sur_3_collab` tinyint(1) default NULL,
  `sur_3_other` text,
  `sur_4_cat` tinyint(1) default NULL,
  `sur_4_elearn` tinyint(1) default NULL,
  `sur_4_journals` tinyint(1) default NULL,
  `sur_4_blackboard` tinyint(1) default NULL,
  `sur_4_lecture` tinyint(1) default NULL,
  `sur_4_instrumentation` tinyint(1) default NULL,
  `sur_4_datasets` tinyint(1) default NULL,
  `sur_4_remotedb` tinyint(1) default NULL,
  `sur_4_extcom` tinyint(1) default NULL,
  `sur_4_collab` tinyint(1) default NULL,
  `sur_4_other` text,
  `sur_comments` text,
  `sur_datetime` datetime default NULL,
  PRIMARY KEY  (`sur_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%user` */

CREATE TABLE `%TABLE_PREFIX%user` (
  `usr_id` int(11) unsigned NOT NULL auto_increment,
  `usr_created_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `usr_status` varchar(8) NOT NULL default 'active',
  `usr_password` varchar(32) default NULL,
  `usr_full_name` varchar(255) NOT NULL default '',
  `usr_given_names` varchar(255) default NULL,
  `usr_family_name` varchar(255) default NULL,
  `usr_email` varchar(255) default NULL,
  `usr_preferences` longtext,
  `usr_sms_email` varchar(255) default NULL,
  `usr_username` varchar(50) NOT NULL,
  `usr_shib_username` varchar(50) default NULL,
  `usr_administrator` tinyint(1) default '0',
  `usr_ldap_authentication` tinyint(1) default '0',
  `usr_login_count` int(11) default '0',
  `usr_last_login_date` datetime default '0000-00-00 00:00:00',
  `usr_shib_login_count` int(11) default '0',
  `usr_external_usr_id` int(11) default NULL,
  `usr_super_administrator` tinyint(1) default '0',
  PRIMARY KEY  (`usr_id`),
  UNIQUE KEY `usr_username` (`usr_username`),
  FULLTEXT KEY `usr_fulltext` (`usr_full_name`,`usr_given_names`,`usr_family_name`,`usr_username`,`usr_shib_username`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%wfbehaviour` */

CREATE TABLE `%TABLE_PREFIX%wfbehaviour` (
  `wfb_id` int(11) NOT NULL auto_increment,
  `wfb_title` varchar(255) NOT NULL default '',
  `wfb_description` text NOT NULL,
  `wfb_version` varchar(255) NOT NULL default '1.0',
  `wfb_script_name` varchar(255) NOT NULL default '',
  `wfb_auto` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`wfb_id`)
) ENGINE=MyISAM AUTO_INCREMENT=133 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%workflow` */

CREATE TABLE `%TABLE_PREFIX%workflow` (
  `wfl_id` int(11) NOT NULL auto_increment,
  `wfl_title` varchar(255) default NULL,
  `wfl_version` varchar(255) default NULL,
  `wfl_description` text,
  `wfl_roles` varchar(255) default NULL,
  `wfl_end_button_label` varchar(64) default NULL,
  PRIMARY KEY  (`wfl_id`)
) ENGINE=MyISAM AUTO_INCREMENT=197 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%workflow_roles` */

CREATE TABLE `%TABLE_PREFIX%workflow_roles` (
  `wfr_wfl_id` int(11) unsigned NOT NULL,
  `wfr_aro_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`wfr_wfl_id`,`wfr_aro_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%workflow_sessions` */

CREATE TABLE `%TABLE_PREFIX%workflow_sessions` (
  `wfses_id` int(11) NOT NULL auto_increment,
  `wfses_usr_id` int(11) NOT NULL,
  `wfses_object` blob,
  `wfses_listing` varchar(255) NOT NULL,
  `wfses_date` datetime NOT NULL,
  PRIMARY KEY  (`wfses_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%workflow_state` */

CREATE TABLE `%TABLE_PREFIX%workflow_state` (
  `wfs_id` int(11) unsigned NOT NULL auto_increment,
  `wfs_wfl_id` int(11) unsigned default NULL,
  `wfs_title` varchar(64) default NULL,
  `wfs_description` text,
  `wfs_auto` tinyint(1) default NULL,
  `wfs_wfb_id` int(11) default NULL,
  `wfs_start` tinyint(1) default NULL,
  `wfs_end` tinyint(1) default NULL,
  `wfs_assigned_role_id` int(11) default NULL,
  `wfs_transparent` tinyint(1) default '0',
  `wfs_roles` varchar(255) default NULL,
  PRIMARY KEY  (`wfs_id`)
) ENGINE=MyISAM AUTO_INCREMENT=745 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%workflow_state_link` */

CREATE TABLE `%TABLE_PREFIX%workflow_state_link` (
  `wfsl_id` int(11) NOT NULL auto_increment,
  `wfsl_wfl_id` int(11) NOT NULL default '0',
  `wfsl_from_id` int(11) NOT NULL default '0',
  `wfsl_to_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`wfsl_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1063 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%workflow_state_roles` */

CREATE TABLE `%TABLE_PREFIX%workflow_state_roles` (
  `wfsr_wfs_id` int(11) unsigned NOT NULL,
  `wfsr_aro_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`wfsr_wfs_id`,`wfsr_aro_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%workflow_trigger` */

CREATE TABLE `%TABLE_PREFIX%workflow_trigger` (
  `wft_id` int(11) NOT NULL auto_increment,
  `wft_pid` varchar(64) NOT NULL default '',
  `wft_type_id` int(11) NOT NULL default '0',
  `wft_wfl_id` int(11) NOT NULL default '0',
  `wft_xdis_id` int(11) NOT NULL default '0',
  `wft_order` int(11) NOT NULL default '0',
  `wft_mimetype` varchar(128) NOT NULL default '',
  `wft_icon` varchar(64) NOT NULL default '',
  `wft_ret_id` int(11) NOT NULL default '0',
  `wft_options` int(11) NOT NULL default '0',
  PRIMARY KEY  (`wft_id`)
) ENGINE=MyISAM AUTO_INCREMENT=279 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%xsd` */

CREATE TABLE `%TABLE_PREFIX%xsd` (
  `xsd_id` int(11) unsigned NOT NULL auto_increment,
  `xsd_title` varchar(50) default NULL,
  `xsd_version` varchar(20) default NULL,
  `xsd_file` longblob,
  `xsd_top_element_name` varchar(50) default NULL,
  `xsd_element_prefix` varchar(50) default NULL,
  `xsd_extra_ns_prefixes` varchar(255) default NULL,
  PRIMARY KEY  (`xsd_id`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%xsd_display` */

CREATE TABLE `%TABLE_PREFIX%xsd_display` (
  `xdis_id` int(11) unsigned NOT NULL auto_increment,
  `xdis_xsd_id` int(11) default NULL,
  `xdis_title` varchar(50) default NULL,
  `xdis_version` varchar(20) default NULL,
  `xdis_object_type` tinyint(1) unsigned default '0',
  `xdis_enabled` tinyint(4) default '1',
  PRIMARY KEY  (`xdis_id`)
) ENGINE=MyISAM AUTO_INCREMENT=295 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%xsd_display_attach` */

CREATE TABLE `%TABLE_PREFIX%xsd_display_attach` (
  `att_id` int(11) NOT NULL auto_increment,
  `att_parent_xsdmf_id` int(11) unsigned NOT NULL default '0',
  `att_child_xsdmf_id` int(11) unsigned NOT NULL default '0',
  `att_order` int(7) default NULL,
  PRIMARY KEY  (`att_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%xsd_display_matchfields` */

CREATE TABLE `%TABLE_PREFIX%xsd_display_matchfields` (
  `xsdmf_id` int(11) unsigned NOT NULL auto_increment,
  `xsdmf_xdis_id` int(11) default NULL,
  `xsdmf_xsdsel_id` int(11) default NULL,
  `xsdmf_element` varchar(255) character set utf8 collate utf8_bin default NULL,
  `xsdmf_title` varchar(255) default NULL,
  `xsdmf_description` varchar(255) default NULL,
  `xsdmf_long_description` text,
  `xsdmf_html_input` varchar(20) default NULL,
  `xsdmf_multiple` tinyint(1) default NULL,
  `xsdmf_multiple_limit` int(4) default NULL,
  `xsdmf_valueintag` tinyint(1) default NULL,
  `xsdmf_enabled` tinyint(1) default NULL,
  `xsdmf_order` int(4) default NULL,
  `xsdmf_validation_type` varchar(8) default NULL,
  `xsdmf_required` tinyint(1) default NULL,
  `xsdmf_static_text` text,
  `xsdmf_dynamic_text` text,
  `xsdmf_xdis_id_ref` int(11) default NULL,
  `xsdmf_id_ref` int(11) default NULL,
  `xsdmf_id_ref_save_type` tinyint(1) default '0',
  `xsdmf_is_key` tinyint(1) default NULL,
  `xsdmf_key_match` varchar(255) default NULL,
  `xsdmf_show_in_view` tinyint(1) default NULL,
  `xsdmf_smarty_variable` varchar(50) default NULL,
  `xsdmf_fez_variable` varchar(50) default NULL,
  `xsdmf_enforced_prefix` varchar(255) default NULL,
  `xsdmf_value_prefix` varchar(255) default NULL,
  `xsdmf_selected_option` varchar(255) default NULL,
  `xsdmf_dynamic_selected_option` varchar(255) default NULL,
  `xsdmf_image_location` varchar(255) default NULL,
  `xsdmf_parent_key_match` varchar(255) default NULL,
  `xsdmf_data_type` varchar(20) default 'varchar',
  `xsdmf_indexed` tinyint(1) default '0',
  `xsdmf_sek_id` int(11) default NULL,
  `xsdmf_cvo_id` int(11) default NULL,
  `xsdmf_cvo_min_level` int(11) default NULL,
  `xsdmf_cvo_save_type` tinyint(1) default '0',
  `xsdmf_original_xsdmf_id` int(11) default NULL,
  `xsdmf_attached_xsdmf_id` int(11) default NULL,
  `xsdmf_cso_value` varchar(7) default NULL,
  `xsdmf_citation_browse` int(1) default '0',
  `xsdmf_citation` int(1) default '0',
  `xsdmf_citation_bold` int(1) default '0',
  `xsdmf_citation_italics` int(1) default '0',
  `xsdmf_citation_order` int(4) default NULL,
  `xsdmf_citation_brackets` int(1) default '0',
  `xsdmf_citation_prefix` varchar(100) default NULL,
  `xsdmf_citation_suffix` varchar(100) default NULL,
  `xsdmf_use_parent_option_list` int(1) default '0',
  `xsdmf_parent_option_xdis_id` int(11) default NULL,
  `xsdmf_parent_option_child_xsdmf_id` int(11) default NULL,
  `xsdmf_org_level` varchar(64) default NULL,
  `xsdmf_use_org_to_fill` int(1) default '0',
  `xsdmf_org_fill_xdis_id` int(11) default NULL,
  `xsdmf_org_fill_xsdmf_id` int(11) default NULL,
  `xsdmf_asuggest_xdis_id` int(11) default NULL,
  `xsdmf_asuggest_xsdmf_id` int(11) default NULL,
  `xsdmf_date_type` tinyint(1) default '0',
  `xsdmf_meta_header` tinyint(1) default '0',
  `xsdmf_meta_header_name` varchar(64) default NULL,
  PRIMARY KEY  (`xsdmf_id`),
  KEY `xsdmf_xsdsel_id` (`xsdmf_xsdsel_id`),
  KEY `xsdmf_xdis_id` (`xsdmf_xdis_id`),
  KEY `xsdmf_sek_id` (`xsdmf_sek_id`),
  FULLTEXT KEY `xsdmf_element` (`xsdmf_element`)
) ENGINE=MyISAM AUTO_INCREMENT=11053 DEFAULT CHARSET=latin1;

/*Table structure for table `%TABLE_PREFIX%xsd_display_mf_option` */

CREATE TABLE `%TABLE_PREFIX%xsd_display_mf_option` (
  `mfo_id` int(10) unsigned NOT NULL auto_increment,
  `mfo_fld_id` int(10) unsigned NOT NULL default '0',
  `mfo_value` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`mfo_id`),
  KEY `icf_fld_id` (`mfo_fld_id`)
) ENGINE=MyISAM AUTO_INCREMENT=155 DEFAULT CHARSET=utf8;

/*Table structure for table `%TABLE_PREFIX%xsd_loop_subelement` */

CREATE TABLE `%TABLE_PREFIX%xsd_loop_subelement` (
  `xsdsel_id` int(11) NOT NULL auto_increment,
  `xsdsel_xsdmf_id` int(11) default NULL,
  `xsdsel_title` varchar(255) default NULL,
  `xsdsel_type` varchar(30) default NULL,
  `xsdsel_order` int(6) default NULL,
  `xsdsel_attribute_loop_xdis_id` int(11) default '0',
  `xsdsel_attribute_loop_xsdmf_id` int(11) default '0',
  `xsdsel_indicator_xdis_id` int(11) default '0',
  `xsdsel_indicator_xsdmf_id` int(11) default '0',
  `xsdsel_indicator_value` varchar(255) default NULL,
  PRIMARY KEY  (`xsdsel_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1488 DEFAULT CHARSET=latin1;

/*Table structure for table `%TABLE_PREFIX%xsd_relationship` */

CREATE TABLE `%TABLE_PREFIX%xsd_relationship` (
  `xsdrel_id` int(11) NOT NULL auto_increment,
  `xsdrel_xsdmf_id` int(11) default NULL,
  `xsdrel_xdis_id` int(11) default NULL,
  `xsdrel_order` int(6) default NULL,
  PRIMARY KEY  (`xsdrel_id`)
) ENGINE=MyISAM AUTO_INCREMENT=600 DEFAULT CHARSET=latin1;
