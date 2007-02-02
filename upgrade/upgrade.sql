

CREATE TABLE `%TABLE_PREFIX%auth_index2` (
  `authi_id` int(11) NOT NULL auto_increment,
  `authi_pid` varchar(64) NOT NULL,
  `authi_role` varchar(64) NOT NULL,
  `authi_arg_id` int(11) NOT NULL,
  `authi_pid_num` int(11) NOT NULL,
  PRIMARY KEY  (`authi_id`),
  KEY `authi_role` (`authi_role`),
  KEY `authi_arg_id` (`authi_arg_id`),
  KEY `authi_role_pid` (`authi_pid`,`authi_role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `%TABLE_PREFIX%auth_rule_group_rules` (
  `argr_arg_id` int(11) NOT NULL,
  `argr_ar_id` int(11) NOT NULL,
  KEY `argr_arg_id` (`argr_arg_id`),
  KEY `argr_ar_id` (`argr_ar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `%TABLE_PREFIX%auth_rule_group_users` (
  `argu_id` int(11) NOT NULL auto_increment,
  `argu_usr_id` int(11) NOT NULL,
  `argu_arg_id` int(11) NOT NULL,
  PRIMARY KEY  (`argu_id`),
  KEY `argu_usr_id` (`argu_usr_id`),
  KEY `argu_arg_id` (`argu_arg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%TABLE_PREFIX%auth_rule_groups` (
  `arg_id` int(11) NOT NULL auto_increment,
  `arg_md5` varchar(128) NOT NULL,
  PRIMARY KEY  (`arg_id`),
  KEY `arg_md5` (`arg_md5`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%TABLE_PREFIX%auth_rules` (
  `ar_id` int(11) NOT NULL auto_increment,
  `ar_rule` varchar(64) NOT NULL,
  `ar_value` varchar(255) NOT NULL,
  PRIMARY KEY  (`ar_id`),
  KEY `ar_value` (`ar_value`),
  FULLTEXT KEY `ar_rule` (`ar_rule`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `%TABLE_PREFIX%author` 
      CHANGE COLUMN `aut_assessed` `aut_assessed` varchar(1) default NULL,
      default CHARACTER SET utf8;

ALTER TABLE `%TABLE_PREFIX%author_org_structure` 
  CHANGE COLUMN `auo_assessed` `auo_assessed` varchar(1) default NULL,
  ADD COLUMN `auo_assessed_year` varchar(11) default NULL,
  ADD UNIQUE KEY `support_unique_key` (`auo_org_id`,`auo_aut_id`,`auo_cla_id`,`auo_fun_id`)
  default CHARACTER SET utf8;



CREATE TABLE `%TABLE_PREFIX%config` (                     
              `config_id` int(11) NOT NULL auto_increment,  
              `config_name` varchar(32) NOT NULL,           
              `config_module` varchar(32) NOT NULL,         
              `config_value` varchar(256) NOT NULL,         
              PRIMARY KEY  (`config_id`),                    
              UNIQUE KEY `config_name` (`config_name`,`config_module`) 
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `%TABLE_PREFIX%config` ('config_name', 'config_module', 'config_value') 
VALUES ('datamodel_version','core','1');

ALTER TABLE `%TABLE_PREFIX%controlled_vocab` 
  ADD COLUMN `cvo_external_id` int(11) default NULL, 
  ADD KEY `cvo_title` (`cvo_title`);

ALTER TABLE `%TABLE_PREFIX%controlled_vocab_relationship` 
  drop key `cvr_parent_cvo_id`,
  add KEY `cvr_child_cvo_id` (`cvr_child_cvo_id`),
  add KEY `cvr_parent_cvo_id` (`cvr_parent_cvo_id`);


CREATE TABLE `%TABLE_PREFIX%eprints_import_pids` (
  `epr_eprints_id` int(11) NOT NULL,
  `epr_fez_pid` varchar(255) NOT NULL,
  `epr_date_added` datetime default NULL,
  PRIMARY KEY  (`epr_eprints_id`,`epr_fez_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%TABLE_PREFIX%fulltext_engine` (
  `fte_id` int(11) NOT NULL auto_increment,
  `fte_fti_id` mediumint(9) NOT NULL default '0',
  `fte_key_id` mediumint(9) NOT NULL default '0',
  `fte_weight` smallint(4) NOT NULL default '0',
  PRIMARY KEY  (`fte_id`),
  KEY `key_id` (`fte_key_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%TABLE_PREFIX%fulltext_index` (
  `fti_id` int(11) NOT NULL auto_increment,
  `fti_pid` varchar(64) NOT NULL,
  `fti_dsid` varchar(128) NOT NULL,
  `fti_indexed` datetime NOT NULL,
  PRIMARY KEY  (`fti_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%TABLE_PREFIX%fulltext_keywords` (
  `ftk_id` int(11) NOT NULL auto_increment,
  `ftk_twoletters` char(2) NOT NULL,
  `ftk_word` varchar(64) NOT NULL,
  PRIMARY KEY  (`ftk_id`),
  UNIQUE KEY `ftk_word` (`ftk_word`),
  KEY `ftk_twoletters` (`ftk_twoletters`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

 
ALTER TABLE `%TABLE_PREFIX%org_structure`
  add column `org_desc` text,
  add column `org_image_filename` varchar(255) default NULL;


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
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `%TABLE_PREFIX%record_matching_field` 
  drop key `rmf_rec_pid`,
  drop KEY `combo_pid_xsdmf`,
  drop key `rmf_varchar`;

ALTER TABLE `%TABLE_PREFIX%record_matching_field` 
  change column `rmf_rec_pid` `rmf_rec_pid` blob,
  change column `rmf_dsid` `rmf_dsid` blob,
  change column `rmf_varchar` `rmf_varchar` blob;

ALTER TABLE `%TABLE_PREFIX%record_matching_field` 
  change column `rmf_rec_pid` `rmf_rec_pid` varchar(64) character set utf8 NOT NULL default '',
  change column `rmf_dsid` `rmf_dsid` varchar(255) character set utf8 default NULL,
  change column `rmf_varchar` `rmf_varchar` varchar(255) character set utf8 default NULL,
  ADD COLUMN `rmf_rec_pid_num` int(11) NOT NULL,
  ADD KEY `rmf_date` (`rmf_date`),
  ADD KEY `rmf_rec_pid_num` (`rmf_rec_pid_num`),
  ADD KEY `rmf_int` (`rmf_int`),
  ADD KEY `rmf_rec_pid` (`rmf_rec_pid`),
  ADD KEY `combo_pid_xsdmf` (`rmf_rec_pid`,`rmf_xsdmf_id`),
  ADD KEY `combo_pid_num_xsdmf` (`rmf_rec_pid_num`,`rmf_xsdmf_id`),
  ADD KEY `rmf_varchar_combo` (`rmf_xsdmf_id`,`rmf_varchar`),
  ADD KEY `combo_pid_num_xsdmf_int` (`rmf_rec_pid_num`,`rmf_xsdmf_id`,`rmf_int`),
  default CHARACTER SET utf8; 
 


ALTER TABLE `%TABLE_PREFIX%search_key` 
  drop key `sek_title`;

DROP TABLE `%TABLE_PREFIX%statistics`;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `%TABLE_PREFIX%statistics_proc` (
  `stp_id` int(11) unsigned NOT NULL auto_increment,
  `stp_latestlog` timestamp NULL default NULL,
  `stp_lastproc` date default NULL,
  `stp_count` int(11) default NULL,
  `stp_count_inserted` int(11) default NULL,
  `stp_timestarted` timestamp NULL default NULL,
  `stp_timefinished` timestamp NULL default NULL,
  PRIMARY KEY  (`stp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `%TABLE_PREFIX%statistics_robots` (
  `str_id` int(11) NOT NULL auto_increment,
  `str_ip` varchar(15) default NULL,
  `str_hostname` varchar(255) default NULL,
  `str_date_added` date default NULL,
  PRIMARY KEY  (`str_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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

ALTER TABLE `%TABLE_PREFIX%user`
  change column `usr_password` `usr_password` varchar(32) default NULL,
  change column `usr_username` `usr_username` varchar(50) NOT NULL,
  ADD COLUMN `usr_given_names` varchar(255) default NULL after `usr_full_name`,
  ADD COLUMN `usr_family_name` varchar(255) default NULL after `usr_given_names`,
   add column `usr_shib_username` varchar(50) default NULL after `usr_username`,
  add column `usr_shib_login_count` int(11) default '0' ,
  ADD COLUMN `usr_external_usr_id` int(11) default NULL ,
  ADD FULLTEXT KEY `usr_fulltext` (`usr_full_name`,`usr_given_names`,`usr_family_name`,`usr_username`,`usr_shib_username`);

ALTER TABLE `%TABLE_PREFIX%xsd_display` 
        add column `xdis_enabled` tinyint(4) default '1';

ALTER TABLE `%TABLE_PREFIX%xsd_display_matchfields` 
  change column `xsdmf_element` `xsdmf_element` varchar(50) default NULL,
  add column `xsdmf_xdis_id_ref` int(11) default NULL AFTER `xsdmf_dynamic_text`,
  add column `xsdmf_id_ref_save_type` tinyint(1) default '0' after `xsdmf_id_ref`,
  add column `xsdmf_cvo_save_type` tinyint(1) default '0' after `xsdmf_cvo_min_level`,
  add column `xsdmf_citation_prefix` varchar(100) default NULL after `xsdmf_citation_brackets`,
  add column `xsdmf_citation_suffix` varchar(100) default NULL after `xsdmf_citation_prefix`,
  add column `xsdmf_meta_header` tinyint(1) default '0',
  add column `xsdmf_meta_header_name` varchar(64) default NULL,
  drop key `xsdmf_element`,
  add FULLTEXT KEY `xsdmf_element` (`xsdmf_element`),
    default CHARACTER SET utf8;

ALTER TABLE `%TABLE_PREFIX%xsd_loop_subelement` 
  add column `xsdsel_attribute_loop_xdis_id` int(11) default '0',
  add column `xsdsel_indicator_xdis_id` int(11) default '0',
  add column `xsdsel_indicator_xsdmf_id` int(11) default '0',
  add column `xsdsel_indicator_value` varchar(255) default NULL;


DROP TABLE IF EXISTS `%TABLE_PREFIX%auth_index`;
