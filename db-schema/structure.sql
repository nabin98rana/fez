/*
SQLyog v4.05
Host - 4.1.10a-standard-log : Database - dev_espace
*********************************************************************
Server version : 4.1.10a-standard-log
*/


create database if not exists `dev_espace`;

USE `dev_espace`;

/*Table structure for table `dev_espace`.`espace_collection` */

CREATE TABLE `espace_collection` (
  `col_id` int(11) unsigned NOT NULL auto_increment,
  `col_title` varchar(30) default NULL,
  `col_status` set('active','archived') NOT NULL default 'active',
  `col_lead_usr_id` int(11) unsigned NOT NULL default '0',
  `col_pid` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`col_id`),
  KEY `col_lead_usr_id` (`col_lead_usr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_collection_news` */

CREATE TABLE `espace_collection_news` (
  `con_nws_id` int(11) unsigned NOT NULL default '0',
  `con_col_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`con_col_id`,`con_nws_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_collection_status` */

CREATE TABLE `espace_collection_status` (
  `cos_id` int(10) unsigned NOT NULL auto_increment,
  `cos_col_id` int(10) unsigned NOT NULL default '0',
  `cos_sta_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cos_id`),
  KEY `cos_col_id` (`cos_col_id`,`cos_sta_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_collection_status_date` */

CREATE TABLE `espace_collection_status_date` (
  `csd_id` int(11) unsigned NOT NULL auto_increment,
  `csd_col_id` int(11) unsigned NOT NULL default '0',
  `csd_sta_id` int(10) unsigned NOT NULL default '0',
  `csd_date_field` varchar(64) NOT NULL default '',
  `csd_label` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`csd_id`),
  UNIQUE KEY `csd_col_id` (`csd_col_id`,`csd_sta_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_collection_subcategory` */

CREATE TABLE `espace_collection_subcategory` (
  `cosc_id` int(11) unsigned NOT NULL auto_increment,
  `coc_id` int(11) unsigned NOT NULL default '0',
  `cosc_title` varchar(64) NOT NULL default '0',
  `cosc_quick` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cosc_id`),
  UNIQUE KEY `uniq_subcat` (`cosc_id`,`cosc_title`),
  KEY `coc_id` (`coc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_collection_user` */

CREATE TABLE `espace_collection_user` (
  `cou_id` int(11) unsigned NOT NULL auto_increment,
  `cou_col_id` int(11) unsigned NOT NULL default '0',
  `cou_usr_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cou_id`),
  KEY `pru_col_id` (`cou_col_id`,`cou_usr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_group` */

CREATE TABLE `espace_group` (
  `grp_id` int(11) unsigned NOT NULL auto_increment,
  `grp_title` varchar(30) default NULL,
  `grp_status` set('active','archived') NOT NULL default 'active',
  `grp_lead_usr_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`grp_id`),
  KEY `col_lead_usr_id` (`grp_lead_usr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_group_user` */

CREATE TABLE `espace_group_user` (
  `gpu_id` int(11) unsigned NOT NULL auto_increment,
  `gpu_grp_id` int(11) unsigned NOT NULL default '0',
  `gpu_usr_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`gpu_id`),
  KEY `pru_col_id` (`gpu_grp_id`,`gpu_usr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_history_type` */

CREATE TABLE `espace_history_type` (
  `htt_id` tinyint(2) unsigned NOT NULL auto_increment,
  `htt_name` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`htt_id`),
  UNIQUE KEY `htt_name` (`htt_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_news` */

CREATE TABLE `espace_news` (
  `nws_id` int(11) unsigned NOT NULL auto_increment,
  `nws_usr_id` int(11) unsigned NOT NULL default '0',
  `nws_created_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `nws_title` varchar(255) NOT NULL default '',
  `nws_message` text NOT NULL,
  `nws_status` varchar(8) NOT NULL default 'active',
  PRIMARY KEY  (`nws_id`),
  UNIQUE KEY `nws_title` (`nws_title`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_record` */

CREATE TABLE `espace_record` (
  `rec_id` int(11) unsigned NOT NULL auto_increment,
  `rec_usr_id` int(10) unsigned NOT NULL default '0',
  `rec_col_id` int(11) unsigned NOT NULL default '0',
  `rec_coc_id` int(11) unsigned NOT NULL default '0',
  `rec_cosc_id` int(11) unsigned NOT NULL default '0',
  `rec_sta_id` tinyint(1) NOT NULL default '0',
  `rec_pid` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rec_id`),
  KEY `rec_col_id` (`rec_col_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_record_association` */

CREATE TABLE `espace_record_association` (
  `rea_record_id` int(10) unsigned NOT NULL default '0',
  `rea_associated_id` int(10) unsigned NOT NULL default '0',
  KEY `isa_record_id` (`rea_record_id`,`rea_associated_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_record_attachment` */

CREATE TABLE `espace_record_attachment` (
  `rat_id` int(10) unsigned NOT NULL auto_increment,
  `rat_rec_id` int(10) unsigned NOT NULL default '0',
  `rat_usr_id` int(10) unsigned NOT NULL default '0',
  `rat_created_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `rat_description` text,
  `rat_unknown_user` varchar(255) default NULL,
  PRIMARY KEY  (`rat_id`),
  KEY `rat_rec_id` (`rat_rec_id`,`rat_usr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_record_attachment_file` */

CREATE TABLE `espace_record_attachment_file` (
  `raf_id` int(10) unsigned NOT NULL auto_increment,
  `raf_rat_id` int(10) unsigned NOT NULL default '0',
  `raf_file` longblob,
  `raf_filename` varchar(255) NOT NULL default '',
  `raf_filetype` varchar(255) default NULL,
  `raf_filesize` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`raf_id`),
  KEY `raf_rat_id` (`raf_rat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_record_history` */

CREATE TABLE `espace_record_history` (
  `his_id` int(10) unsigned NOT NULL auto_increment,
  `his_rec_id` int(10) unsigned NOT NULL default '0',
  `his_usr_id` int(11) unsigned NOT NULL default '0',
  `his_created_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `his_summary` text NOT NULL,
  `his_htt_id` varchar(20) NOT NULL default '',
  `his_is_hidden` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`his_id`),
  KEY `his_id` (`his_id`),
  KEY `his_rec_id` (`his_rec_id`),
  KEY `his_created_date` (`his_created_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_record_user` */

CREATE TABLE `espace_record_user` (
  `reu_rec_id` int(10) unsigned NOT NULL default '0',
  `reu_usr_id` int(10) unsigned NOT NULL default '0',
  `reu_assigned_date` datetime default NULL,
  PRIMARY KEY  (`reu_rec_id`,`reu_usr_id`),
  KEY `reu_usr_id` (`reu_usr_id`),
  KEY `reu_rec_id` (`reu_rec_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_status` */

CREATE TABLE `espace_status` (
  `sta_id` int(10) NOT NULL auto_increment,
  `sta_title` varchar(64) NOT NULL default '',
  `sta_abbreviation` char(3) NOT NULL default '',
  `sta_rank` int(2) NOT NULL default '0',
  `sta_color` varchar(7) NOT NULL default '',
  `sta_is_closed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`sta_id`),
  UNIQUE KEY `sta_abbreviation` (`sta_abbreviation`),
  KEY `sta_rank` (`sta_rank`),
  KEY `sta_is_closed` (`sta_is_closed`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_user` */

CREATE TABLE `espace_user` (
  `usr_id` int(11) unsigned NOT NULL auto_increment,
  `usr_created_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `usr_status` varchar(8) NOT NULL default 'active',
  `usr_password` varchar(32) NOT NULL default '',
  `usr_full_name` varchar(255) NOT NULL default '',
  `usr_email` varchar(255) default NULL,
  `usr_preferences` longtext,
  `usr_sms_email` varchar(255) default NULL,
  `usr_username` varchar(15) default NULL,
  `usr_administrator` tinyint(1) default '0',
  `usr_ldap_authentication` tinyint(1) default '0',
  `usr_login_count` int(11) default '0',
  `usr_last_login_date` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`usr_id`),
  KEY `usr_email_password` (`usr_email`,`usr_password`),
  KEY `usr_email` (`usr_email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_userold` */

CREATE TABLE `espace_userold` (
  `usr_id` int(11) unsigned NOT NULL auto_increment,
  `usr_created_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `usr_status` varchar(8) NOT NULL default 'active',
  `usr_password` varchar(32) NOT NULL default '',
  `usr_full_name` varchar(255) NOT NULL default '',
  `usr_email` varchar(255) default NULL,
  `usr_role` tinyint(1) unsigned NOT NULL default '1',
  `usr_preferences` longtext,
  `usr_sms_email` varchar(255) default NULL,
  `usr_ldap_username` varchar(15) default NULL,
  `usr_primary_col_id` int(11) unsigned NOT NULL default '0',
  `usr_primary_campus_id` int(11) default '19',
  PRIMARY KEY  (`usr_id`),
  KEY `usr_email_password` (`usr_email`,`usr_password`),
  KEY `usr_email` (`usr_email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_xsd` */

CREATE TABLE `espace_xsd` (
  `xsd_id` int(11) unsigned NOT NULL auto_increment,
  `xsd_title` varchar(50) default NULL,
  `xsd_version` varchar(20) default NULL,
  `xsd_file` longblob,
  `xsd_top_element_name` varchar(50) default NULL,
  `xsd_element_prefix` varchar(50) default NULL,
  `xsd_extra_ns_prefixes` varchar(255) default NULL,
  PRIMARY KEY  (`xsd_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_xsd_display` */

CREATE TABLE `espace_xsd_display` (
  `xdis_id` int(11) unsigned NOT NULL auto_increment,
  `xdis_xsd_id` int(11) default NULL,
  `xdis_title` varchar(50) default NULL,
  `xdis_version` varchar(20) default NULL,
  PRIMARY KEY  (`xdis_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_xsd_display_matchfields` */

CREATE TABLE `espace_xsd_display_matchfields` (
  `xsdmf_id` int(11) unsigned NOT NULL auto_increment,
  `xsdmf_xdis_id` int(11) default NULL,
  `xsdmf_xsdsel_id` int(11) default NULL,
  `xsdmf_element` varchar(255) default NULL,
  `xsdmf_title` varchar(255) default NULL,
  `xsdmf_description` varchar(255) default NULL,
  `xsdmf_html_input` varchar(20) default NULL,
  `xsdmf_multiple` tinyint(1) default NULL,
  `xsdmf_multiple_limit` int(4) default NULL,
  `xsdmf_valueintag` tinyint(1) default NULL,
  `xsdmf_enabled` tinyint(1) default NULL,
  `xsdmf_order` int(4) default NULL,
  `xsdmf_xml_order` int(4) default NULL,
  `xsdmf_validation_type` varchar(8) default NULL,
  `xsdmf_required` tinyint(1) default NULL,
  `xsdmf_static_text` text,
  `xsdmf_dynamic_text` text,
  `xsdmf_id_ref` int(11) default NULL,
  `xsdmf_is_key` tinyint(1) default NULL,
  `xsdmf_key_match` varchar(255) default NULL,
  `xsdmf_show_in_view` tinyint(1) default NULL,
  `xsdmf_smarty_variable` varchar(50) default NULL,
  `xsdmf_espace_variable` varchar(50) default NULL,
  `xsdmf_enforced_prefix` varchar(255) default NULL,
  `xsdmf_value_prefix` varchar(255) default NULL,
  `xsdmf_selected_option` varchar(255) default NULL,
  `xsdmf_dynamic_selected_option` varchar(255) default NULL,
  `xsdmf_image_location` varchar(255) default NULL,
  `xsdmf_parent_key_match` varchar(255) default NULL,
  PRIMARY KEY  (`xsdmf_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_xsd_display_mf_option` */

CREATE TABLE `espace_xsd_display_mf_option` (
  `mfo_id` int(10) unsigned NOT NULL auto_increment,
  `mfo_fld_id` int(10) unsigned NOT NULL default '0',
  `mfo_value` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`mfo_id`),
  KEY `icf_fld_id` (`mfo_fld_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_xsd_loop_subelement` */

CREATE TABLE `espace_xsd_loop_subelement` (
  `xsdsel_id` int(11) NOT NULL auto_increment,
  `xsdsel_xsdmf_id` int(11) default NULL,
  `xsdsel_title` varchar(255) default NULL,
  `xsdsel_type` varchar(30) default NULL,
  `xsdsel_order` int(6) default NULL,
  `xsdsel_attribute_loop_xsdmf_id` int(11) default '0',
  PRIMARY KEY  (`xsdsel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_xsd_relationship` */

CREATE TABLE `espace_xsd_relationship` (
  `xsdrel_id` int(11) NOT NULL auto_increment,
  `xsdrel_xsdmf_id` int(11) default NULL,
  `xsdrel_xdis_id` int(11) default NULL,
  `xsdrel_order` int(6) default NULL,
  PRIMARY KEY  (`xsdrel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `dev_espace`.`espace_xsd_xsl` */

CREATE TABLE `espace_xsd_xsl` (
  `xsl_id` int(11) unsigned NOT NULL auto_increment,
  `xsl_xsd_id` int(11) default NULL,
  `xsl_title` varchar(50) default NULL,
  `xsl_version` varchar(20) default NULL,
  `xsl_file` longblob,
  PRIMARY KEY  (`xsl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
