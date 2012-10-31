-- MySQL dump 10.13  Distrib 5.5.19, for Linux (x86_64)
--
-- Host: dbdev    Database: _fez2_toxic
-- ------------------------------------------------------
-- Server version	5.5.10-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `fez_ad_hoc_sql`
--

DROP TABLE IF EXISTS `fez_ad_hoc_sql`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_ad_hoc_sql` (
  `ahs_id` int(11) NOT NULL AUTO_INCREMENT,
  `ahs_name` varchar(64) DEFAULT NULL,
  `ahs_query` text,
  `ahs_query_show` text,
  `ahs_query_count` text,
  PRIMARY KEY (`ahs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_datastream_index2`
--

DROP TABLE IF EXISTS `fez_auth_datastream_index2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_datastream_index2` (
  `authdi_did` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `authdi_role` int(11) unsigned NOT NULL DEFAULT '0',
  `authdi_arg_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`authdi_did`,`authdi_role`,`authdi_arg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_datastream_index2_not_inherited`
--

DROP TABLE IF EXISTS `fez_auth_datastream_index2_not_inherited`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_datastream_index2_not_inherited` (
  `authdii_did` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `authdii_role` int(11) unsigned NOT NULL DEFAULT '0',
  `authdii_arg_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`authdii_did`,`authdii_role`,`authdii_arg_id`),
  KEY `authii_role_arg_id` (`authdii_role`,`authdii_arg_id`),
  KEY `authii_role` (`authdii_did`,`authdii_role`),
  KEY `authii_pid_arg_id` (`authdii_did`,`authdii_arg_id`),
  KEY `authii_pid` (`authdii_did`),
  KEY `authii_arg_id` (`authdii_arg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_datastream_index2_not_inherited__shadow`
--

DROP TABLE IF EXISTS `fez_auth_datastream_index2_not_inherited__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_datastream_index2_not_inherited__shadow` (
  `authdii_did` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `authdii_role` int(11) unsigned NOT NULL DEFAULT '0',
  `authdii_arg_id` int(11) unsigned NOT NULL DEFAULT '0',
  `authdii_edition_stamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`authdii_did`,`authdii_role`,`authdii_arg_id`,`authdii_edition_stamp`),
  KEY `authii_role_arg_id` (`authdii_role`,`authdii_arg_id`),
  KEY `authii_role` (`authdii_did`,`authdii_role`),
  KEY `authii_pid_arg_id` (`authdii_did`,`authdii_arg_id`),
  KEY `authii_pid` (`authdii_did`),
  KEY `authii_arg_id` (`authdii_arg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_index2`
--

DROP TABLE IF EXISTS `fez_auth_index2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_index2` (
  `authi_pid` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `authi_role` int(11) unsigned NOT NULL DEFAULT '0',
  `authi_arg_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`authi_pid`,`authi_role`,`authi_arg_id`),
  KEY `authi_role_arg_id` (`authi_role`,`authi_arg_id`),
  KEY `authi_role` (`authi_pid`,`authi_role`),
  KEY `authi_pid_arg_id` (`authi_pid`,`authi_arg_id`),
  KEY `authi_pid` (`authi_pid`),
  KEY `authi_arg_id` (`authi_arg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_index2_lister`
--

DROP TABLE IF EXISTS `fez_auth_index2_lister`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_index2_lister` (
  `authi_pid` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `authi_arg_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`authi_pid`,`authi_arg_id`),
  UNIQUE KEY `authi_pid` (`authi_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_index2_not_inherited`
--

DROP TABLE IF EXISTS `fez_auth_index2_not_inherited`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_index2_not_inherited` (
  `authii_pid` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `authii_role` int(11) unsigned NOT NULL DEFAULT '0',
  `authii_arg_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`authii_pid`,`authii_role`,`authii_arg_id`),
  KEY `authii_role_arg_id` (`authii_role`,`authii_arg_id`),
  KEY `authii_role` (`authii_pid`,`authii_role`),
  KEY `authii_pid_arg_id` (`authii_pid`,`authii_arg_id`),
  KEY `authii_pid` (`authii_pid`),
  KEY `authii_arg_id` (`authii_arg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_index2_not_inherited__shadow`
--

DROP TABLE IF EXISTS `fez_auth_index2_not_inherited__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_index2_not_inherited__shadow` (
  `authii_pid` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `authii_role` int(11) unsigned NOT NULL DEFAULT '0',
  `authii_arg_id` int(11) unsigned NOT NULL DEFAULT '0',
  `authii_edition_stamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`authii_pid`,`authii_role`,`authii_arg_id`,`authii_edition_stamp`),
  KEY `authii_role_arg_id` (`authii_role`,`authii_arg_id`),
  KEY `authii_role` (`authii_pid`,`authii_role`),
  KEY `authii_pid_arg_id` (`authii_pid`,`authii_arg_id`),
  KEY `authii_pid` (`authii_pid`),
  KEY `authii_arg_id` (`authii_arg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_index2_pre_fez2_upgrade`
--

DROP TABLE IF EXISTS `fez_auth_index2_pre_fez2_upgrade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_index2_pre_fez2_upgrade` (
  `authi_id` int(11) NOT NULL AUTO_INCREMENT,
  `authi_pid` varchar(64) NOT NULL,
  `authi_role` varchar(64) NOT NULL,
  `authi_arg_id` int(11) NOT NULL,
  `authi_pid_num` int(11) NOT NULL,
  PRIMARY KEY (`authi_id`),
  KEY `authi_pid` (`authi_pid`),
  KEY `authi_role` (`authi_role`),
  KEY `authi_arg_id` (`authi_arg_id`),
  KEY `authi_role_pid` (`authi_pid`,`authi_role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_quick_rules`
--

DROP TABLE IF EXISTS `fez_auth_quick_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_quick_rules` (
  `qac_id` int(11) unsigned NOT NULL DEFAULT '0',
  `qac_role` int(11) DEFAULT NULL,
  `qac_arg_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_quick_rules_id`
--

DROP TABLE IF EXISTS `fez_auth_quick_rules_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_quick_rules_id` (
  `qai_id` int(11) DEFAULT NULL,
  `qai_title` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_quick_rules_pid`
--

DROP TABLE IF EXISTS `fez_auth_quick_rules_pid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_quick_rules_pid` (
  `qrp_pid` varchar(255) DEFAULT NULL,
  `qrp_qac_id` int(11) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_quick_template`
--

DROP TABLE IF EXISTS `fez_auth_quick_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_quick_template` (
  `qat_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `qat_title` varchar(100) DEFAULT NULL,
  `qat_value` text,
  PRIMARY KEY (`qat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_roles`
--

DROP TABLE IF EXISTS `fez_auth_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_roles` (
  `aro_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `aro_role` varchar(64) NOT NULL,
  `aro_ranking` int(11) unsigned NOT NULL,
  PRIMARY KEY (`aro_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_rule_group_rules`
--

DROP TABLE IF EXISTS `fez_auth_rule_group_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_rule_group_rules` (
  `argr_arg_id` int(11) NOT NULL,
  `argr_ar_id` int(11) NOT NULL,
  KEY `argr_arg_id` (`argr_arg_id`),
  KEY `argr_ar_id` (`argr_ar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_rule_group_users`
--

DROP TABLE IF EXISTS `fez_auth_rule_group_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_rule_group_users` (
  `argu_id` int(11) NOT NULL AUTO_INCREMENT,
  `argu_usr_id` int(11) NOT NULL,
  `argu_arg_id` int(11) NOT NULL,
  PRIMARY KEY (`argu_id`),
  KEY `argu_usr_id` (`argu_usr_id`),
  KEY `argu_arg_id` (`argu_arg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_rule_groups`
--

DROP TABLE IF EXISTS `fez_auth_rule_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_rule_groups` (
  `arg_id` int(11) NOT NULL AUTO_INCREMENT,
  `arg_md5` varchar(128) NOT NULL,
  PRIMARY KEY (`arg_id`),
  KEY `arg_md5` (`arg_md5`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_auth_rules`
--

DROP TABLE IF EXISTS `fez_auth_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_auth_rules` (
  `ar_id` int(11) NOT NULL AUTO_INCREMENT,
  `ar_rule` varchar(64) NOT NULL,
  `ar_value` varchar(255) NOT NULL,
  PRIMARY KEY (`ar_id`),
  KEY `ar_value` (`ar_value`),
  FULLTEXT KEY `ar_rule` (`ar_rule`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_author`
--

DROP TABLE IF EXISTS `fez_author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_author` (
  `aut_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `aut_org_username` varchar(255) DEFAULT NULL,
  `aut_org_staff_id` varchar(255) DEFAULT NULL,
  `aut_display_name` varchar(255) DEFAULT NULL,
  `aut_fname` varchar(255) DEFAULT NULL,
  `aut_mname` varchar(255) DEFAULT NULL,
  `aut_lname` varchar(255) DEFAULT NULL,
  `aut_title` varchar(255) DEFAULT NULL,
  `aut_position` varchar(255) DEFAULT NULL,
  `aut_function` varchar(255) DEFAULT NULL,
  `aut_cv_link` varchar(255) DEFAULT NULL,
  `aut_homepage_link` varchar(255) DEFAULT NULL,
  `aut_assessed` varchar(1) DEFAULT NULL,
  `aut_created_date` date DEFAULT NULL,
  `aut_update_date` date DEFAULT NULL,
  `aut_external_id` varchar(50) DEFAULT NULL,
  `aut_ref_num` varchar(50) DEFAULT NULL,
  `aut_email` varchar(255) DEFAULT NULL,
  `aut_mypub_url` varchar(255) DEFAULT NULL,
  `aut_researcher_id` varchar(255) DEFAULT NULL,
  `aut_scopus_id` varchar(255) DEFAULT NULL,
  `aut_rid_password` varchar(255) DEFAULT NULL,
  `aut_description` text,
  `aut_people_australia_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`aut_id`),
  UNIQUE KEY `aut_org_staff_id` (`aut_org_staff_id`),
  UNIQUE KEY `aut_org_username` (`aut_org_username`),
  FULLTEXT KEY `aut_fname` (`aut_fname`,`aut_lname`),
  FULLTEXT KEY `aut_display_name` (`aut_display_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_author_affiliation`
--

DROP TABLE IF EXISTS `fez_author_affiliation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_author_affiliation` (
  `af_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `af_pid` varchar(32) NOT NULL,
  `af_author_id` int(11) NOT NULL,
  `af_percent_affiliation` int(11) NOT NULL,
  `af_org_id` int(11) NOT NULL,
  `af_status` int(1) DEFAULT '0',
  PRIMARY KEY (`af_id`),
  UNIQUE KEY `unique_constraint` (`af_pid`,`af_author_id`,`af_org_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_author_classification`
--

DROP TABLE IF EXISTS `fez_author_classification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_author_classification` (
  `cla_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cla_title` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`cla_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_author_function`
--

DROP TABLE IF EXISTS `fez_author_function`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_author_function` (
  `fun_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fun_title` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`fun_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_author_org_structure`
--

DROP TABLE IF EXISTS `fez_author_org_structure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_author_org_structure` (
  `auo_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `auo_org_id` int(11) unsigned DEFAULT NULL,
  `auo_aut_id` int(11) unsigned DEFAULT NULL,
  `auo_cla_id` int(11) unsigned DEFAULT NULL,
  `auo_fun_id` int(11) unsigned DEFAULT NULL,
  `auo_assessed` varchar(1) DEFAULT NULL,
  `auo_assessed_year` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`auo_id`),
  UNIQUE KEY `support_unique_key` (`auo_org_id`,`auo_aut_id`,`auo_cla_id`,`auo_fun_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_background_process`
--

DROP TABLE IF EXISTS `fez_background_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_background_process` (
  `bgp_id` int(11) NOT NULL AUTO_INCREMENT,
  `bgp_status_message` text,
  `bgp_progress` int(11) DEFAULT NULL,
  `bgp_usr_id` int(11) DEFAULT NULL,
  `bgp_state` int(11) DEFAULT NULL,
  `bgp_heartbeat` datetime DEFAULT NULL,
  `bgp_serialized` longtext,
  `bgp_include` varchar(255) DEFAULT NULL,
  `bgp_name` varchar(255) DEFAULT NULL,
  `bgp_started` datetime DEFAULT NULL,
  `bgp_filename` varchar(255) DEFAULT NULL,
  `bgp_headers` text,
  PRIMARY KEY (`bgp_id`),
  KEY `bgp_started` (`bgp_started`),
  KEY `bgp_state` (`bgp_state`),
  KEY `bgp_usr_id` (`bgp_usr_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_background_process_pids`
--

DROP TABLE IF EXISTS `fez_background_process_pids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_background_process_pids` (
  `bgpid_bgp_id` int(11) NOT NULL,
  `bgpid_pid` varchar(64) NOT NULL,
  PRIMARY KEY (`bgpid_bgp_id`,`bgpid_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_citation`
--

DROP TABLE IF EXISTS `fez_citation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_citation` (
  `cit_id` int(11) NOT NULL AUTO_INCREMENT,
  `cit_xdis_id` int(11) NOT NULL,
  `cit_template` text NOT NULL,
  `cit_type` varchar(10) NOT NULL,
  PRIMARY KEY (`cit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_cloud_tag`
--

DROP TABLE IF EXISTS `fez_cloud_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_cloud_tag` (
  `keyword` varchar(100) NOT NULL,
  `quantity` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_conference`
--

DROP TABLE IF EXISTS `fez_conference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_conference` (
  `cnf_id` int(11) NOT NULL AUTO_INCREMENT,
  `cnf_conference_name` varchar(255) NOT NULL,
  `cnf_acronym` varchar(50) DEFAULT NULL,
  `cnf_rank` varchar(1) DEFAULT NULL,
  `cnf_era_id` int(11) NOT NULL,
  `cnf_era_year` int(4) DEFAULT NULL,
  `cnf_created_date` date DEFAULT NULL,
  `cnf_updated_date` date DEFAULT NULL,
  `cnf_foreign_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cnf_id`),
  KEY `idx_cnf_conference_id` (`cnf_id`),
  KEY `idx_cnf_era_id` (`cnf_era_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_conference_for_codes`
--

DROP TABLE IF EXISTS `fez_conference_for_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_conference_for_codes` (
  `cfe_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cfe_cnf_id` int(11) NOT NULL,
  `cfe_for_code` varchar(6) NOT NULL,
  `cfe_number` int(11) NOT NULL,
  PRIMARY KEY (`cfe_id`),
  KEY `idx_cfe_id` (`cfe_id`),
  KEY `idx_cfe_era_id` (`cfe_cnf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_conference_id`
--

DROP TABLE IF EXISTS `fez_conference_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_conference_id` (
  `cfi_id` int(11) NOT NULL AUTO_INCREMENT,
  `cfi_conference_name` varchar(255) NOT NULL,
  `cfi_created_date` date DEFAULT NULL,
  `cfi_updated_date` date DEFAULT NULL,
  `cfi_details_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cfi_id`),
  KEY `idx_cfi_conference_id` (`cfi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_config`
--

DROP TABLE IF EXISTS `fez_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_config` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_name` varchar(100) DEFAULT NULL,
  `config_module` varchar(32) NOT NULL,
  `config_value` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `config_name` (`config_name`,`config_module`)
) ENGINE=MyISAM AUTO_INCREMENT=395 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_controlled_vocab`
--

DROP TABLE IF EXISTS `fez_controlled_vocab`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_controlled_vocab` (
  `cvo_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cvo_title` varchar(255) DEFAULT NULL,
  `cvo_desc` varchar(255) DEFAULT NULL,
  `cvo_image_filename` varchar(64) DEFAULT NULL,
  `cvo_external_id` varchar(50) DEFAULT NULL,
  `cvo_hide` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cvo_id`),
  UNIQUE KEY `cvo_id` (`cvo_id`),
  KEY `cvo_title` (`cvo_title`)
) ENGINE=MyISAM AUTO_INCREMENT=465586 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_controlled_vocab_relationship`
--

DROP TABLE IF EXISTS `fez_controlled_vocab_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_controlled_vocab_relationship` (
  `cvr_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cvr_parent_cvo_id` int(11) unsigned NOT NULL DEFAULT '0',
  `cvr_child_cvo_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cvr_id`),
  UNIQUE KEY `cvr_parent_cvo_id` (`cvr_parent_cvo_id`,`cvr_child_cvo_id`,`cvr_id`),
  KEY `ix_cvr_child_cvo_id` (`cvr_child_cvo_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16715 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_custom_views`
--

DROP TABLE IF EXISTS `fez_custom_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_custom_views` (
  `cview_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cview_name` varchar(100) DEFAULT NULL,
  `cview_folder` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cview_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_custom_views_community`
--

DROP TABLE IF EXISTS `fez_custom_views_community`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_custom_views_community` (
  `cvcom_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cvcom_cview_id` int(11) unsigned DEFAULT NULL,
  `cvcom_com_pid` varchar(64) DEFAULT NULL,
  `cvcom_hostname` varchar(255) DEFAULT NULL,
  `cvcom_default_template` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cvcom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_custom_views_search_keys`
--

DROP TABLE IF EXISTS `fez_custom_views_search_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_custom_views_search_keys` (
  `cvsk_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cvsk_cview_id` int(11) unsigned DEFAULT NULL,
  `cvsk_sek_id` varchar(64) DEFAULT NULL,
  `cvsk_sek_name` varchar(100) DEFAULT NULL,
  `cvsk_order` mediumint(9) DEFAULT NULL,
  `cvsk_sek_desc` text,
  PRIMARY KEY (`cvsk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_datastream_cache`
--

DROP TABLE IF EXISTS `fez_datastream_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_datastream_cache` (
  `dc_pid` varchar(64) NOT NULL,
  `dc_dsid` varchar(255) NOT NULL,
  PRIMARY KEY (`dc_pid`,`dc_dsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_digital_object`
--

DROP TABLE IF EXISTS `fez_digital_object`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_digital_object` (
  `pidns` varchar(5) NOT NULL,
  `pidint` int(11) NOT NULL,
  `xdis_id` int(11) NOT NULL,
  `sta_id` int(11) NOT NULL,
  `usr_id` int(11) NOT NULL,
  `grp_id` int(11) DEFAULT NULL,
  `copyright` enum('on','off') NOT NULL DEFAULT 'on',
  `depositor` int(11) DEFAULT NULL,
  `depositor_affiliation` int(11) DEFAULT NULL,
  `additional_notes` text,
  `refereed` enum('on','off') DEFAULT NULL,
  `herdc_status` int(11) DEFAULT NULL,
  `institutional_status` int(11) DEFAULT NULL,
  `follow_up` enum('on','off') NOT NULL DEFAULT 'off',
  `follow_up_imu` enum('on','off') NOT NULL DEFAULT 'off',
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pidns`,`pidint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_eprints_import_pids`
--

DROP TABLE IF EXISTS `fez_eprints_import_pids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_eprints_import_pids` (
  `epr_eprints_id` int(11) NOT NULL,
  `epr_fez_pid` varchar(255) NOT NULL,
  `epr_date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`epr_eprints_id`,`epr_fez_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_exif`
--

DROP TABLE IF EXISTS `fez_exif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_exif` (
  `exif_id` int(11) NOT NULL AUTO_INCREMENT,
  `exif_pid` varchar(64) NOT NULL,
  `exif_dsid` varchar(255) NOT NULL,
  `exif_file_size` varchar(64) DEFAULT NULL,
  `exif_file_size_human` varchar(64) DEFAULT NULL,
  `exif_image_width` int(11) DEFAULT NULL,
  `exif_image_height` int(11) DEFAULT NULL,
  `exif_mime_type` varchar(64) DEFAULT NULL,
  `exif_camera_model_name` varchar(255) DEFAULT NULL,
  `exif_make` varchar(255) DEFAULT NULL,
  `exif_create_date` datetime DEFAULT NULL,
  `exif_file_type` varchar(64) DEFAULT NULL,
  `exif_page_count` int(11) DEFAULT NULL,
  `exif_play_duration` varchar(64) DEFAULT NULL,
  `exif_all` text,
  PRIMARY KEY (`exif_id`),
  UNIQUE KEY `exif_idx` (`exif_pid`,`exif_dsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_faq_categories`
--

DROP TABLE IF EXISTS `fez_faq_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_faq_categories` (
  `faq_cat_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `faq_cat_name` varchar(255) NOT NULL,
  `faq_cat_order` int(11) NOT NULL,
  PRIMARY KEY (`faq_cat_id`),
  UNIQUE KEY `faq_cat_id` (`faq_cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_faq_questions`
--

DROP TABLE IF EXISTS `fez_faq_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_faq_questions` (
  `faq_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `faq_group` int(11) NOT NULL,
  `faq_question` varchar(255) NOT NULL,
  `faq_answer` text NOT NULL,
  `faq_order` int(11) NOT NULL,
  PRIMARY KEY (`faq_id`),
  UNIQUE KEY `faq_id` (`faq_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_favourites`
--

DROP TABLE IF EXISTS `fez_favourites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_favourites` (
  `fvt_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fvt_pid` varchar(64) NOT NULL,
  `fvt_username` varchar(64) NOT NULL,
  PRIMARY KEY (`fvt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_file_attachments`
--

DROP TABLE IF EXISTS `fez_file_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_file_attachments` (
  `fat_did` int(11) NOT NULL AUTO_INCREMENT,
  `fat_hash` varchar(50) NOT NULL,
  `fat_filename` varchar(200) NOT NULL,
  `fat_label` varchar(200) DEFAULT NULL,
  `fat_version` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fat_metaid` int(11) NOT NULL,
  `fat_state` enum('A','D') NOT NULL DEFAULT 'A',
  `fat_size` int(20) NOT NULL DEFAULT '0',
  `fat_pid` varchar(15) NOT NULL DEFAULT '0',
  `fat_mimetype` varchar(100) DEFAULT NULL,
  `fat_controlgroup` char(1) NOT NULL DEFAULT 'M',
  `fat_xdis_id` int(11) DEFAULT '5',
  `fat_copyright` char(1) DEFAULT NULL,
  `fat_watermark` char(1) DEFAULT NULL,
  `fat_security_inherited` char(1) DEFAULT NULL,
  PRIMARY KEY (`fat_did`),
  KEY `unique_pid_hash` (`fat_hash`,`fat_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_file_attachments_shadow`
--

DROP TABLE IF EXISTS `fez_file_attachments_shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_file_attachments_shadow` (
  `fat_did` int(11) NOT NULL AUTO_INCREMENT,
  `fat_hash` varchar(50) NOT NULL,
  `fat_filename` varchar(200) NOT NULL,
  `fat_label` varchar(200) DEFAULT NULL,
  `fat_version` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fat_metaid` int(11) NOT NULL,
  `fat_state` enum('A','D') NOT NULL DEFAULT 'A',
  `fat_size` int(20) NOT NULL DEFAULT '0',
  `fat_pid` varchar(15) NOT NULL DEFAULT '0',
  `fat_mimetype` varchar(100) DEFAULT NULL,
  `fat_controlgroup` char(1) NOT NULL DEFAULT 'M',
  `fat_xdis_id` int(11) DEFAULT '5',
  `fat_copyright` char(1) DEFAULT NULL,
  `fat_watermark` char(1) DEFAULT NULL,
  `fat_security_inherited` char(1) DEFAULT NULL,
  `fat_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`fat_did`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_fulltext_cache`
--

DROP TABLE IF EXISTS `fez_fulltext_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_fulltext_cache` (
  `ftc_id` int(11) NOT NULL AUTO_INCREMENT,
  `ftc_pid` varchar(64) DEFAULT NULL,
  `rek_file_attachment_content_xsdmf_id` int(11) DEFAULT NULL,
  `ftc_content` mediumtext,
  `ftc_dsid` varchar(64) NOT NULL DEFAULT '',
  `ftc_is_text_usable` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`ftc_id`),
  UNIQUE KEY `ftc_key` (`ftc_pid`,`ftc_dsid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_fulltext_engine`
--

DROP TABLE IF EXISTS `fez_fulltext_engine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_fulltext_engine` (
  `fte_id` int(11) NOT NULL AUTO_INCREMENT,
  `fte_fti_id` mediumint(9) NOT NULL DEFAULT '0',
  `fte_key_id` mediumint(9) NOT NULL DEFAULT '0',
  `fte_weight` smallint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fte_id`),
  KEY `key_id` (`fte_key_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_fulltext_index`
--

DROP TABLE IF EXISTS `fez_fulltext_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_fulltext_index` (
  `fti_id` int(11) NOT NULL AUTO_INCREMENT,
  `fti_pid` varchar(64) NOT NULL,
  `fti_dsid` varchar(128) NOT NULL,
  `fti_indexed` datetime NOT NULL,
  PRIMARY KEY (`fti_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_fulltext_keywords`
--

DROP TABLE IF EXISTS `fez_fulltext_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_fulltext_keywords` (
  `ftk_id` int(11) NOT NULL AUTO_INCREMENT,
  `ftk_twoletters` char(2) NOT NULL,
  `ftk_word` varchar(64) NOT NULL,
  PRIMARY KEY (`ftk_id`),
  UNIQUE KEY `ftk_word` (`ftk_word`),
  KEY `ftk_twoletters` (`ftk_twoletters`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_fulltext_locks`
--

DROP TABLE IF EXISTS `fez_fulltext_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_fulltext_locks` (
  `ftl_name` varchar(8) NOT NULL,
  `ftl_value` int(10) unsigned NOT NULL,
  `ftl_pid` int(11) DEFAULT NULL,
  PRIMARY KEY (`ftl_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_fulltext_queue`
--

DROP TABLE IF EXISTS `fez_fulltext_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_fulltext_queue` (
  `ftq_key` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ftq_pid` varchar(128) NOT NULL DEFAULT '',
  `ftq_op` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`ftq_key`),
  UNIQUE KEY `pid_op` (`ftq_pid`,`ftq_op`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_geocode_cities`
--

DROP TABLE IF EXISTS `fez_geocode_cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_geocode_cities` (
  `gcity_country_code` char(2) NOT NULL,
  `gcity_region_code` char(2) NOT NULL,
  `gcity_location_name` varchar(100) NOT NULL,
  `gcity_city` varchar(100) NOT NULL,
  `gcity_latitude` double DEFAULT NULL,
  `gcity_longitude` double DEFAULT NULL,
  PRIMARY KEY (`gcity_country_code`,`gcity_region_code`,`gcity_location_name`,`gcity_city`),
  KEY `cities_lat_lng_idx` (`gcity_latitude`,`gcity_longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_geocode_country`
--

DROP TABLE IF EXISTS `fez_geocode_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_geocode_country` (
  `gctry_country_code` char(2) NOT NULL,
  `gctry_latitude` double DEFAULT NULL,
  `gctry_longitude` double DEFAULT NULL,
  PRIMARY KEY (`gctry_country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_geocode_location_cache`
--

DROP TABLE IF EXISTS `fez_geocode_location_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_geocode_location_cache` (
  `loc_location` varchar(255) NOT NULL,
  `loc_latitude` double DEFAULT NULL,
  `loc_longitude` double DEFAULT NULL,
  `loc_accuracy` int(2) DEFAULT NULL,
  PRIMARY KEY (`loc_location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_geocode_regions`
--

DROP TABLE IF EXISTS `fez_geocode_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_geocode_regions` (
  `gcr_country_code` char(2) NOT NULL,
  `gcr_region_code` char(2) NOT NULL,
  `gcr_location_name` varchar(100) NOT NULL,
  `gcr_latitude` double DEFAULT NULL,
  `gcr_longitude` double DEFAULT NULL,
  PRIMARY KEY (`gcr_country_code`,`gcr_region_code`),
  KEY `region_lat_lng_idx` (`gcr_latitude`,`gcr_longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_google_scholar_citations`
--

DROP TABLE IF EXISTS `fez_google_scholar_citations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_google_scholar_citations` (
  `gs_pid` varchar(64) NOT NULL,
  `gs_last_checked` int(10) unsigned NOT NULL,
  `gs_count` int(10) unsigned NOT NULL,
  `gs_link` text,
  `gs_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gs_created` int(11) unsigned NOT NULL,
  PRIMARY KEY (`gs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_group`
--

DROP TABLE IF EXISTS `fez_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_group` (
  `grp_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `grp_title` varchar(30) DEFAULT NULL,
  `grp_status` set('active','archived') NOT NULL DEFAULT 'active',
  `grp_created_date` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`grp_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_group_user`
--

DROP TABLE IF EXISTS `fez_group_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_group_user` (
  `gpu_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpu_grp_id` int(11) unsigned NOT NULL DEFAULT '0',
  `gpu_usr_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`gpu_id`),
  KEY `pru_col_id` (`gpu_grp_id`,`gpu_usr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_input_filter`
--

DROP TABLE IF EXISTS `fez_input_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_input_filter` (
  `ift_id` int(11) NOT NULL AUTO_INCREMENT,
  `ift_input_name` varchar(45) NOT NULL,
  `ift_filter_class` varchar(45) NOT NULL,
  PRIMARY KEY (`ift_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_integrity_index_ghosts`
--

DROP TABLE IF EXISTS `fez_integrity_index_ghosts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_integrity_index_ghosts` (
  `pid` varchar(64) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_integrity_pid_auth_ghosts`
--

DROP TABLE IF EXISTS `fez_integrity_pid_auth_ghosts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_integrity_pid_auth_ghosts` (
  `pid` varchar(64) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_integrity_solr_ghosts`
--

DROP TABLE IF EXISTS `fez_integrity_solr_ghosts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_integrity_solr_ghosts` (
  `pid` varchar(64) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_integrity_solr_unspawned`
--

DROP TABLE IF EXISTS `fez_integrity_solr_unspawned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_integrity_solr_unspawned` (
  `pid` varchar(64) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_integrity_solr_unspawned_citations`
--

DROP TABLE IF EXISTS `fez_integrity_solr_unspawned_citations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_integrity_solr_unspawned_citations` (
  `pid` varchar(64) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_internal_notes`
--

DROP TABLE IF EXISTS `fez_internal_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_internal_notes` (
  `ain_id` int(11) NOT NULL AUTO_INCREMENT,
  `ain_pid` varchar(64) NOT NULL,
  `ain_detail` text,
  PRIMARY KEY (`ain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_journal`
--

DROP TABLE IF EXISTS `fez_journal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_journal` (
  `jnl_id` int(11) NOT NULL AUTO_INCREMENT,
  `jnl_journal_name` varchar(255) NOT NULL,
  `jnl_era_id` int(11) NOT NULL,
  `jnl_era_year` int(4) DEFAULT NULL,
  `jnl_created_date` date DEFAULT NULL,
  `jnl_updated_date` date DEFAULT NULL,
  `jnl_rank` varchar(2) DEFAULT NULL,
  `jnl_foreign_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`jnl_id`),
  KEY `idx_jnl_journal_id` (`jnl_id`),
  KEY `idx_jnl_era_id` (`jnl_era_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_journal_for_codes`
--

DROP TABLE IF EXISTS `fez_journal_for_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_journal_for_codes` (
  `jne_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `jne_jnl_id` int(11) NOT NULL,
  `jne_for_code` varchar(6) NOT NULL,
  `jne_number` int(11) NOT NULL,
  PRIMARY KEY (`jne_id`),
  KEY `idx_jne_id` (`jne_id`),
  KEY `idx_jne_era_id` (`jne_jnl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_journal_issns`
--

DROP TABLE IF EXISTS `fez_journal_issns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_journal_issns` (
  `jni_id` int(11) NOT NULL AUTO_INCREMENT,
  `jni_jnl_id` int(11) NOT NULL,
  `jni_issn` varchar(50) DEFAULT NULL,
  `jni_issn_order` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`jni_id`),
  KEY `idx_jnl_journal_issn_id` (`jni_id`),
  KEY `idx_jnl_journal_id` (`jni_jnl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_language`
--

DROP TABLE IF EXISTS `fez_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_language` (
  `lng_alpha3_bibliographic` char(3) NOT NULL,
  `lng_alpha3_terminologic` char(3) DEFAULT NULL,
  `lng_alpha2` varchar(2) DEFAULT NULL,
  `lng_english_name` varchar(255) NOT NULL,
  `lng_french_name` varchar(255) DEFAULT NULL,
  `lng_ascl_code` varchar(4) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_link_status_reports`
--

DROP TABLE IF EXISTS `fez_link_status_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_link_status_reports` (
  `lsr_url` varchar(255) NOT NULL,
  `lsr_status` varchar(3) NOT NULL,
  `lsr_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`lsr_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_linksamr_locks`
--

DROP TABLE IF EXISTS `fez_linksamr_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_linksamr_locks` (
  `lnl_name` varchar(8) NOT NULL,
  `lnl_value` int(10) unsigned NOT NULL,
  `lnl_pid` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`lnl_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_linksamr_queue`
--

DROP TABLE IF EXISTS `fez_linksamr_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_linksamr_queue` (
  `lnq_key` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lnq_id` varchar(128) NOT NULL DEFAULT '',
  `lnq_op` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`lnq_key`),
  UNIQUE KEY `id_op` (`lnq_id`,`lnq_op`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_mail_queue`
--

DROP TABLE IF EXISTS `fez_mail_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_mail_queue` (
  `maq_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `maq_queued_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `maq_status` varchar(8) NOT NULL DEFAULT 'pending',
  `maq_save_copy` tinyint(1) NOT NULL DEFAULT '1',
  `maq_sender_ip_address` varchar(15) NOT NULL DEFAULT '',
  `maq_recipient` varchar(255) NOT NULL DEFAULT '',
  `maq_headers` text NOT NULL,
  `maq_body` longtext NOT NULL,
  PRIMARY KEY (`maq_id`),
  KEY `maq_status` (`maq_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_mail_queue_log`
--

DROP TABLE IF EXISTS `fez_mail_queue_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_mail_queue_log` (
  `mql_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mql_maq_id` int(11) unsigned NOT NULL DEFAULT '0',
  `mql_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mql_status` varchar(8) NOT NULL DEFAULT 'error',
  `mql_server_message` text,
  PRIMARY KEY (`mql_id`),
  KEY `mql_maq_id` (`mql_maq_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_main_chapter`
--

DROP TABLE IF EXISTS `fez_main_chapter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_main_chapter` (
  `mc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mc_pid` varchar(32) NOT NULL,
  `mc_author_id` int(11) NOT NULL,
  `mc_status` int(1) DEFAULT '0',
  PRIMARY KEY (`mc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_matched_conferences`
--

DROP TABLE IF EXISTS `fez_matched_conferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_matched_conferences` (
  `mtc_pid` varchar(64) NOT NULL,
  `mtc_cnf_id` int(11) NOT NULL,
  `mtc_status` varchar(1) NOT NULL,
  PRIMARY KEY (`mtc_pid`,`mtc_cnf_id`),
  KEY `idx_mtc_pid` (`mtc_pid`),
  KEY `idx_mtc_eraid` (`mtc_cnf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_matched_journals`
--

DROP TABLE IF EXISTS `fez_matched_journals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_matched_journals` (
  `mtj_pid` varchar(64) NOT NULL,
  `mtj_jnl_id` int(11) NOT NULL,
  `mtj_status` varchar(1) NOT NULL,
  PRIMARY KEY (`mtj_pid`,`mtj_jnl_id`),
  KEY `idx_mtj_pid` (`mtj_pid`),
  KEY `idx_mtj_eraid` (`mtj_jnl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_my_research_claimed_flagged`
--

DROP TABLE IF EXISTS `fez_my_research_claimed_flagged`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_my_research_claimed_flagged` (
  `mrc_id` int(11) NOT NULL AUTO_INCREMENT,
  `mrc_pid` varchar(64) NOT NULL,
  `mrc_author_username` varchar(255) NOT NULL,
  `mrc_timestamp` datetime NOT NULL,
  `mrc_correction` text,
  `mrc_type` varchar(1) NOT NULL,
  `mrc_user_username` varchar(255) NOT NULL,
  PRIMARY KEY (`mrc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_my_research_possible_flagged`
--

DROP TABLE IF EXISTS `fez_my_research_possible_flagged`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_my_research_possible_flagged` (
  `mrp_id` int(11) NOT NULL AUTO_INCREMENT,
  `mrp_pid` varchar(64) NOT NULL,
  `mrp_author_username` varchar(255) NOT NULL,
  `mrp_timestamp` datetime NOT NULL,
  `mrp_correction` text,
  `mrp_type` varchar(1) NOT NULL,
  `mrp_user_username` varchar(255) NOT NULL,
  PRIMARY KEY (`mrp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_near_matched_journals`
--

DROP TABLE IF EXISTS `fez_near_matched_journals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_near_matched_journals` (
  `nmj_id` int(11) NOT NULL AUTO_INCREMENT,
  `nmj_pid` varchar(64) NOT NULL,
  `nmj_jnl_id` int(11) NOT NULL,
  `nmj_jnl_journal_name` varchar(255) NOT NULL,
  `nmj_rek_journal_name` varchar(255) NOT NULL,
  `nmj_similarity` decimal(13,2) NOT NULL,
  `nmj_created_date` datetime NOT NULL,
  PRIMARY KEY (`nmj_id`),
  KEY `nmj_pid` (`nmj_pid`),
  KEY `nmj_similarity` (`nmj_similarity`),
  KEY `nmj_jnl_id` (`nmj_jnl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_news`
--

DROP TABLE IF EXISTS `fez_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_news` (
  `nws_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nws_usr_id` int(11) unsigned NOT NULL DEFAULT '0',
  `nws_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `nws_title` varchar(255) NOT NULL DEFAULT '',
  `nws_message` text NOT NULL,
  `nws_status` varchar(8) NOT NULL DEFAULT 'active',
  `nws_published_date` datetime DEFAULT '0000-00-00 00:00:00',
  `nws_updated_date` datetime DEFAULT '0000-00-00 00:00:00',
  `nws_admin_only` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`nws_id`),
  UNIQUE KEY `nws_title` (`nws_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_object_type`
--

DROP TABLE IF EXISTS `fez_object_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_object_type` (
  `ret_id` int(11) unsigned NOT NULL DEFAULT '0',
  `ret_title` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`ret_id`),
  UNIQUE KEY `htt_name` (`ret_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_org_structure`
--

DROP TABLE IF EXISTS `fez_org_structure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_org_structure` (
  `org_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `org_extdb_name` varchar(20) DEFAULT NULL,
  `org_extdb_id` int(11) DEFAULT NULL,
  `org_ext_table` varchar(100) DEFAULT NULL,
  `org_title` varchar(255) DEFAULT NULL,
  `org_is_current` int(1) DEFAULT '1',
  `org_desc` text,
  `org_image_filename` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`org_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_org_structure_relationship`
--

DROP TABLE IF EXISTS `fez_org_structure_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_org_structure_relationship` (
  `orr_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `orr_parent_org_id` int(11) DEFAULT NULL,
  `orr_child_org_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`orr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_pages`
--

DROP TABLE IF EXISTS `fez_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_pages` (
  `pge_id` varchar(20) NOT NULL,
  `pge_title` varchar(255) NOT NULL,
  `pge_content` text NOT NULL,
  PRIMARY KEY (`pge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_pid_index`
--

DROP TABLE IF EXISTS `fez_pid_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_pid_index` (
  `pid_number` int(10) unsigned NOT NULL,
  PRIMARY KEY (`pid_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_premis_event`
--

DROP TABLE IF EXISTS `fez_premis_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_premis_event` (
  `pre_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pre_wfl_id` int(11) DEFAULT NULL,
  `pre_date` datetime DEFAULT NULL,
  `pre_detail` text,
  `pre_outcome` varchar(50) DEFAULT NULL,
  `pre_outcomedetail` text,
  `pre_usr_id` int(11) DEFAULT NULL,
  `pre_pid` varchar(255) DEFAULT NULL,
  `pre_is_hidden` tinyint(1) DEFAULT '0',
  `pre_msq_usr_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`pre_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_publisher`
--

DROP TABLE IF EXISTS `fez_publisher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_publisher` (
  `pub_id` int(11) NOT NULL AUTO_INCREMENT,
  `pub_name` varchar(255) DEFAULT NULL,
  `pub_created_date` datetime DEFAULT NULL,
  `pub_updated_date` datetime DEFAULT NULL,
  `pub_details_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`pub_id`),
  UNIQUE KEY `pud_id` (`pub_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_recently_added_items`
--

DROP TABLE IF EXISTS `fez_recently_added_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_recently_added_items` (
  `rai_pid` varchar(64) NOT NULL,
  PRIMARY KEY (`rai_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_recently_downloaded_items`
--

DROP TABLE IF EXISTS `fez_recently_downloaded_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_recently_downloaded_items` (
  `rdi_pid` varchar(64) NOT NULL,
  `rdi_downloads` int(11) DEFAULT NULL,
  PRIMARY KEY (`rdi_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_locks`
--

DROP TABLE IF EXISTS `fez_record_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_locks` (
  `rl_id` int(11) NOT NULL AUTO_INCREMENT,
  `rl_pid` varchar(64) NOT NULL,
  `rl_usr_id` int(11) NOT NULL,
  `rl_context_type` int(11) NOT NULL,
  `rl_context_value` int(11) NOT NULL,
  PRIMARY KEY (`rl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_matching_field`
--

DROP TABLE IF EXISTS `fez_record_matching_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_matching_field` (
  `rmf_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rmf_rec_pid_num` int(11) NOT NULL,
  `rmf_rec_pid` varchar(64) NOT NULL DEFAULT '',
  `rmf_dsid` varchar(255) DEFAULT NULL,
  `rmf_xsdmf_id` int(11) unsigned DEFAULT NULL,
  `rmf_varchar` varchar(255) DEFAULT NULL,
  `rmf_date` datetime DEFAULT NULL,
  `rmf_int` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`rmf_id`),
  KEY `rmf_xsdmf_id` (`rmf_xsdmf_id`),
  KEY `rmf_date` (`rmf_date`),
  KEY `rmf_rec_pid_num` (`rmf_rec_pid_num`),
  KEY `rmf_int` (`rmf_int`),
  KEY `rmf_rec_pid` (`rmf_rec_pid`),
  KEY `combo_pid_xsdmf` (`rmf_rec_pid`,`rmf_xsdmf_id`),
  KEY `combo_pid_num_xsdmf` (`rmf_rec_pid_num`,`rmf_xsdmf_id`),
  FULLTEXT KEY `rmf_varchar` (`rmf_varchar`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key`
--

DROP TABLE IF EXISTS `fez_record_search_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key` (
  `rek_pid` varchar(64) NOT NULL COMMENT 'PID',
  `rek_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_title` varchar(1000) DEFAULT NULL COMMENT 'Title',
  `rek_description_xsdmf_id` int(11) DEFAULT NULL,
  `rek_description` text COMMENT 'Description',
  `rek_display_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_display_type` int(11) DEFAULT NULL COMMENT 'Display Type',
  `rek_status_xsdmf_id` int(11) DEFAULT NULL,
  `rek_status` int(11) DEFAULT NULL COMMENT 'Status',
  `rek_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_date` datetime DEFAULT NULL COMMENT 'Date',
  `rek_object_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_object_type` int(11) DEFAULT NULL COMMENT 'Object Type',
  `rek_depositor_xsdmf_id` int(11) DEFAULT NULL,
  `rek_depositor` int(11) DEFAULT NULL COMMENT 'Depositor',
  `rek_created_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_created_date` datetime DEFAULT NULL COMMENT 'Created Date',
  `rek_updated_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_updated_date` datetime DEFAULT NULL COMMENT 'Updated Date',
  `rek_file_downloads` int(11) DEFAULT '0',
  `rek_views` int(11) DEFAULT '0',
  `rek_citation` text,
  `rek_sequence` int(11) DEFAULT '0' COMMENT 'Sequence order in a parent object',
  `rek_sequence_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre` varchar(255) DEFAULT NULL COMMENT 'Genre',
  `rek_genre_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre_type` varchar(255) DEFAULT NULL COMMENT 'Genre Type',
  `rek_formatted_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_formatted_title` text COMMENT 'Formatted Title',
  `rek_formatted_abstract_xsdmf_id` int(11) DEFAULT NULL,
  `rek_formatted_abstract` text COMMENT 'Formatted Abstract',
  `rek_depositor_affiliation_xsdmf_id` int(11) DEFAULT NULL,
  `rek_depositor_affiliation` int(11) DEFAULT NULL,
  `rek_thomson_citation_count` int(11) DEFAULT NULL,
  `rek_gs_citation_count` int(4) DEFAULT NULL,
  `rek_gs_cited_by_link` text,
  `rek_thomson_citation_count_xsdmf_id` int(11) DEFAULT NULL,
  `rek_subtype_xsdmf_id` int(11) DEFAULT NULL,
  `rek_subtype` varchar(255) DEFAULT NULL,
  `rek_scopus_citation_count` int(11) DEFAULT NULL,
  `rek_herdc_notes_xsdmf_id` int(11) DEFAULT NULL,
  `rek_herdc_notes` text,
  `rek_scopus_doc_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_scopus_doc_type` varchar(255) DEFAULT NULL,
  `rek_wok_doc_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_wok_doc_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_pid`),
  KEY `rek_display_type` (`rek_display_type`),
  KEY `rek_status` (`rek_status`),
  KEY `rek_date` (`rek_date`),
  KEY `rek_object_type` (`rek_object_type`),
  KEY `rek_depositor` (`rek_depositor`),
  KEY `rek_created_date` (`rek_created_date`),
  KEY `rek_updated_date` (`rek_updated_date`),
  KEY `rek_title` (`rek_title`(255)),
  KEY `rek_views` (`rek_views`),
  KEY `rek_file_downloads` (`rek_file_downloads`),
  KEY `rek_sequence` (`rek_sequence`),
  KEY `rek_genre` (`rek_genre`),
  KEY `rek_genre_type` (`rek_genre_type`),
  KEY `rek_subtype` (`rek_subtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key__shadow` (
  `rek_pid` varchar(64) NOT NULL COMMENT 'PID',
  `rek_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_title` varchar(1000) DEFAULT NULL COMMENT 'Title',
  `rek_description_xsdmf_id` int(11) DEFAULT NULL,
  `rek_description` text COMMENT 'Description',
  `rek_display_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_display_type` int(11) DEFAULT NULL COMMENT 'Display Type',
  `rek_status_xsdmf_id` int(11) DEFAULT NULL,
  `rek_status` int(11) DEFAULT NULL COMMENT 'Status',
  `rek_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_date` datetime DEFAULT NULL COMMENT 'Date',
  `rek_object_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_object_type` int(11) DEFAULT NULL COMMENT 'Object Type',
  `rek_depositor_xsdmf_id` int(11) DEFAULT NULL,
  `rek_depositor` int(11) DEFAULT NULL COMMENT 'Depositor',
  `rek_created_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_created_date` datetime DEFAULT NULL COMMENT 'Created Date',
  `rek_updated_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_updated_date` datetime DEFAULT NULL COMMENT 'Updated Date',
  `rek_file_downloads` int(11) DEFAULT '0',
  `rek_views` int(11) DEFAULT '0',
  `rek_citation` text,
  `rek_sequence` int(11) DEFAULT '0' COMMENT 'Sequence order in a parent object',
  `rek_sequence_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre` varchar(255) DEFAULT NULL COMMENT 'Genre',
  `rek_genre_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre_type` varchar(255) DEFAULT NULL COMMENT 'Genre Type',
  `rek_formatted_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_formatted_title` text COMMENT 'Formatted Title',
  `rek_formatted_abstract_xsdmf_id` int(11) DEFAULT NULL,
  `rek_formatted_abstract` text COMMENT 'Formatted Abstract',
  `rek_depositor_affiliation_xsdmf_id` int(11) DEFAULT NULL,
  `rek_depositor_affiliation` int(11) DEFAULT NULL,
  `rek_thomson_citation_count` int(11) DEFAULT NULL,
  `rek_gs_citation_count` int(4) DEFAULT NULL,
  `rek_gs_cited_by_link` text,
  `rek_thomson_citation_count_xsdmf_id` int(11) DEFAULT NULL,
  `rek_subtype_xsdmf_id` int(11) DEFAULT NULL,
  `rek_subtype` varchar(255) DEFAULT NULL,
  `rek_scopus_citation_count` int(11) DEFAULT NULL,
  `rek_herdc_notes_xsdmf_id` int(11) DEFAULT NULL,
  `rek_herdc_notes` text,
  `rek_scopus_doc_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_scopus_doc_type` varchar(255) DEFAULT NULL,
  `rek_wok_doc_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_wok_doc_type` varchar(255) DEFAULT NULL,
  `rek_stamp` datetime DEFAULT NULL,
  KEY `rek_display_type` (`rek_display_type`),
  KEY `rek_status` (`rek_status`),
  KEY `rek_date` (`rek_date`),
  KEY `rek_object_type` (`rek_object_type`),
  KEY `rek_depositor` (`rek_depositor`),
  KEY `rek_created_date` (`rek_created_date`),
  KEY `rek_updated_date` (`rek_updated_date`),
  KEY `rek_title` (`rek_title`(255)),
  KEY `rek_views` (`rek_views`),
  KEY `rek_file_downloads` (`rek_file_downloads`),
  KEY `rek_sequence` (`rek_sequence`),
  KEY `rek_genre` (`rek_genre`),
  KEY `rek_genre_type` (`rek_genre_type`),
  KEY `rek_subtype` (`rek_subtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_access_conditions`
--

DROP TABLE IF EXISTS `fez_record_search_key_access_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_access_conditions` (
  `rek_access_conditions_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_access_conditions_pid` varchar(64) DEFAULT NULL,
  `rek_access_conditions_xsdmf_id` int(11) DEFAULT NULL,
  `rek_access_conditions` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_access_conditions_id`),
  KEY `rek_access_conditions` (`rek_access_conditions`),
  KEY `rek_access_conditions_pid` (`rek_access_conditions_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_access_conditions__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_access_conditions__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_access_conditions__shadow` (
  `rek_access_conditions_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_access_conditions_pid` varchar(64) DEFAULT NULL,
  `rek_access_conditions_xsdmf_id` int(11) DEFAULT NULL,
  `rek_access_conditions` varchar(255) DEFAULT NULL,
  `rek_access_conditions_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_access_conditions_id`),
  UNIQUE KEY `rek_access_conditions_pid_2` (`rek_access_conditions_pid`,`rek_access_conditions_stamp`),
  KEY `rek_access_conditions` (`rek_access_conditions`),
  KEY `rek_access_conditions_pid` (`rek_access_conditions_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_adt_id`
--

DROP TABLE IF EXISTS `fez_record_search_key_adt_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_adt_id` (
  `rek_adt_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_adt_id_pid` varchar(64) DEFAULT NULL,
  `rek_adt_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_adt_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_adt_id_id`),
  UNIQUE KEY `unique_constraint` (`rek_adt_id_pid`,`rek_adt_id`),
  UNIQUE KEY `rek_adt_id_pid` (`rek_adt_id_pid`),
  KEY `rek_adt_id` (`rek_adt_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12183 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_adt_id__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_adt_id__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_adt_id__shadow` (
  `rek_adt_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_adt_id_pid` varchar(64) DEFAULT NULL,
  `rek_adt_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_adt_id` varchar(255) DEFAULT NULL,
  `rek_adt_id_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_adt_id_id`),
  UNIQUE KEY `rek_adt_id_pid` (`rek_adt_id_pid`),
  UNIQUE KEY `rek_adt_id_pid_2` (`rek_adt_id_pid`,`rek_adt_id_stamp`),
  KEY `rek_adt_id` (`rek_adt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_alternative_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_alternative_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_alternative_title` (
  `rek_alternative_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_alternative_title_pid` varchar(64) DEFAULT NULL,
  `rek_alternative_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_alternative_title` varchar(255) DEFAULT NULL,
  `rek_alternative_title_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_alternative_title_id`),
  UNIQUE KEY `unique_constraint` (`rek_alternative_title_pid`,`rek_alternative_title`,`rek_alternative_title_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_alternative_title_pid`,`rek_alternative_title_order`),
  KEY `rek_alternative_title` (`rek_alternative_title`),
  KEY `rek_alternative_title_pid` (`rek_alternative_title_pid`),
  KEY `rek_alternative_title_order` (`rek_alternative_title_order`)
) ENGINE=InnoDB AUTO_INCREMENT=2225 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_alternative_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_alternative_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_alternative_title__shadow` (
  `rek_alternative_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_alternative_title_pid` varchar(64) DEFAULT NULL,
  `rek_alternative_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_alternative_title` varchar(255) DEFAULT NULL,
  `rek_alternative_title_order` int(11) DEFAULT '1',
  `rek_alternative_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_alternative_title_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_alternative_title_pid`,`rek_alternative_title_order`),
  UNIQUE KEY `rek_alternative_title_pid_2` (`rek_alternative_title_pid`,`rek_alternative_title_stamp`),
  KEY `rek_alternative_title` (`rek_alternative_title`),
  KEY `rek_alternative_title_pid` (`rek_alternative_title_pid`),
  KEY `rek_alternative_title_order` (`rek_alternative_title_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_ands_collection_type`
--

DROP TABLE IF EXISTS `fez_record_search_key_ands_collection_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_ands_collection_type` (
  `rek_ands_collection_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_ands_collection_type_pid` varchar(64) DEFAULT NULL,
  `rek_ands_collection_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_ands_collection_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_ands_collection_type_id`),
  KEY `rek_ands_collection_type` (`rek_ands_collection_type`),
  KEY `rek_ands_collection_type_pid` (`rek_ands_collection_type_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_ands_collection_type__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_ands_collection_type__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_ands_collection_type__shadow` (
  `rek_ands_collection_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_ands_collection_type_pid` varchar(64) DEFAULT NULL,
  `rek_ands_collection_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_ands_collection_type` varchar(255) DEFAULT NULL,
  `rek_ands_collection_type_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_ands_collection_type_id`),
  UNIQUE KEY `rek_ands_collection_type_pid_2` (`rek_ands_collection_type_pid`,`rek_ands_collection_type_stamp`),
  KEY `rek_ands_collection_type` (`rek_ands_collection_type`),
  KEY `rek_ands_collection_type_pid` (`rek_ands_collection_type_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_architectural_features`
--

DROP TABLE IF EXISTS `fez_record_search_key_architectural_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_architectural_features` (
  `rek_architectural_features_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_architectural_features_pid` varchar(64) DEFAULT NULL,
  `rek_architectural_features_xsdmf_id` int(11) DEFAULT NULL,
  `rek_architectural_features_order` int(11) DEFAULT '1',
  `rek_architectural_features` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_architectural_features_id`),
  UNIQUE KEY `unique_constraint` (`rek_architectural_features_pid`,`rek_architectural_features_order`,`rek_architectural_features`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_architectural_features_pid`,`rek_architectural_features_order`),
  KEY `rek_architectural_features_pid` (`rek_architectural_features_pid`),
  FULLTEXT KEY `fulltext` (`rek_architectural_features`)
) ENGINE=MyISAM AUTO_INCREMENT=42661 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_architectural_features__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_architectural_features__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_architectural_features__shadow` (
  `rek_architectural_features_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_architectural_features_pid` varchar(64) DEFAULT NULL,
  `rek_architectural_features_xsdmf_id` int(11) DEFAULT NULL,
  `rek_architectural_features_order` int(11) DEFAULT '1',
  `rek_architectural_features` varchar(255) DEFAULT NULL,
  `rek_architectural_features_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_architectural_features_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_architectural_features_pid`,`rek_architectural_features_order`),
  UNIQUE KEY `rek_architectural_features_pid_2` (`rek_architectural_features_pid`,`rek_architectural_features_stamp`),
  KEY `rek_architectural_features_pid` (`rek_architectural_features_pid`),
  FULLTEXT KEY `fulltext` (`rek_architectural_features`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_assigned_group_id`
--

DROP TABLE IF EXISTS `fez_record_search_key_assigned_group_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_assigned_group_id` (
  `rek_assigned_group_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_assigned_group_id_pid` varchar(64) DEFAULT NULL,
  `rek_assigned_group_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_assigned_group_id` int(11) DEFAULT NULL,
  `rek_assigned_group_id_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_assigned_group_id_id`),
  UNIQUE KEY `unique_constraint` (`rek_assigned_group_id_pid`,`rek_assigned_group_id`,`rek_assigned_group_id_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_assigned_group_id_pid`,`rek_assigned_group_id_order`),
  KEY `rek_assigned_group_id_pid` (`rek_assigned_group_id_pid`),
  KEY `rek_assigned_group_id` (`rek_assigned_group_id`),
  KEY `rek_assigned_group_id_order` (`rek_assigned_group_id_order`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_assigned_group_id__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_assigned_group_id__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_assigned_group_id__shadow` (
  `rek_assigned_group_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_assigned_group_id_pid` varchar(64) DEFAULT NULL,
  `rek_assigned_group_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_assigned_group_id` int(11) DEFAULT NULL,
  `rek_assigned_group_id_order` int(11) DEFAULT '1',
  `rek_assigned_group_id_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_assigned_group_id_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_assigned_group_id_pid`,`rek_assigned_group_id_order`),
  UNIQUE KEY `rek_assigned_group_id_pid_2` (`rek_assigned_group_id_pid`,`rek_assigned_group_id_stamp`),
  KEY `rek_assigned_group_id_pid` (`rek_assigned_group_id_pid`),
  KEY `rek_assigned_group_id` (`rek_assigned_group_id`),
  KEY `rek_assigned_group_id_order` (`rek_assigned_group_id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_assigned_user_id`
--

DROP TABLE IF EXISTS `fez_record_search_key_assigned_user_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_assigned_user_id` (
  `rek_assigned_user_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_assigned_user_id_pid` varchar(64) DEFAULT NULL,
  `rek_assigned_user_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_assigned_user_id` int(11) DEFAULT NULL,
  `rek_assigned_user_id_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_assigned_user_id_id`),
  UNIQUE KEY `unique_constraint` (`rek_assigned_user_id_pid`,`rek_assigned_user_id`,`rek_assigned_user_id_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_assigned_user_id_pid`,`rek_assigned_user_id_order`),
  KEY `rek_assigned_user_id_pid` (`rek_assigned_user_id_pid`),
  KEY `rek_assigned_user_id` (`rek_assigned_user_id`),
  KEY `rek_assigned_user_id_order` (`rek_assigned_user_id_order`)
) ENGINE=InnoDB AUTO_INCREMENT=480555 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_assigned_user_id__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_assigned_user_id__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_assigned_user_id__shadow` (
  `rek_assigned_user_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_assigned_user_id_pid` varchar(64) DEFAULT NULL,
  `rek_assigned_user_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_assigned_user_id` int(11) DEFAULT NULL,
  `rek_assigned_user_id_order` int(11) DEFAULT '1',
  `rek_assigned_user_id_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_assigned_user_id_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_assigned_user_id_pid`,`rek_assigned_user_id_order`),
  UNIQUE KEY `rek_assigned_user_id_pid_2` (`rek_assigned_user_id_pid`,`rek_assigned_user_id_stamp`),
  KEY `rek_assigned_user_id_pid` (`rek_assigned_user_id_pid`),
  KEY `rek_assigned_user_id` (`rek_assigned_user_id`),
  KEY `rek_assigned_user_id_order` (`rek_assigned_user_id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_author`
--

DROP TABLE IF EXISTS `fez_record_search_key_author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_author` (
  `rek_author_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_pid` varchar(64) DEFAULT NULL,
  `rek_author_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author` varchar(255) DEFAULT NULL,
  `rek_author_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_author_id`),
  UNIQUE KEY `unique_constraint` (`rek_author_pid`,`rek_author`,`rek_author_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_author_pid`,`rek_author_order`),
  KEY `rek_author_pid` (`rek_author_pid`),
  KEY `rek_author` (`rek_author`),
  KEY `rek_author_order` (`rek_author_order`)
) ENGINE=InnoDB AUTO_INCREMENT=17812015 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_author__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_author__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_author__shadow` (
  `rek_author_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_pid` varchar(64) DEFAULT NULL,
  `rek_author_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author` varchar(255) DEFAULT NULL,
  `rek_author_order` int(11) DEFAULT '1',
  `rek_author_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_author_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_author_pid`,`rek_author_order`),
  UNIQUE KEY `rek_author_pid_2` (`rek_author_pid`,`rek_author_stamp`),
  KEY `rek_author_pid` (`rek_author_pid`),
  KEY `rek_author` (`rek_author`),
  KEY `rek_author_order` (`rek_author_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `fez_record_search_key_author_author_id`
--

DROP TABLE IF EXISTS `fez_record_search_key_author_author_id`;
/*!50001 DROP VIEW IF EXISTS `fez_record_search_key_author_author_id`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `fez_record_search_key_author_author_id` (
  `rek_author_id_auto_inc` int(11),
  `rek_author_pid` varchar(64),
  `rek_author_xsdmf_id` int(11),
  `rek_author` varchar(255),
  `rek_author_order` int(11),
  `rek_author_id_id` int(11),
  `rek_author_id_pid` varchar(64),
  `rek_author_id_xsdmf_id` int(11),
  `rek_author_id` int(11),
  `rek_author_id_order` int(11)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `fez_record_search_key_author_case_sensitive`
--

DROP TABLE IF EXISTS `fez_record_search_key_author_case_sensitive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_author_case_sensitive` (
  `rek_author_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_pid` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `rek_author_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author` varchar(255) DEFAULT NULL,
  `rek_author_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_author_id`),
  KEY `rek_author_pid` (`rek_author_pid`),
  KEY `rek_author` (`rek_author`),
  KEY `rek_author_order` (`rek_author_order`)
) ENGINE=InnoDB AUTO_INCREMENT=3308811 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_author_count`
--

DROP TABLE IF EXISTS `fez_record_search_key_author_count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_author_count` (
  `rek_author_count_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_count_pid` varchar(64) DEFAULT NULL,
  `rek_author_count_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author_count` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_author_count_id`),
  KEY `rek_author_count` (`rek_author_count`),
  KEY `rek_author_count_pid` (`rek_author_count_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_author_count__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_author_count__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_author_count__shadow` (
  `rek_author_count_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_count_pid` varchar(64) DEFAULT NULL,
  `rek_author_count_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author_count` varchar(255) DEFAULT NULL,
  `rek_author_count_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_author_count_id`),
  UNIQUE KEY `rek_author_count_pid_2` (`rek_author_count_pid`,`rek_author_count_stamp`),
  KEY `rek_author_count` (`rek_author_count`),
  KEY `rek_author_count_pid` (`rek_author_count_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_author_id`
--

DROP TABLE IF EXISTS `fez_record_search_key_author_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_author_id` (
  `rek_author_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_id_pid` varchar(64) DEFAULT NULL,
  `rek_author_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author_id` int(11) DEFAULT NULL,
  `rek_author_id_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_author_id_id`),
  UNIQUE KEY `unique_constraint` (`rek_author_id_pid`,`rek_author_id`,`rek_author_id_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_author_id_pid`,`rek_author_id_order`),
  KEY `rek_author_id_pid` (`rek_author_id_pid`),
  KEY `rek_author_id` (`rek_author_id`),
  KEY `rek_author_id_order` (`rek_author_id_order`)
) ENGINE=InnoDB AUTO_INCREMENT=17539205 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_author_id__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_author_id__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_author_id__shadow` (
  `rek_author_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_id_pid` varchar(64) DEFAULT NULL,
  `rek_author_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author_id` int(11) DEFAULT NULL,
  `rek_author_id_order` int(11) DEFAULT '1',
  `rek_author_id_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_author_id_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_author_id_pid`,`rek_author_id_order`),
  UNIQUE KEY `rek_author_id_pid_2` (`rek_author_id_pid`,`rek_author_id_stamp`),
  KEY `rek_author_id_pid` (`rek_author_id_pid`),
  KEY `rek_author_id` (`rek_author_id`),
  KEY `rek_author_id_order` (`rek_author_id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_author_role`
--

DROP TABLE IF EXISTS `fez_record_search_key_author_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_author_role` (
  `rek_author_role_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_role_pid` varchar(64) DEFAULT NULL,
  `rek_author_role_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author_role_order` int(11) DEFAULT '1',
  `rek_author_role` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_author_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_author_role__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_author_role__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_author_role__shadow` (
  `rek_author_role_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_author_role_pid` varchar(64) DEFAULT NULL,
  `rek_author_role_xsdmf_id` int(11) DEFAULT NULL,
  `rek_author_role_order` int(11) DEFAULT '1',
  `rek_author_role` varchar(255) DEFAULT NULL,
  `rek_author_role_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_author_role_id`),
  UNIQUE KEY `rek_author_role_pid` (`rek_author_role_pid`,`rek_author_role_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_book_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_book_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_book_title` (
  `rek_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_book_title` text,
  PRIMARY KEY (`rek_book_title_id`),
  UNIQUE KEY `rek_book_title_pid` (`rek_book_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=153281 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_book_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_book_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_book_title__shadow` (
  `rek_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_book_title` text,
  `rek_book_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_book_title_id`),
  UNIQUE KEY `rek_book_title_pid` (`rek_book_title_pid`),
  UNIQUE KEY `rek_book_title_pid_2` (`rek_book_title_pid`,`rek_book_title_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_building_materials`
--

DROP TABLE IF EXISTS `fez_record_search_key_building_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_building_materials` (
  `rek_building_materials_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_building_materials_pid` varchar(64) DEFAULT NULL,
  `rek_building_materials_xsdmf_id` int(11) DEFAULT NULL,
  `rek_building_materials_order` int(11) DEFAULT '1',
  `rek_building_materials` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_building_materials_id`),
  UNIQUE KEY `unique_constraint` (`rek_building_materials_pid`,`rek_building_materials_order`,`rek_building_materials`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_building_materials_pid`,`rek_building_materials_order`),
  KEY `rek_building_materials_pid` (`rek_building_materials_pid`),
  FULLTEXT KEY `fulltext` (`rek_building_materials`)
) ENGINE=MyISAM AUTO_INCREMENT=25671 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_building_materials__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_building_materials__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_building_materials__shadow` (
  `rek_building_materials_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_building_materials_pid` varchar(64) DEFAULT NULL,
  `rek_building_materials_xsdmf_id` int(11) DEFAULT NULL,
  `rek_building_materials_order` int(11) DEFAULT '1',
  `rek_building_materials` varchar(255) DEFAULT NULL,
  `rek_building_materials_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_building_materials_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_building_materials_pid`,`rek_building_materials_order`),
  UNIQUE KEY `rek_building_materials_pid_2` (`rek_building_materials_pid`,`rek_building_materials_stamp`),
  KEY `rek_building_materials_pid` (`rek_building_materials_pid`),
  FULLTEXT KEY `fulltext` (`rek_building_materials`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_case_sensitive`
--

DROP TABLE IF EXISTS `fez_record_search_key_case_sensitive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_case_sensitive` (
  `rek_pid` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'PID',
  `rek_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_title` varchar(1000) DEFAULT NULL COMMENT 'Title',
  `rek_description_xsdmf_id` int(11) DEFAULT NULL,
  `rek_description` text COMMENT 'Description',
  `rek_display_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_display_type` int(11) DEFAULT NULL COMMENT 'Display Type',
  `rek_status_xsdmf_id` int(11) DEFAULT NULL,
  `rek_status` int(11) DEFAULT NULL COMMENT 'Status',
  `rek_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_date` datetime DEFAULT NULL COMMENT 'Date',
  `rek_object_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_object_type` int(11) DEFAULT NULL COMMENT 'Object Type',
  `rek_depositor_xsdmf_id` int(11) DEFAULT NULL,
  `rek_depositor` int(11) DEFAULT NULL COMMENT 'Depositor',
  `rek_created_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_created_date` datetime DEFAULT NULL COMMENT 'Created Date',
  `rek_updated_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_updated_date` datetime DEFAULT NULL COMMENT 'Updated Date',
  `rek_file_downloads` int(11) DEFAULT '0',
  `rek_views` int(11) DEFAULT '0',
  `rek_citation` text,
  `rek_sequence` int(11) DEFAULT '0' COMMENT 'Sequence order in a parent object',
  `rek_sequence_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre` varchar(255) DEFAULT NULL COMMENT 'Genre',
  `rek_genre_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre_type` varchar(255) DEFAULT NULL COMMENT 'Genre Type',
  `rek_formatted_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_formatted_title` text COMMENT 'Formatted Title',
  `rek_formatted_abstract_xsdmf_id` int(11) DEFAULT NULL,
  `rek_formatted_abstract` text COMMENT 'Formatted Abstract',
  `rek_depositor_affiliation_xsdmf_id` int(11) DEFAULT NULL,
  `rek_depositor_affiliation` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_pid`),
  KEY `rek_display_type` (`rek_display_type`),
  KEY `rek_status` (`rek_status`),
  KEY `rek_date` (`rek_date`),
  KEY `rek_object_type` (`rek_object_type`),
  KEY `rek_depositor` (`rek_depositor`),
  KEY `rek_created_date` (`rek_created_date`),
  KEY `rek_updated_date` (`rek_updated_date`),
  KEY `rek_title` (`rek_title`(255)),
  KEY `rek_views` (`rek_views`),
  KEY `rek_file_downloads` (`rek_file_downloads`),
  KEY `rek_sequence` (`rek_sequence`),
  KEY `rek_genre` (`rek_genre`),
  KEY `rek_genre_type` (`rek_genre_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_case_sensitive_2`
--

DROP TABLE IF EXISTS `fez_record_search_key_case_sensitive_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_case_sensitive_2` (
  `rek_pid` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'PID',
  `rek_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_title` varchar(1000) DEFAULT NULL COMMENT 'Title',
  `rek_description_xsdmf_id` int(11) DEFAULT NULL,
  `rek_description` text COMMENT 'Description',
  `rek_display_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_display_type` int(11) DEFAULT NULL COMMENT 'Display Type',
  `rek_status_xsdmf_id` int(11) DEFAULT NULL,
  `rek_status` int(11) DEFAULT NULL COMMENT 'Status',
  `rek_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_date` datetime DEFAULT NULL COMMENT 'Date',
  `rek_object_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_object_type` int(11) DEFAULT NULL COMMENT 'Object Type',
  `rek_depositor_xsdmf_id` int(11) DEFAULT NULL,
  `rek_depositor` int(11) DEFAULT NULL COMMENT 'Depositor',
  `rek_created_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_created_date` datetime DEFAULT NULL COMMENT 'Created Date',
  `rek_updated_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_updated_date` datetime DEFAULT NULL COMMENT 'Updated Date',
  `rek_file_downloads` int(11) DEFAULT '0',
  `rek_views` int(11) DEFAULT '0',
  `rek_citation` text,
  `rek_sequence` int(11) DEFAULT '0' COMMENT 'Sequence order in a parent object',
  `rek_sequence_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre` varchar(255) DEFAULT NULL COMMENT 'Genre',
  `rek_genre_type_xsdmf_id` int(11) DEFAULT NULL,
  `rek_genre_type` varchar(255) DEFAULT NULL COMMENT 'Genre Type',
  `rek_formatted_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_formatted_title` text COMMENT 'Formatted Title',
  `rek_formatted_abstract_xsdmf_id` int(11) DEFAULT NULL,
  `rek_formatted_abstract` text COMMENT 'Formatted Abstract',
  `rek_depositor_affiliation_xsdmf_id` int(11) DEFAULT NULL,
  `rek_depositor_affiliation` int(11) DEFAULT NULL,
  `rek_thomson_citation_count` int(11) DEFAULT NULL,
  `rek_gs_citation_count` int(4) DEFAULT NULL,
  `rek_gs_cited_by_link` text,
  `rek_thomson_citation_count_xsdmf_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_pid`),
  KEY `rek_display_type` (`rek_display_type`),
  KEY `rek_status` (`rek_status`),
  KEY `rek_date` (`rek_date`),
  KEY `rek_object_type` (`rek_object_type`),
  KEY `rek_depositor` (`rek_depositor`),
  KEY `rek_created_date` (`rek_created_date`),
  KEY `rek_updated_date` (`rek_updated_date`),
  KEY `rek_title` (`rek_title`(255)),
  KEY `rek_views` (`rek_views`),
  KEY `rek_file_downloads` (`rek_file_downloads`),
  KEY `rek_sequence` (`rek_sequence`),
  KEY `rek_genre` (`rek_genre`),
  KEY `rek_genre_type` (`rek_genre_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_category`
--

DROP TABLE IF EXISTS `fez_record_search_key_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_category` (
  `rek_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_category_pid` varchar(64) DEFAULT NULL,
  `rek_category_xsdmf_id` int(11) DEFAULT NULL,
  `rek_category_order` int(11) DEFAULT '1',
  `rek_category` text,
  PRIMARY KEY (`rek_category_id`),
  UNIQUE KEY `unique_constraints` (`rek_category_pid`,`rek_category_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_category_pid`,`rek_category_order`),
  KEY `rek_category_pid` (`rek_category_pid`),
  FULLTEXT KEY `rek_category` (`rek_category`)
) ENGINE=MyISAM AUTO_INCREMENT=13465 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_category__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_category__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_category__shadow` (
  `rek_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_category_pid` varchar(64) DEFAULT NULL,
  `rek_category_xsdmf_id` int(11) DEFAULT NULL,
  `rek_category_order` int(11) DEFAULT '1',
  `rek_category` text,
  `rek_category_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_category_id`),
  UNIQUE KEY `unique_constraints` (`rek_category_pid`,`rek_category_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_category_pid`,`rek_category_order`),
  UNIQUE KEY `rek_category_pid_2` (`rek_category_pid`,`rek_category_stamp`),
  KEY `rek_category_pid` (`rek_category_pid`),
  FULLTEXT KEY `rek_category` (`rek_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_chapter_number`
--

DROP TABLE IF EXISTS `fez_record_search_key_chapter_number`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_chapter_number` (
  `rek_chapter_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_chapter_number_pid` varchar(64) DEFAULT NULL,
  `rek_chapter_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_chapter_number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_chapter_number_id`),
  UNIQUE KEY `unique_constraint` (`rek_chapter_number_pid`,`rek_chapter_number`),
  UNIQUE KEY `rek_chapter_number_pid` (`rek_chapter_number_pid`),
  KEY `rek_chapter_number` (`rek_chapter_number`)
) ENGINE=InnoDB AUTO_INCREMENT=55992 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_chapter_number__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_chapter_number__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_chapter_number__shadow` (
  `rek_chapter_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_chapter_number_pid` varchar(64) DEFAULT NULL,
  `rek_chapter_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_chapter_number` varchar(255) DEFAULT NULL,
  `rek_chapter_number_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_chapter_number_id`),
  UNIQUE KEY `rek_chapter_number_pid` (`rek_chapter_number_pid`),
  UNIQUE KEY `rek_chapter_number_pid_2` (`rek_chapter_number_pid`,`rek_chapter_number_stamp`),
  KEY `rek_chapter_number` (`rek_chapter_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_collection_year`
--

DROP TABLE IF EXISTS `fez_record_search_key_collection_year`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_collection_year` (
  `rek_collection_year_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_collection_year_pid` varchar(64) DEFAULT NULL,
  `rek_collection_year_xsdmf_id` int(11) DEFAULT NULL,
  `rek_collection_year` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_collection_year_id`),
  UNIQUE KEY `unique_constraint` (`rek_collection_year_pid`,`rek_collection_year`),
  UNIQUE KEY `rek_collection_year_pid` (`rek_collection_year_pid`),
  KEY `rek_collection_year` (`rek_collection_year`)
) ENGINE=InnoDB AUTO_INCREMENT=1671843 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_collection_year__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_collection_year__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_collection_year__shadow` (
  `rek_collection_year_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_collection_year_pid` varchar(64) DEFAULT NULL,
  `rek_collection_year_xsdmf_id` int(11) DEFAULT NULL,
  `rek_collection_year` datetime DEFAULT NULL,
  `rek_collection_year_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_collection_year_id`),
  UNIQUE KEY `rek_collection_year_pid` (`rek_collection_year_pid`),
  UNIQUE KEY `rek_collection_year_pid_2` (`rek_collection_year_pid`,`rek_collection_year_stamp`),
  KEY `rek_collection_year` (`rek_collection_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_condition`
--

DROP TABLE IF EXISTS `fez_record_search_key_condition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_condition` (
  `rek_condition_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_condition_pid` varchar(64) DEFAULT NULL,
  `rek_condition_xsdmf_id` int(11) DEFAULT NULL,
  `rek_condition_order` int(11) DEFAULT '1',
  `rek_condition` text,
  PRIMARY KEY (`rek_condition_id`),
  UNIQUE KEY `unique_constraint` (`rek_condition_pid`,`rek_condition_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_condition_pid`,`rek_condition_order`),
  KEY `rek_condition_pid` (`rek_condition_pid`),
  FULLTEXT KEY `rek_condition` (`rek_condition`)
) ENGINE=MyISAM AUTO_INCREMENT=5495 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_condition__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_condition__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_condition__shadow` (
  `rek_condition_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_condition_pid` varchar(64) DEFAULT NULL,
  `rek_condition_xsdmf_id` int(11) DEFAULT NULL,
  `rek_condition_order` int(11) DEFAULT '1',
  `rek_condition` text,
  `rek_condition_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_condition_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_condition_pid`,`rek_condition_order`),
  UNIQUE KEY `rek_condition_pid_2` (`rek_condition_pid`,`rek_condition_stamp`),
  KEY `rek_condition_pid` (`rek_condition_pid`),
  FULLTEXT KEY `rek_condition` (`rek_condition`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_conference_dates`
--

DROP TABLE IF EXISTS `fez_record_search_key_conference_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_conference_dates` (
  `rek_conference_dates_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_conference_dates_pid` varchar(64) DEFAULT NULL,
  `rek_conference_dates_xsdmf_id` int(11) DEFAULT NULL,
  `rek_conference_dates` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_conference_dates_id`),
  UNIQUE KEY `unique_constraint` (`rek_conference_dates_pid`,`rek_conference_dates`),
  UNIQUE KEY `rek_conference_dates_pid` (`rek_conference_dates_pid`),
  KEY `rek_conference_dates` (`rek_conference_dates`)
) ENGINE=InnoDB AUTO_INCREMENT=637792 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_conference_dates__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_conference_dates__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_conference_dates__shadow` (
  `rek_conference_dates_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_conference_dates_pid` varchar(64) DEFAULT NULL,
  `rek_conference_dates_xsdmf_id` int(11) DEFAULT NULL,
  `rek_conference_dates` varchar(255) DEFAULT NULL,
  `rek_conference_dates_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_conference_dates_id`),
  UNIQUE KEY `rek_conference_dates_pid` (`rek_conference_dates_pid`),
  UNIQUE KEY `rek_conference_dates_pid_2` (`rek_conference_dates_pid`,`rek_conference_dates_stamp`),
  KEY `rek_conference_dates` (`rek_conference_dates`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_conference_id`
--

DROP TABLE IF EXISTS `fez_record_search_key_conference_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_conference_id` (
  `rek_conference_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_conference_id_pid` varchar(64) DEFAULT NULL,
  `rek_conference_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_conference_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_conference_id_id`),
  KEY `rek_conference_id` (`rek_conference_id`),
  KEY `rek_conference_id_pid` (`rek_conference_id_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_conference_id__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_conference_id__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_conference_id__shadow` (
  `rek_conference_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_conference_id_pid` varchar(64) DEFAULT NULL,
  `rek_conference_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_conference_id` int(11) DEFAULT NULL,
  `rek_conference_id_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_conference_id_id`),
  UNIQUE KEY `rek_conference_id_pid_2` (`rek_conference_id_pid`,`rek_conference_id_stamp`),
  KEY `rek_conference_id` (`rek_conference_id`),
  KEY `rek_conference_id_pid` (`rek_conference_id_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_conference_location`
--

DROP TABLE IF EXISTS `fez_record_search_key_conference_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_conference_location` (
  `rek_conference_location_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_conference_location_pid` varchar(64) DEFAULT NULL,
  `rek_conference_location_xsdmf_id` int(11) DEFAULT NULL,
  `rek_conference_location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_conference_location_id`),
  UNIQUE KEY `unique_constraint` (`rek_conference_location_pid`,`rek_conference_location`),
  UNIQUE KEY `rek_conference_location_pid` (`rek_conference_location_pid`),
  KEY `rek_conference_location` (`rek_conference_location`),
  FULLTEXT KEY `rek_conference_location_ft` (`rek_conference_location`)
) ENGINE=MyISAM AUTO_INCREMENT=648947 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_conference_location__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_conference_location__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_conference_location__shadow` (
  `rek_conference_location_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_conference_location_pid` varchar(64) DEFAULT NULL,
  `rek_conference_location_xsdmf_id` int(11) DEFAULT NULL,
  `rek_conference_location` varchar(255) DEFAULT NULL,
  `rek_conference_location_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_conference_location_id`),
  UNIQUE KEY `rek_conference_location_pid` (`rek_conference_location_pid`),
  UNIQUE KEY `rek_conference_location_pid_2` (`rek_conference_location_pid`,`rek_conference_location_stamp`),
  KEY `rek_conference_location` (`rek_conference_location`),
  FULLTEXT KEY `rek_conference_location_ft` (`rek_conference_location`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_conference_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_conference_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_conference_name` (
  `rek_conference_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_conference_name_pid` varchar(64) DEFAULT NULL,
  `rek_conference_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_conference_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_conference_name_id`),
  UNIQUE KEY `unique_constraint` (`rek_conference_name_pid`,`rek_conference_name`),
  UNIQUE KEY `rek_conference_name_pid` (`rek_conference_name_pid`),
  KEY `rek_conference_name` (`rek_conference_name`),
  FULLTEXT KEY `rek_conference_name_ft` (`rek_conference_name`)
) ENGINE=MyISAM AUTO_INCREMENT=632707 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_conference_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_conference_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_conference_name__shadow` (
  `rek_conference_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_conference_name_pid` varchar(64) DEFAULT NULL,
  `rek_conference_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_conference_name` varchar(255) DEFAULT NULL,
  `rek_conference_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_conference_name_id`),
  UNIQUE KEY `rek_conference_name_pid` (`rek_conference_name_pid`),
  UNIQUE KEY `rek_conference_name_pid_2` (`rek_conference_name_pid`,`rek_conference_name_stamp`),
  KEY `rek_conference_name` (`rek_conference_name`),
  FULLTEXT KEY `rek_conference_name_ft` (`rek_conference_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_contact_details_email`
--

DROP TABLE IF EXISTS `fez_record_search_key_contact_details_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_contact_details_email` (
  `rek_contact_details_email_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_contact_details_email_pid` varchar(64) DEFAULT NULL,
  `rek_contact_details_email_xsdmf_id` int(11) DEFAULT NULL,
  `rek_contact_details_email_order` int(11) DEFAULT '1',
  `rek_contact_details_email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_contact_details_email_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_contact_details_email__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_contact_details_email__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_contact_details_email__shadow` (
  `rek_contact_details_email_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_contact_details_email_pid` varchar(64) DEFAULT NULL,
  `rek_contact_details_email_xsdmf_id` int(11) DEFAULT NULL,
  `rek_contact_details_email_order` int(11) DEFAULT '1',
  `rek_contact_details_email` varchar(255) DEFAULT NULL,
  `rek_contact_details_email_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_contact_details_email_id`),
  UNIQUE KEY `rek_contact_details_email_pid` (`rek_contact_details_email_pid`,`rek_contact_details_email_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_contact_details_physical`
--

DROP TABLE IF EXISTS `fez_record_search_key_contact_details_physical`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_contact_details_physical` (
  `rek_contact_details_physical_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_contact_details_physical_pid` varchar(64) DEFAULT NULL,
  `rek_contact_details_physical_xsdmf_id` int(11) DEFAULT NULL,
  `rek_contact_details_physical_order` int(11) DEFAULT '1',
  `rek_contact_details_physical` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_contact_details_physical_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_contact_details_physical__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_contact_details_physical__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_contact_details_physical__shadow` (
  `rek_contact_details_physical_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_contact_details_physical_pid` varchar(64) DEFAULT NULL,
  `rek_contact_details_physical_xsdmf_id` int(11) DEFAULT NULL,
  `rek_contact_details_physical_order` int(11) DEFAULT '1',
  `rek_contact_details_physical` varchar(255) DEFAULT NULL,
  `rek_contact_details_physical_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_contact_details_physical_id`),
  UNIQUE KEY `rek_contact_details_physical_pid` (`rek_contact_details_physical_pid`,`rek_contact_details_physical_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_contributor`
--

DROP TABLE IF EXISTS `fez_record_search_key_contributor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_contributor` (
  `rek_contributor_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_contributor_pid` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `rek_contributor_xsdmf_id` int(11) DEFAULT NULL,
  `rek_contributor` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `rek_contributor_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_contributor_id`),
  UNIQUE KEY `unique_constraint` (`rek_contributor_pid`,`rek_contributor`,`rek_contributor_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_contributor_pid`,`rek_contributor_order`),
  KEY `rek_contributor_pid` (`rek_contributor_pid`),
  KEY `rek_contributor` (`rek_contributor`),
  KEY `rek_contributor_order` (`rek_contributor_order`)
) ENGINE=InnoDB AUTO_INCREMENT=2203509 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_contributor__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_contributor__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_contributor__shadow` (
  `rek_contributor_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_contributor_pid` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `rek_contributor_xsdmf_id` int(11) DEFAULT NULL,
  `rek_contributor` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `rek_contributor_order` int(11) DEFAULT '1',
  `rek_contributor_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_contributor_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_contributor_pid`,`rek_contributor_order`),
  UNIQUE KEY `rek_contributor_pid_2` (`rek_contributor_pid`,`rek_contributor_stamp`),
  KEY `rek_contributor_pid` (`rek_contributor_pid`),
  KEY `rek_contributor` (`rek_contributor`),
  KEY `rek_contributor_order` (`rek_contributor_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `fez_record_search_key_contributor_contributor_id`
--

DROP TABLE IF EXISTS `fez_record_search_key_contributor_contributor_id`;
/*!50001 DROP VIEW IF EXISTS `fez_record_search_key_contributor_contributor_id`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `fez_record_search_key_contributor_contributor_id` (
  `rek_contributor_id_auto_inc` int(11),
  `rek_contributor_pid` varchar(64),
  `rek_contributor_xsdmf_id` int(11),
  `rek_contributor` varchar(255),
  `rek_contributor_order` int(11),
  `rek_contributor_id_id` int(11),
  `rek_contributor_id_pid` varchar(64),
  `rek_contributor_id_xsdmf_id` int(11),
  `rek_contributor_id` int(11),
  `rek_contributor_id_order` int(11)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `fez_record_search_key_contributor_id`
--

DROP TABLE IF EXISTS `fez_record_search_key_contributor_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_contributor_id` (
  `rek_contributor_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_contributor_id_pid` varchar(64) DEFAULT NULL,
  `rek_contributor_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_contributor_id` int(11) DEFAULT NULL,
  `rek_contributor_id_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_contributor_id_id`),
  UNIQUE KEY `unique_constraint` (`rek_contributor_id_pid`,`rek_contributor_id`,`rek_contributor_id_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_contributor_id_pid`,`rek_contributor_id_order`),
  KEY `rek_contributor_id_pid` (`rek_contributor_id_pid`),
  KEY `rek_contributor_id` (`rek_contributor_id`),
  KEY `rek_contributor_id_order` (`rek_contributor_id_order`)
) ENGINE=InnoDB AUTO_INCREMENT=1605488 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_contributor_id__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_contributor_id__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_contributor_id__shadow` (
  `rek_contributor_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_contributor_id_pid` varchar(64) DEFAULT NULL,
  `rek_contributor_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_contributor_id` int(11) DEFAULT NULL,
  `rek_contributor_id_order` int(11) DEFAULT '1',
  `rek_contributor_id_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_contributor_id_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_contributor_id_pid`,`rek_contributor_id_order`),
  UNIQUE KEY `rek_contributor_id_pid_2` (`rek_contributor_id_pid`,`rek_contributor_id_stamp`),
  KEY `rek_contributor_id_pid` (`rek_contributor_id_pid`),
  KEY `rek_contributor_id` (`rek_contributor_id`),
  KEY `rek_contributor_id_order` (`rek_contributor_id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_contributor_role`
--

DROP TABLE IF EXISTS `fez_record_search_key_contributor_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_contributor_role` (
  `rek_contributor_role_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_contributor_role_pid` varchar(64) DEFAULT NULL,
  `rek_contributor_role_xsdmf_id` int(11) DEFAULT NULL,
  `rek_contributor_role_order` int(11) DEFAULT '1',
  `rek_contributor_role` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_contributor_role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_contributor_role__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_contributor_role__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_contributor_role__shadow` (
  `rek_contributor_role_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_contributor_role_pid` varchar(64) DEFAULT NULL,
  `rek_contributor_role_xsdmf_id` int(11) DEFAULT NULL,
  `rek_contributor_role_order` int(11) DEFAULT '1',
  `rek_contributor_role` varchar(255) DEFAULT NULL,
  `rek_contributor_role_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_contributor_role_id`),
  UNIQUE KEY `rek_contributor_role_pid` (`rek_contributor_role_pid`,`rek_contributor_role_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_convener`
--

DROP TABLE IF EXISTS `fez_record_search_key_convener`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_convener` (
  `rek_convener_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_convener_pid` varchar(64) DEFAULT NULL,
  `rek_convener_xsdmf_id` int(11) DEFAULT NULL,
  `rek_convener` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_convener_id`),
  UNIQUE KEY `unique_constraint` (`rek_convener_pid`,`rek_convener`),
  UNIQUE KEY `rek_convener_pid` (`rek_convener_pid`),
  KEY `rek_convener` (`rek_convener`)
) ENGINE=InnoDB AUTO_INCREMENT=35927 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_convener__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_convener__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_convener__shadow` (
  `rek_convener_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_convener_pid` varchar(64) DEFAULT NULL,
  `rek_convener_xsdmf_id` int(11) DEFAULT NULL,
  `rek_convener` varchar(255) DEFAULT NULL,
  `rek_convener_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_convener_id`),
  UNIQUE KEY `rek_convener_pid` (`rek_convener_pid`),
  UNIQUE KEY `rek_convener_pid_2` (`rek_convener_pid`,`rek_convener_stamp`),
  KEY `rek_convener` (`rek_convener`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `fez_record_search_key_core`
--

DROP TABLE IF EXISTS `fez_record_search_key_core`;
/*!50001 DROP VIEW IF EXISTS `fez_record_search_key_core`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `fez_record_search_key_core` (
  `rek_pid` varchar(64),
  `rek_thomson_citation_count_xsdmf_id` int(11),
  `rek_thomson_citation_count` int(11),
  `rek_title_xsdmf_id` int(11),
  `rek_title` varchar(1000),
  `rek_description_xsdmf_id` int(11),
  `rek_description` text,
  `rek_display_type_xsdmf_id` int(11),
  `rek_display_type` int(11),
  `rek_status_xsdmf_id` int(11),
  `rek_status` int(11),
  `rek_date_xsdmf_id` int(11),
  `rek_date` datetime,
  `rek_object_type_xsdmf_id` int(11),
  `rek_object_type` int(11),
  `rek_depositor_xsdmf_id` int(11),
  `rek_depositor` int(11),
  `rek_created_date_xsdmf_id` int(11),
  `rek_created_date` datetime,
  `rek_updated_date_xsdmf_id` int(11),
  `rek_updated_date` datetime,
  `rek_file_downloads` int(11),
  `rek_views` int(11),
  `rek_citation` text,
  `rek_sequence` int(11),
  `rek_sequence_xsdmf_id` int(11),
  `rek_genre_xsdmf_id` int(11),
  `rek_genre` varchar(255),
  `rek_genre_type_xsdmf_id` int(11),
  `rek_genre_type` varchar(255),
  `rek_formatted_title_xsdmf_id` int(11),
  `rek_formatted_title` text,
  `rek_formatted_abstract_xsdmf_id` int(11),
  `rek_formatted_abstract` text,
  `rek_depositor_affiliation_xsdmf_id` int(11),
  `rek_depositor_affiliation` int(11),
  `rek_proceedings_title_id` int(11),
  `rek_proceedings_title_pid` varchar(64),
  `rek_proceedings_title_xsdmf_id` int(11),
  `rek_proceedings_title` varchar(255),
  `rek_collection_year_id` int(11),
  `rek_collection_year_pid` varchar(64),
  `rek_collection_year_xsdmf_id` int(11),
  `rek_collection_year` datetime,
  `rek_total_pages_id` int(11),
  `rek_total_pages_pid` varchar(64),
  `rek_total_pages_xsdmf_id` int(11),
  `rek_total_pages` varchar(255),
  `rek_total_chapters_id` int(11),
  `rek_total_chapters_pid` varchar(64),
  `rek_total_chapters_xsdmf_id` int(11),
  `rek_total_chapters` varchar(255),
  `rek_notes_id` int(11),
  `rek_notes_pid` varchar(64),
  `rek_notes_xsdmf_id` int(11),
  `rek_notes` text,
  `rek_publisher_id` int(11),
  `rek_publisher_pid` varchar(64),
  `rek_publisher_xsdmf_id` int(11),
  `rek_publisher` varchar(255),
  `rek_refereed_id` int(11),
  `rek_refereed_pid` varchar(64),
  `rek_refereed_xsdmf_id` int(11),
  `rek_refereed` int(11),
  `rek_series_id` int(11),
  `rek_series_pid` varchar(64),
  `rek_series_xsdmf_id` int(11),
  `rek_series` varchar(255),
  `rek_journal_name_id` int(11),
  `rek_journal_name_pid` varchar(64),
  `rek_journal_name_xsdmf_id` int(11),
  `rek_journal_name` varchar(255),
  `rek_newspaper_id` int(11),
  `rek_newspaper_pid` varchar(64),
  `rek_newspaper_xsdmf_id` int(11),
  `rek_newspaper` varchar(255),
  `rek_conference_name_id` int(11),
  `rek_conference_name_pid` varchar(64),
  `rek_conference_name_xsdmf_id` int(11),
  `rek_conference_name` varchar(255),
  `rek_book_title_id` int(11),
  `rek_book_title_pid` varchar(64),
  `rek_book_title_xsdmf_id` int(11),
  `rek_book_title` text,
  `rek_edition_id` int(11),
  `rek_edition_pid` varchar(64),
  `rek_edition_xsdmf_id` int(11),
  `rek_edition` varchar(255),
  `rek_place_of_publication_id` int(11),
  `rek_place_of_publication_pid` varchar(64),
  `rek_place_of_publication_xsdmf_id` int(11),
  `rek_place_of_publication` varchar(255),
  `rek_start_page_id` int(11),
  `rek_start_page_pid` varchar(64),
  `rek_start_page_xsdmf_id` int(11),
  `rek_start_page` varchar(255),
  `rek_end_page_id` int(11),
  `rek_end_page_pid` varchar(64),
  `rek_end_page_xsdmf_id` int(11),
  `rek_end_page` varchar(255),
  `rek_chapter_number_id` int(11),
  `rek_chapter_number_pid` varchar(64),
  `rek_chapter_number_xsdmf_id` int(11),
  `rek_chapter_number` varchar(255),
  `rek_issue_number_id` int(11),
  `rek_issue_number_pid` varchar(64),
  `rek_issue_number_xsdmf_id` int(11),
  `rek_issue_number` varchar(255),
  `rek_volume_number_id` int(11),
  `rek_volume_number_pid` varchar(64),
  `rek_volume_number_xsdmf_id` int(11),
  `rek_volume_number` varchar(255),
  `rek_conference_dates_id` int(11),
  `rek_conference_dates_pid` varchar(64),
  `rek_conference_dates_xsdmf_id` int(11),
  `rek_conference_dates` varchar(255),
  `rek_conference_location_id` int(11),
  `rek_conference_location_pid` varchar(64),
  `rek_conference_location_xsdmf_id` int(11),
  `rek_conference_location` varchar(255),
  `rek_patent_number_id` int(11),
  `rek_patent_number_pid` varchar(64),
  `rek_patent_number_xsdmf_id` int(11),
  `rek_patent_number` varchar(255),
  `rek_country_of_issue_id` int(11),
  `rek_country_of_issue_pid` varchar(64),
  `rek_country_of_issue_xsdmf_id` int(11),
  `rek_country_of_issue` varchar(255),
  `rek_date_available_id` int(11),
  `rek_date_available_pid` varchar(64),
  `rek_date_available_xsdmf_id` int(11),
  `rek_date_available` datetime,
  `rek_language_id` int(11),
  `rek_language_pid` varchar(64),
  `rek_language_xsdmf_id` int(11),
  `rek_language` varchar(255),
  `rek_phonetic_title_id` int(11),
  `rek_phonetic_title_pid` varchar(64),
  `rek_phonetic_title_xsdmf_id` int(11),
  `rek_phonetic_title` varchar(255),
  `rek_language_of_title_id` int(11),
  `rek_language_of_title_pid` varchar(64),
  `rek_language_of_title_xsdmf_id` int(11),
  `rek_language_of_title` varchar(255),
  `rek_translated_title_id` int(11),
  `rek_translated_title_pid` varchar(64),
  `rek_translated_title_xsdmf_id` int(11),
  `rek_translated_title` varchar(255),
  `rek_phonetic_journal_name_id` int(11),
  `rek_phonetic_journal_name_pid` varchar(64),
  `rek_phonetic_journal_name_xsdmf_id` int(11),
  `rek_phonetic_journal_name` varchar(255),
  `rek_translated_journal_name_id` int(11),
  `rek_translated_journal_name_pid` varchar(64),
  `rek_translated_journal_name_xsdmf_id` int(11),
  `rek_translated_journal_name` varchar(255),
  `rek_phonetic_book_title_id` int(11),
  `rek_phonetic_book_title_pid` varchar(64),
  `rek_phonetic_book_title_xsdmf_id` int(11),
  `rek_phonetic_book_title` varchar(255),
  `rek_translated_book_title_id` int(11),
  `rek_translated_book_title_pid` varchar(64),
  `rek_translated_book_title_xsdmf_id` int(11),
  `rek_translated_book_title` varchar(255),
  `rek_phonetic_newspaper_id` int(11),
  `rek_phonetic_newspaper_pid` varchar(64),
  `rek_phonetic_newspaper_xsdmf_id` int(11),
  `rek_phonetic_newspaper` varchar(255),
  `rek_translated_newspaper_id` int(11),
  `rek_translated_newspaper_pid` varchar(64),
  `rek_translated_newspaper_xsdmf_id` int(11),
  `rek_translated_newspaper` varchar(255),
  `rek_phonetic_conference_name_id` int(11),
  `rek_phonetic_conference_name_pid` varchar(64),
  `rek_phonetic_conference_name_xsdmf_id` int(11),
  `rek_phonetic_conference_name` varchar(255),
  `rek_translated_conference_name_id` int(11),
  `rek_translated_conference_name_pid` varchar(64),
  `rek_translated_conference_name_xsdmf_id` int(11),
  `rek_translated_conference_name` varchar(255),
  `rek_issn_id` int(11),
  `rek_issn_pid` varchar(64),
  `rek_issn_xsdmf_id` int(11),
  `rek_issn` varchar(255),
  `rek_isbn_id` int(11),
  `rek_isbn_pid` varchar(64),
  `rek_isbn_xsdmf_id` int(11),
  `rek_isbn` varchar(255),
  `rek_isi_loc_id` int(11),
  `rek_isi_loc_pid` varchar(64),
  `rek_isi_loc_xsdmf_id` int(11),
  `rek_isi_loc` varchar(255),
  `rek_prn_id` int(11),
  `rek_prn_pid` varchar(64),
  `rek_prn_xsdmf_id` int(11),
  `rek_prn` varchar(255),
  `rek_output_availability_id` int(11),
  `rek_output_availability_pid` varchar(64),
  `rek_output_availability_xsdmf_id` int(11),
  `rek_output_availability` varchar(1),
  `rek_na_explanation_id` int(11),
  `rek_na_explanation_pid` varchar(64),
  `rek_na_explanation_xsdmf_id` int(11),
  `rek_na_explanation` text,
  `rek_sensitivity_explanation_id` int(11),
  `rek_sensitivity_explanation_pid` varchar(64),
  `rek_sensitivity_explanation_xsdmf_id` int(11),
  `rek_sensitivity_explanation` text,
  `rek_org_unit_name_id` int(11),
  `rek_org_unit_name_pid` varchar(64),
  `rek_org_unit_name_xsdmf_id` int(11),
  `rek_org_unit_name` varchar(255),
  `rek_org_name_id` int(11),
  `rek_org_name_pid` varchar(64),
  `rek_org_name_xsdmf_id` int(11),
  `rek_org_name` varchar(255),
  `rek_report_number_id` int(11),
  `rek_report_number_pid` varchar(64),
  `rek_report_number_xsdmf_id` int(11),
  `rek_report_number` varchar(255),
  `rek_parent_publication_id` int(11),
  `rek_parent_publication_pid` varchar(64),
  `rek_parent_publication_xsdmf_id` int(11),
  `rek_parent_publication` varchar(255),
  `rek_scopus_id_id` int(11),
  `rek_scopus_id_pid` varchar(64),
  `rek_scopus_id_xsdmf_id` int(11),
  `rek_scopus_id` varchar(255),
  `rek_convener_id` int(11),
  `rek_convener_pid` varchar(64),
  `rek_convener_xsdmf_id` int(11),
  `rek_convener` varchar(255)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `fez_record_search_key_core_filtered`
--

DROP TABLE IF EXISTS `fez_record_search_key_core_filtered`;
/*!50001 DROP VIEW IF EXISTS `fez_record_search_key_core_filtered`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `fez_record_search_key_core_filtered` (
  `rek_pid` varchar(64),
  `rek_scopus_id_id` int(11),
  `rek_scopus_id_pid` varchar(64),
  `rek_scopus_id_xsdmf_id` int(11),
  `rek_scopus_id` varchar(255),
  `rek_thomson_citation_count_xsdmf_id` int(11),
  `rek_thomson_citation_count` int(11),
  `rek_title_xsdmf_id` int(11),
  `rek_title` varchar(1000),
  `rek_description_xsdmf_id` int(11),
  `rek_description` text,
  `rek_display_type_xsdmf_id` int(11),
  `rek_display_type` int(11),
  `rek_status_xsdmf_id` int(11),
  `rek_status` int(11),
  `rek_date_xsdmf_id` int(11),
  `rek_date` datetime,
  `rek_object_type_xsdmf_id` int(11),
  `rek_object_type` int(11),
  `rek_depositor_xsdmf_id` int(11),
  `rek_depositor` int(11),
  `rek_created_date_xsdmf_id` int(11),
  `rek_created_date` datetime,
  `rek_updated_date_xsdmf_id` int(11),
  `rek_updated_date` datetime,
  `rek_file_downloads` int(11),
  `rek_views` int(11),
  `rek_citation` text,
  `rek_sequence` int(11),
  `rek_sequence_xsdmf_id` int(11),
  `rek_genre_xsdmf_id` int(11),
  `rek_genre` varchar(255),
  `rek_genre_type_xsdmf_id` int(11),
  `rek_genre_type` varchar(255),
  `rek_formatted_title_xsdmf_id` int(11),
  `rek_formatted_title` text,
  `rek_formatted_abstract_xsdmf_id` int(11),
  `rek_formatted_abstract` text,
  `rek_depositor_affiliation_xsdmf_id` int(11),
  `rek_depositor_affiliation` int(11),
  `rek_proceedings_title_id` int(11),
  `rek_proceedings_title_pid` varchar(64),
  `rek_proceedings_title_xsdmf_id` int(11),
  `rek_proceedings_title` varchar(255),
  `rek_collection_year_id` int(11),
  `rek_collection_year_pid` varchar(64),
  `rek_collection_year_xsdmf_id` int(11),
  `rek_collection_year` datetime,
  `rek_total_pages_id` int(11),
  `rek_total_pages_pid` varchar(64),
  `rek_total_pages_xsdmf_id` int(11),
  `rek_total_pages` varchar(255),
  `rek_total_chapters_id` int(11),
  `rek_total_chapters_pid` varchar(64),
  `rek_total_chapters_xsdmf_id` int(11),
  `rek_total_chapters` varchar(255),
  `rek_notes_id` int(11),
  `rek_notes_pid` varchar(64),
  `rek_notes_xsdmf_id` int(11),
  `rek_notes` text,
  `rek_publisher_id` int(11),
  `rek_publisher_pid` varchar(64),
  `rek_publisher_xsdmf_id` int(11),
  `rek_publisher` varchar(255),
  `rek_refereed_id` int(11),
  `rek_refereed_pid` varchar(64),
  `rek_refereed_xsdmf_id` int(11),
  `rek_refereed` int(11),
  `rek_series_id` int(11),
  `rek_series_pid` varchar(64),
  `rek_series_xsdmf_id` int(11),
  `rek_series` varchar(255),
  `rek_journal_name_id` int(11),
  `rek_journal_name_pid` varchar(64),
  `rek_journal_name_xsdmf_id` int(11),
  `rek_journal_name` varchar(255),
  `rek_newspaper_id` int(11),
  `rek_newspaper_pid` varchar(64),
  `rek_newspaper_xsdmf_id` int(11),
  `rek_newspaper` varchar(255),
  `rek_conference_name_id` int(11),
  `rek_conference_name_pid` varchar(64),
  `rek_conference_name_xsdmf_id` int(11),
  `rek_conference_name` varchar(255),
  `rek_book_title_id` int(11),
  `rek_book_title_pid` varchar(64),
  `rek_book_title_xsdmf_id` int(11),
  `rek_book_title` text,
  `rek_edition_id` int(11),
  `rek_edition_pid` varchar(64),
  `rek_edition_xsdmf_id` int(11),
  `rek_edition` varchar(255),
  `rek_place_of_publication_id` int(11),
  `rek_place_of_publication_pid` varchar(64),
  `rek_place_of_publication_xsdmf_id` int(11),
  `rek_place_of_publication` varchar(255),
  `rek_start_page_id` int(11),
  `rek_start_page_pid` varchar(64),
  `rek_start_page_xsdmf_id` int(11),
  `rek_start_page` varchar(255),
  `rek_end_page_id` int(11),
  `rek_end_page_pid` varchar(64),
  `rek_end_page_xsdmf_id` int(11),
  `rek_end_page` varchar(255),
  `rek_chapter_number_id` int(11),
  `rek_chapter_number_pid` varchar(64),
  `rek_chapter_number_xsdmf_id` int(11),
  `rek_chapter_number` varchar(255),
  `rek_issue_number_id` int(11),
  `rek_issue_number_pid` varchar(64),
  `rek_issue_number_xsdmf_id` int(11),
  `rek_issue_number` varchar(255),
  `rek_volume_number_id` int(11),
  `rek_volume_number_pid` varchar(64),
  `rek_volume_number_xsdmf_id` int(11),
  `rek_volume_number` varchar(255),
  `rek_conference_dates_id` int(11),
  `rek_conference_dates_pid` varchar(64),
  `rek_conference_dates_xsdmf_id` int(11),
  `rek_conference_dates` varchar(255),
  `rek_conference_location_id` int(11),
  `rek_conference_location_pid` varchar(64),
  `rek_conference_location_xsdmf_id` int(11),
  `rek_conference_location` varchar(255),
  `rek_patent_number_id` int(11),
  `rek_patent_number_pid` varchar(64),
  `rek_patent_number_xsdmf_id` int(11),
  `rek_patent_number` varchar(255),
  `rek_country_of_issue_id` int(11),
  `rek_country_of_issue_pid` varchar(64),
  `rek_country_of_issue_xsdmf_id` int(11),
  `rek_country_of_issue` varchar(255),
  `rek_date_available_id` int(11),
  `rek_date_available_pid` varchar(64),
  `rek_date_available_xsdmf_id` int(11),
  `rek_date_available` datetime,
  `rek_language_id` int(11),
  `rek_language_pid` varchar(64),
  `rek_language_xsdmf_id` int(11),
  `rek_language` varchar(255),
  `rek_phonetic_title_id` int(11),
  `rek_phonetic_title_pid` varchar(64),
  `rek_phonetic_title_xsdmf_id` int(11),
  `rek_phonetic_title` varchar(255),
  `rek_language_of_title_id` int(11),
  `rek_language_of_title_pid` varchar(64),
  `rek_language_of_title_xsdmf_id` int(11),
  `rek_language_of_title` varchar(255),
  `rek_translated_title_id` int(11),
  `rek_translated_title_pid` varchar(64),
  `rek_translated_title_xsdmf_id` int(11),
  `rek_translated_title` varchar(255),
  `rek_phonetic_journal_name_id` int(11),
  `rek_phonetic_journal_name_pid` varchar(64),
  `rek_phonetic_journal_name_xsdmf_id` int(11),
  `rek_phonetic_journal_name` varchar(255),
  `rek_translated_journal_name_id` int(11),
  `rek_translated_journal_name_pid` varchar(64),
  `rek_translated_journal_name_xsdmf_id` int(11),
  `rek_translated_journal_name` varchar(255),
  `rek_phonetic_book_title_id` int(11),
  `rek_phonetic_book_title_pid` varchar(64),
  `rek_phonetic_book_title_xsdmf_id` int(11),
  `rek_phonetic_book_title` varchar(255),
  `rek_translated_book_title_id` int(11),
  `rek_translated_book_title_pid` varchar(64),
  `rek_translated_book_title_xsdmf_id` int(11),
  `rek_translated_book_title` varchar(255),
  `rek_phonetic_newspaper_id` int(11),
  `rek_phonetic_newspaper_pid` varchar(64),
  `rek_phonetic_newspaper_xsdmf_id` int(11),
  `rek_phonetic_newspaper` varchar(255),
  `rek_translated_newspaper_id` int(11),
  `rek_translated_newspaper_pid` varchar(64),
  `rek_translated_newspaper_xsdmf_id` int(11),
  `rek_translated_newspaper` varchar(255),
  `rek_phonetic_conference_name_id` int(11),
  `rek_phonetic_conference_name_pid` varchar(64),
  `rek_phonetic_conference_name_xsdmf_id` int(11),
  `rek_phonetic_conference_name` varchar(255),
  `rek_translated_conference_name_id` int(11),
  `rek_translated_conference_name_pid` varchar(64),
  `rek_translated_conference_name_xsdmf_id` int(11),
  `rek_translated_conference_name` varchar(255),
  `rek_issn_id` int(11),
  `rek_issn_pid` varchar(64),
  `rek_issn_xsdmf_id` int(11),
  `rek_issn` varchar(255),
  `rek_isbn_id` int(11),
  `rek_isbn_pid` varchar(64),
  `rek_isbn_xsdmf_id` int(11),
  `rek_isbn` varchar(255),
  `rek_isi_loc_id` int(11),
  `rek_isi_loc_pid` varchar(64),
  `rek_isi_loc_xsdmf_id` int(11),
  `rek_isi_loc` varchar(255),
  `rek_prn_id` int(11),
  `rek_prn_pid` varchar(64),
  `rek_prn_xsdmf_id` int(11),
  `rek_prn` varchar(255),
  `rek_output_availability_id` int(11),
  `rek_output_availability_pid` varchar(64),
  `rek_output_availability_xsdmf_id` int(11),
  `rek_output_availability` varchar(1),
  `rek_na_explanation_id` int(11),
  `rek_na_explanation_pid` varchar(64),
  `rek_na_explanation_xsdmf_id` int(11),
  `rek_na_explanation` text,
  `rek_sensitivity_explanation_id` int(11),
  `rek_sensitivity_explanation_pid` varchar(64),
  `rek_sensitivity_explanation_xsdmf_id` int(11),
  `rek_sensitivity_explanation` text,
  `rek_org_unit_name_id` int(11),
  `rek_org_unit_name_pid` varchar(64),
  `rek_org_unit_name_xsdmf_id` int(11),
  `rek_org_unit_name` varchar(255),
  `rek_org_name_id` int(11),
  `rek_org_name_pid` varchar(64),
  `rek_org_name_xsdmf_id` int(11),
  `rek_org_name` varchar(255),
  `rek_report_number_id` int(11),
  `rek_report_number_pid` varchar(64),
  `rek_report_number_xsdmf_id` int(11),
  `rek_report_number` varchar(255),
  `rek_parent_publication_id` int(11),
  `rek_parent_publication_pid` varchar(64),
  `rek_parent_publication_xsdmf_id` int(11),
  `rek_parent_publication` varchar(255),
  `rek_convener_id` int(11),
  `rek_convener_pid` varchar(64),
  `rek_convener_xsdmf_id` int(11),
  `rek_convener` varchar(255)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `fez_record_search_key_country_of_issue`
--

DROP TABLE IF EXISTS `fez_record_search_key_country_of_issue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_country_of_issue` (
  `rek_country_of_issue_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_country_of_issue_pid` varchar(64) DEFAULT NULL,
  `rek_country_of_issue_xsdmf_id` int(11) DEFAULT NULL,
  `rek_country_of_issue` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_country_of_issue_id`),
  UNIQUE KEY `unique_constraint` (`rek_country_of_issue_pid`,`rek_country_of_issue`),
  UNIQUE KEY `rek_country_of_issue_pid` (`rek_country_of_issue_pid`),
  KEY `rek_country_of_issue` (`rek_country_of_issue`),
  FULLTEXT KEY `rek_country_of_issue_ft` (`rek_country_of_issue`)
) ENGINE=MyISAM AUTO_INCREMENT=1005 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_country_of_issue__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_country_of_issue__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_country_of_issue__shadow` (
  `rek_country_of_issue_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_country_of_issue_pid` varchar(64) DEFAULT NULL,
  `rek_country_of_issue_xsdmf_id` int(11) DEFAULT NULL,
  `rek_country_of_issue` varchar(255) DEFAULT NULL,
  `rek_country_of_issue_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_country_of_issue_id`),
  UNIQUE KEY `rek_country_of_issue_pid` (`rek_country_of_issue_pid`),
  UNIQUE KEY `rek_country_of_issue_pid_2` (`rek_country_of_issue_pid`,`rek_country_of_issue_stamp`),
  KEY `rek_country_of_issue` (`rek_country_of_issue`),
  FULLTEXT KEY `rek_country_of_issue_ft` (`rek_country_of_issue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_coverage_period`
--

DROP TABLE IF EXISTS `fez_record_search_key_coverage_period`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_coverage_period` (
  `rek_coverage_period_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_coverage_period_pid` varchar(64) DEFAULT NULL,
  `rek_coverage_period_xsdmf_id` int(11) DEFAULT NULL,
  `rek_coverage_period_order` int(11) DEFAULT '1',
  `rek_coverage_period` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_coverage_period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_coverage_period__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_coverage_period__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_coverage_period__shadow` (
  `rek_coverage_period_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_coverage_period_pid` varchar(64) DEFAULT NULL,
  `rek_coverage_period_xsdmf_id` int(11) DEFAULT NULL,
  `rek_coverage_period_order` int(11) DEFAULT '1',
  `rek_coverage_period` varchar(255) DEFAULT NULL,
  `rek_coverage_period_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_coverage_period_id`),
  UNIQUE KEY `rek_coverage_period_pid` (`rek_coverage_period_pid`,`rek_coverage_period_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_date_available`
--

DROP TABLE IF EXISTS `fez_record_search_key_date_available`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_date_available` (
  `rek_date_available_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_date_available_pid` varchar(64) DEFAULT NULL,
  `rek_date_available_xsdmf_id` int(11) DEFAULT NULL,
  `rek_date_available` datetime DEFAULT NULL COMMENT 'Date Available',
  PRIMARY KEY (`rek_date_available_id`),
  UNIQUE KEY `unique_constraint` (`rek_date_available_pid`,`rek_date_available`),
  UNIQUE KEY `rek_date_available_pid` (`rek_date_available_pid`),
  KEY `rek_date_available` (`rek_date_available`)
) ENGINE=InnoDB AUTO_INCREMENT=479407 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_date_available__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_date_available__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_date_available__shadow` (
  `rek_date_available_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_date_available_pid` varchar(64) DEFAULT NULL,
  `rek_date_available_xsdmf_id` int(11) DEFAULT NULL,
  `rek_date_available` datetime DEFAULT NULL COMMENT 'Date Available',
  `rek_date_available_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_date_available_id`),
  UNIQUE KEY `rek_date_available_pid` (`rek_date_available_pid`),
  UNIQUE KEY `rek_date_available_pid_2` (`rek_date_available_pid`,`rek_date_available_stamp`),
  KEY `rek_date_available` (`rek_date_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_doi`
--

DROP TABLE IF EXISTS `fez_record_search_key_doi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_doi` (
  `rek_doi_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_doi_pid` varchar(64) DEFAULT NULL,
  `rek_doi_xsdmf_id` int(11) DEFAULT NULL,
  `rek_doi` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_doi_id`),
  KEY `rek_doi` (`rek_doi`),
  KEY `rek_doi_pid` (`rek_doi_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=135809 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_doi__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_doi__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_doi__shadow` (
  `rek_doi_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_doi_pid` varchar(64) DEFAULT NULL,
  `rek_doi_xsdmf_id` int(11) DEFAULT NULL,
  `rek_doi` varchar(255) DEFAULT NULL,
  `rek_doi_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_doi_id`),
  UNIQUE KEY `rek_doi_pid_2` (`rek_doi_pid`,`rek_doi_stamp`),
  KEY `rek_doi` (`rek_doi`),
  KEY `rek_doi_pid` (`rek_doi_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_edition`
--

DROP TABLE IF EXISTS `fez_record_search_key_edition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_edition` (
  `rek_edition_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_edition_pid` varchar(64) DEFAULT NULL,
  `rek_edition_xsdmf_id` int(11) DEFAULT NULL,
  `rek_edition` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_edition_id`),
  UNIQUE KEY `unique_constraint` (`rek_edition_pid`,`rek_edition`),
  UNIQUE KEY `rek_edition_pid` (`rek_edition_pid`),
  KEY `rek_edition` (`rek_edition`)
) ENGINE=InnoDB AUTO_INCREMENT=58334 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_edition__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_edition__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_edition__shadow` (
  `rek_edition_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_edition_pid` varchar(64) DEFAULT NULL,
  `rek_edition_xsdmf_id` int(11) DEFAULT NULL,
  `rek_edition` varchar(255) DEFAULT NULL,
  `rek_edition_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_edition_id`),
  UNIQUE KEY `rek_edition_pid` (`rek_edition_pid`),
  UNIQUE KEY `rek_edition_pid_2` (`rek_edition_pid`,`rek_edition_stamp`),
  KEY `rek_edition` (`rek_edition`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_end_date`
--

DROP TABLE IF EXISTS `fez_record_search_key_end_date`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_end_date` (
  `rek_end_date_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_end_date_pid` varchar(64) DEFAULT NULL,
  `rek_end_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_end_date_id`),
  KEY `rek_end_date` (`rek_end_date`),
  KEY `rek_end_date_pid` (`rek_end_date_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_end_date__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_end_date__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_end_date__shadow` (
  `rek_end_date_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_end_date_pid` varchar(64) DEFAULT NULL,
  `rek_end_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_end_date` datetime DEFAULT NULL,
  `rek_end_date_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_end_date_id`),
  UNIQUE KEY `rek_end_date_pid_2` (`rek_end_date_pid`,`rek_end_date_stamp`),
  KEY `rek_end_date` (`rek_end_date`),
  KEY `rek_end_date_pid` (`rek_end_date_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_end_page`
--

DROP TABLE IF EXISTS `fez_record_search_key_end_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_end_page` (
  `rek_end_page_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_end_page_pid` varchar(64) DEFAULT NULL,
  `rek_end_page_xsdmf_id` int(11) DEFAULT NULL,
  `rek_end_page` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_end_page_id`),
  UNIQUE KEY `unique_constraint` (`rek_end_page_pid`,`rek_end_page`),
  UNIQUE KEY `rek_end_page_pid` (`rek_end_page_pid`),
  KEY `rek_end_page` (`rek_end_page`)
) ENGINE=InnoDB AUTO_INCREMENT=3682177 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_end_page__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_end_page__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_end_page__shadow` (
  `rek_end_page_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_end_page_pid` varchar(64) DEFAULT NULL,
  `rek_end_page_xsdmf_id` int(11) DEFAULT NULL,
  `rek_end_page` varchar(255) DEFAULT NULL,
  `rek_end_page_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_end_page_id`),
  UNIQUE KEY `rek_end_page_pid` (`rek_end_page_pid`),
  UNIQUE KEY `rek_end_page_pid_2` (`rek_end_page_pid`,`rek_end_page_stamp`),
  KEY `rek_end_page` (`rek_end_page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_extent`
--

DROP TABLE IF EXISTS `fez_record_search_key_extent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_extent` (
  `rek_extent_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_extent_pid` varchar(64) DEFAULT NULL,
  `rek_extent_xsdmf_id` int(11) DEFAULT NULL,
  `rek_extent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_extent_id`),
  KEY `rek_extent` (`rek_extent`),
  KEY `rek_extent_pid` (`rek_extent_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_extent__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_extent__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_extent__shadow` (
  `rek_extent_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_extent_pid` varchar(64) DEFAULT NULL,
  `rek_extent_xsdmf_id` int(11) DEFAULT NULL,
  `rek_extent` varchar(255) DEFAULT NULL,
  `rek_extent_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_extent_id`),
  UNIQUE KEY `rek_extent_pid_2` (`rek_extent_pid`,`rek_extent_stamp`),
  KEY `rek_extent` (`rek_extent`),
  KEY `rek_extent_pid` (`rek_extent_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_file_attachment_content`
--

DROP TABLE IF EXISTS `fez_record_search_key_file_attachment_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_file_attachment_content` (
  `rek_file_attachment_content_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_file_attachment_content_pid` varchar(64) DEFAULT NULL,
  `rek_file_attachment_content_xsdmf_id` int(11) DEFAULT NULL,
  `rek_file_attachment_content` text,
  `rek_file_attachment_content_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_file_attachment_content_id`),
  UNIQUE KEY `unique_constraint` (`rek_file_attachment_content_pid`,`rek_file_attachment_content_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_file_attachment_content_pid`,`rek_file_attachment_content_order`),
  KEY `rek_file_attachment_content_order` (`rek_file_attachment_content_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_file_attachment_content__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_file_attachment_content__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_file_attachment_content__shadow` (
  `rek_file_attachment_content_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_file_attachment_content_pid` varchar(64) DEFAULT NULL,
  `rek_file_attachment_content_xsdmf_id` int(11) DEFAULT NULL,
  `rek_file_attachment_content` text,
  `rek_file_attachment_content_order` int(11) DEFAULT '1',
  `rek_file_attachment_content_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_file_attachment_content_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_file_attachment_content_pid`,`rek_file_attachment_content_order`),
  UNIQUE KEY `rek_file_attachment_content_pid` (`rek_file_attachment_content_pid`,`rek_file_attachment_content_stamp`),
  KEY `rek_file_attachment_content_order` (`rek_file_attachment_content_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_file_attachment_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_file_attachment_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_file_attachment_name` (
  `rek_file_attachment_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_file_attachment_name_pid` varchar(64) DEFAULT NULL,
  `rek_file_attachment_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_file_attachment_name` varchar(255) DEFAULT NULL,
  `rek_file_attachment_name_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_file_attachment_name_id`),
  UNIQUE KEY `rek_file_attachment_name_pid_unique` (`rek_file_attachment_name_pid`,`rek_file_attachment_name`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_file_attachment_name_pid`,`rek_file_attachment_name_order`),
  KEY `rek_file_attachment_name_id` (`rek_file_attachment_name_pid`),
  KEY `rek_file_attachment_name` (`rek_file_attachment_name`),
  KEY `rek_file_attachment_name_order` (`rek_file_attachment_name_order`)
) ENGINE=InnoDB AUTO_INCREMENT=1602226 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_file_attachment_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_file_attachment_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_file_attachment_name__shadow` (
  `rek_file_attachment_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_file_attachment_name_pid` varchar(64) DEFAULT NULL,
  `rek_file_attachment_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_file_attachment_name` varchar(255) DEFAULT NULL,
  `rek_file_attachment_name_order` int(11) DEFAULT '1',
  `rek_file_attachment_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_file_attachment_name_id`),
  UNIQUE KEY `rek_file_attachment_name_pid_unique` (`rek_file_attachment_name_pid`,`rek_file_attachment_name`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_file_attachment_name_pid`,`rek_file_attachment_name_order`),
  UNIQUE KEY `rek_file_attachment_name_pid` (`rek_file_attachment_name_pid`,`rek_file_attachment_name_stamp`),
  KEY `rek_file_attachment_name_id` (`rek_file_attachment_name_pid`),
  KEY `rek_file_attachment_name` (`rek_file_attachment_name`),
  KEY `rek_file_attachment_name_order` (`rek_file_attachment_name_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_file_description`
--

DROP TABLE IF EXISTS `fez_record_search_key_file_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_file_description` (
  `rek_file_description_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_file_description_pid` varchar(64) DEFAULT NULL,
  `rek_file_description_xsdmf_id` int(11) DEFAULT NULL,
  `rek_file_description_order` int(11) DEFAULT '1',
  `rek_file_description` text,
  PRIMARY KEY (`rek_file_description_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_file_description_pid`,`rek_file_description_order`),
  KEY `rek_file_description_pid` (`rek_file_description_pid`),
  FULLTEXT KEY `rek_file_description` (`rek_file_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_file_description__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_file_description__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_file_description__shadow` (
  `rek_file_description_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_file_description_pid` varchar(64) DEFAULT NULL,
  `rek_file_description_xsdmf_id` int(11) DEFAULT NULL,
  `rek_file_description_order` int(11) DEFAULT '1',
  `rek_file_description` text,
  `rek_file_description_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_file_description_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_file_description_pid`,`rek_file_description_order`),
  UNIQUE KEY `rek_file_description_pid_2` (`rek_file_description_pid`,`rek_file_description_stamp`),
  KEY `rek_file_description_pid` (`rek_file_description_pid`),
  FULLTEXT KEY `rek_file_description` (`rek_file_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_file_downloads`
--

DROP TABLE IF EXISTS `fez_record_search_key_file_downloads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_file_downloads` (
  `rek_file_downloads_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_file_downloads_pid` varchar(64) DEFAULT NULL,
  `rek_file_downloads_xsdmf_id` int(11) DEFAULT NULL,
  `rek_file_downloads` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_file_downloads_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_first_author_in_document_derived`
--

DROP TABLE IF EXISTS `fez_record_search_key_first_author_in_document_derived`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_first_author_in_document_derived` (
  `rek_first_author_in_document_derived_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_first_author_in_document_derived_pid` varchar(64) DEFAULT NULL,
  `rek_first_author_in_document_derived_xsdmf_id` int(11) DEFAULT NULL,
  `rek_first_author_in_document_derived` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_first_author_in_document_derived_id`),
  UNIQUE KEY `unique_pid_value` (`rek_first_author_in_document_derived_pid`,`rek_first_author_in_document_derived`),
  UNIQUE KEY `rek_first_author_in_document_derived_pid` (`rek_first_author_in_document_derived_pid`),
  KEY `rek_first_author_in_document_derived` (`rek_first_author_in_document_derived`)
) ENGINE=InnoDB AUTO_INCREMENT=2944028 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_first_author_in_document_derived__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_first_author_in_document_derived__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_first_author_in_document_derived__shadow` (
  `rek_first_author_in_document_derived_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_first_author_in_document_derived_pid` varchar(64) DEFAULT NULL,
  `rek_first_author_in_document_derived_xsdmf_id` int(11) DEFAULT NULL,
  `rek_first_author_in_document_derived` varchar(255) DEFAULT NULL,
  `rek_first_author_in_document_derived_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_first_author_in_document_derived_id`),
  UNIQUE KEY `unique_pid_value` (`rek_first_author_in_document_derived_pid`,`rek_first_author_in_document_derived`),
  UNIQUE KEY `rek_first_author_in_document_derived_pid` (`rek_first_author_in_document_derived_pid`),
  UNIQUE KEY `rek_first_author_in_document_d_2` (`rek_first_author_in_document_derived_pid`,`rek_first_author_in_document_derived_stamp`),
  KEY `rek_first_author_in_document_derived` (`rek_first_author_in_document_derived`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_first_author_in_fez_derived`
--

DROP TABLE IF EXISTS `fez_record_search_key_first_author_in_fez_derived`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_first_author_in_fez_derived` (
  `rek_first_author_in_fez_derived_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_first_author_in_fez_derived_pid` varchar(64) DEFAULT NULL,
  `rek_first_author_in_fez_derived_xsdmf_id` int(11) DEFAULT NULL,
  `rek_first_author_in_fez_derived` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_first_author_in_fez_derived_id`),
  UNIQUE KEY `rek_first_author_in_fez_derived_pid` (`rek_first_author_in_fez_derived_pid`),
  KEY `rek_first_author_in_fez_derived` (`rek_first_author_in_fez_derived`)
) ENGINE=InnoDB AUTO_INCREMENT=1523485 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_first_author_in_fez_derived__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_first_author_in_fez_derived__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_first_author_in_fez_derived__shadow` (
  `rek_first_author_in_fez_derived_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_first_author_in_fez_derived_pid` varchar(64) DEFAULT NULL,
  `rek_first_author_in_fez_derived_xsdmf_id` int(11) DEFAULT NULL,
  `rek_first_author_in_fez_derived` varchar(255) DEFAULT NULL,
  `rek_first_author_in_fez_derived_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_first_author_in_fez_derived_id`),
  UNIQUE KEY `rek_first_author_in_fez_derived_pid` (`rek_first_author_in_fez_derived_pid`),
  UNIQUE KEY `rek_first_author_in_fez_derive_2` (`rek_first_author_in_fez_derived_pid`,`rek_first_author_in_fez_derived_stamp`),
  KEY `rek_first_author_in_fez_derived` (`rek_first_author_in_fez_derived`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_follow_up_flags`
--

DROP TABLE IF EXISTS `fez_record_search_key_follow_up_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_follow_up_flags` (
  `rek_follow_up_flags_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_follow_up_flags_pid` varchar(64) DEFAULT NULL,
  `rek_follow_up_flags_xsdmf_id` int(11) DEFAULT NULL,
  `rek_follow_up_flags` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_follow_up_flags_id`),
  UNIQUE KEY `rek_follow_up_flags_pid` (`rek_follow_up_flags_pid`),
  KEY `rek_follow_up_flags` (`rek_follow_up_flags`)
) ENGINE=InnoDB AUTO_INCREMENT=390523 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_follow_up_flags__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_follow_up_flags__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_follow_up_flags__shadow` (
  `rek_follow_up_flags_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_follow_up_flags_pid` varchar(64) DEFAULT NULL,
  `rek_follow_up_flags_xsdmf_id` int(11) DEFAULT NULL,
  `rek_follow_up_flags` int(11) DEFAULT NULL,
  `rek_follow_up_flags_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_follow_up_flags_id`),
  UNIQUE KEY `rek_follow_up_flags_pid` (`rek_follow_up_flags_pid`),
  UNIQUE KEY `rek_follow_up_flags_pid_2` (`rek_follow_up_flags_pid`,`rek_follow_up_flags_stamp`),
  KEY `rek_follow_up_flags` (`rek_follow_up_flags`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_follow_up_flags_imu`
--

DROP TABLE IF EXISTS `fez_record_search_key_follow_up_flags_imu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_follow_up_flags_imu` (
  `rek_follow_up_flags_imu_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_follow_up_flags_imu_pid` varchar(64) DEFAULT NULL,
  `rek_follow_up_flags_imu_xsdmf_id` int(11) DEFAULT NULL,
  `rek_follow_up_flags_imu` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_follow_up_flags_imu_id`),
  UNIQUE KEY `rek_follow_up_flags_imu_pid` (`rek_follow_up_flags_imu_pid`),
  KEY `rek_follow_up_flags_imu` (`rek_follow_up_flags_imu`)
) ENGINE=InnoDB AUTO_INCREMENT=272766 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_follow_up_flags_imu__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_follow_up_flags_imu__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_follow_up_flags_imu__shadow` (
  `rek_follow_up_flags_imu_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_follow_up_flags_imu_pid` varchar(64) DEFAULT NULL,
  `rek_follow_up_flags_imu_xsdmf_id` int(11) DEFAULT NULL,
  `rek_follow_up_flags_imu` int(11) DEFAULT NULL,
  `rek_follow_up_flags_imu_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_follow_up_flags_imu_id`),
  UNIQUE KEY `rek_follow_up_flags_imu_pid` (`rek_follow_up_flags_imu_pid`),
  UNIQUE KEY `rek_follow_up_flags_imu_pid_2` (`rek_follow_up_flags_imu_pid`,`rek_follow_up_flags_imu_stamp`),
  KEY `rek_follow_up_flags_imu` (`rek_follow_up_flags_imu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_geographic_area`
--

DROP TABLE IF EXISTS `fez_record_search_key_geographic_area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_geographic_area` (
  `rek_geographic_area_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_geographic_area_pid` varchar(64) DEFAULT NULL,
  `rek_geographic_area_xsdmf_id` int(11) DEFAULT NULL,
  `rek_geographic_area_order` int(11) DEFAULT '1',
  `rek_geographic_area` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_geographic_area_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_geographic_area__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_geographic_area__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_geographic_area__shadow` (
  `rek_geographic_area_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_geographic_area_pid` varchar(64) DEFAULT NULL,
  `rek_geographic_area_xsdmf_id` int(11) DEFAULT NULL,
  `rek_geographic_area_order` int(11) DEFAULT '1',
  `rek_geographic_area` varchar(255) DEFAULT NULL,
  `rek_geographic_area_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_geographic_area_id`),
  UNIQUE KEY `rek_geographic_area_pid` (`rek_geographic_area_pid`,`rek_geographic_area_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_geographic_coordinates`
--

DROP TABLE IF EXISTS `fez_record_search_key_geographic_coordinates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_geographic_coordinates` (
  `rek_geographic_coordinates_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_geographic_coordinates_pid` varchar(64) DEFAULT NULL,
  `rek_geographic_coordinates_xsdmf_id` int(11) DEFAULT NULL,
  `rek_geographic_coordinates_order` int(11) DEFAULT '1',
  `rek_geographic_coordinates` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_geographic_coordinates_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_geographic_coordinates__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_geographic_coordinates__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_geographic_coordinates__shadow` (
  `rek_geographic_coordinates_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_geographic_coordinates_pid` varchar(64) DEFAULT NULL,
  `rek_geographic_coordinates_xsdmf_id` int(11) DEFAULT NULL,
  `rek_geographic_coordinates_order` int(11) DEFAULT '1',
  `rek_geographic_coordinates` varchar(255) DEFAULT NULL,
  `rek_geographic_coordinates_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_geographic_coordinates_id`),
  UNIQUE KEY `rek_geographic_coordinates_pid` (`rek_geographic_coordinates_pid`,`rek_geographic_coordinates_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_herdc_code`
--

DROP TABLE IF EXISTS `fez_record_search_key_herdc_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_herdc_code` (
  `rek_herdc_code_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_herdc_code_pid` varchar(64) DEFAULT NULL,
  `rek_herdc_code_xsdmf_id` int(11) DEFAULT NULL,
  `rek_herdc_code` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_herdc_code_id`),
  UNIQUE KEY `rek_herdc_pid` (`rek_herdc_code_pid`),
  KEY `rek_herdc` (`rek_herdc_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2605172 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_herdc_code__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_herdc_code__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_herdc_code__shadow` (
  `rek_herdc_code_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_herdc_code_pid` varchar(64) DEFAULT NULL,
  `rek_herdc_code_xsdmf_id` int(11) DEFAULT NULL,
  `rek_herdc_code` int(11) DEFAULT NULL,
  `rek_herdc_code_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_herdc_code_id`),
  UNIQUE KEY `rek_herdc_pid` (`rek_herdc_code_pid`),
  UNIQUE KEY `rek_herdc_code_pid` (`rek_herdc_code_pid`,`rek_herdc_code_stamp`),
  KEY `rek_herdc` (`rek_herdc_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_herdc_status`
--

DROP TABLE IF EXISTS `fez_record_search_key_herdc_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_herdc_status` (
  `rek_herdc_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_herdc_status_pid` varchar(64) DEFAULT NULL,
  `rek_herdc_status_xsdmf_id` int(11) DEFAULT NULL,
  `rek_herdc_status` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_herdc_status_id`),
  UNIQUE KEY `rek_herdc_status_pid` (`rek_herdc_status_pid`),
  KEY `rek_herdc_status` (`rek_herdc_status`)
) ENGINE=InnoDB AUTO_INCREMENT=1800420 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_herdc_status__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_herdc_status__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_herdc_status__shadow` (
  `rek_herdc_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_herdc_status_pid` varchar(64) DEFAULT NULL,
  `rek_herdc_status_xsdmf_id` int(11) DEFAULT NULL,
  `rek_herdc_status` int(11) DEFAULT NULL,
  `rek_herdc_status_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_herdc_status_id`),
  UNIQUE KEY `rek_herdc_status_pid` (`rek_herdc_status_pid`),
  UNIQUE KEY `rek_herdc_status_pid_2` (`rek_herdc_status_pid`,`rek_herdc_status_stamp`),
  KEY `rek_herdc_status` (`rek_herdc_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_identifier`
--

DROP TABLE IF EXISTS `fez_record_search_key_identifier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_identifier` (
  `rek_identifier_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_identifier_pid` varchar(64) DEFAULT NULL,
  `rek_identifier_xsdmf_id` int(11) DEFAULT NULL,
  `rek_identifier` varchar(255) DEFAULT NULL,
  `rek_identifier_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_identifier_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_identifier_pid`,`rek_identifier_order`),
  KEY `rek_identifier_pid` (`rek_identifier_pid`),
  KEY `rek_identifier` (`rek_identifier`),
  KEY `rek_identifier_order` (`rek_identifier_order`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_identifier__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_identifier__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_identifier__shadow` (
  `rek_identifier_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_identifier_pid` varchar(64) DEFAULT NULL,
  `rek_identifier_xsdmf_id` int(11) DEFAULT NULL,
  `rek_identifier` varchar(255) DEFAULT NULL,
  `rek_identifier_order` int(11) DEFAULT '1',
  `rek_identifier_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_identifier_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_identifier_pid`,`rek_identifier_order`),
  UNIQUE KEY `rek_identifier_pid_2` (`rek_identifier_pid`,`rek_identifier_stamp`),
  KEY `rek_identifier_pid` (`rek_identifier_pid`),
  KEY `rek_identifier` (`rek_identifier`),
  KEY `rek_identifier_order` (`rek_identifier_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_institutional_status`
--

DROP TABLE IF EXISTS `fez_record_search_key_institutional_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_institutional_status` (
  `rek_institutional_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_institutional_status_pid` varchar(64) DEFAULT NULL,
  `rek_institutional_status_xsdmf_id` int(11) DEFAULT NULL,
  `rek_institutional_status` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_institutional_status_id`),
  UNIQUE KEY `rek_institutional_status_pid` (`rek_institutional_status_pid`),
  KEY `rek_institutional_status` (`rek_institutional_status`)
) ENGINE=InnoDB AUTO_INCREMENT=1699656 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_institutional_status__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_institutional_status__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_institutional_status__shadow` (
  `rek_institutional_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_institutional_status_pid` varchar(64) DEFAULT NULL,
  `rek_institutional_status_xsdmf_id` int(11) DEFAULT NULL,
  `rek_institutional_status` int(11) DEFAULT NULL,
  `rek_institutional_status_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_institutional_status_id`),
  UNIQUE KEY `rek_institutional_status_pid` (`rek_institutional_status_pid`),
  UNIQUE KEY `rek_institutional_status_pid_2` (`rek_institutional_status_pid`,`rek_institutional_status_stamp`),
  KEY `rek_institutional_status` (`rek_institutional_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_interior_features`
--

DROP TABLE IF EXISTS `fez_record_search_key_interior_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_interior_features` (
  `rek_interior_features_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_interior_features_pid` varchar(64) DEFAULT NULL,
  `rek_interior_features_xsdmf_id` int(11) DEFAULT NULL,
  `rek_interior_features_order` int(11) DEFAULT '1',
  `rek_interior_features` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_interior_features_id`),
  UNIQUE KEY `unique_constraint` (`rek_interior_features_pid`,`rek_interior_features_order`,`rek_interior_features`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_interior_features_pid`,`rek_interior_features_order`),
  KEY `rek_interior_features_pid` (`rek_interior_features_pid`),
  FULLTEXT KEY `rek_interior_features` (`rek_interior_features`)
) ENGINE=MyISAM AUTO_INCREMENT=815 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_interior_features__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_interior_features__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_interior_features__shadow` (
  `rek_interior_features_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_interior_features_pid` varchar(64) DEFAULT NULL,
  `rek_interior_features_xsdmf_id` int(11) DEFAULT NULL,
  `rek_interior_features_order` int(11) DEFAULT '1',
  `rek_interior_features` varchar(255) DEFAULT NULL,
  `rek_interior_features_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_interior_features_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_interior_features_pid`,`rek_interior_features_order`),
  UNIQUE KEY `rek_interior_features_pid_2` (`rek_interior_features_pid`,`rek_interior_features_stamp`),
  KEY `rek_interior_features_pid` (`rek_interior_features_pid`),
  FULLTEXT KEY `rek_interior_features` (`rek_interior_features`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_isannotationof`
--

DROP TABLE IF EXISTS `fez_record_search_key_isannotationof`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_isannotationof` (
  `rek_isannotationof_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_isannotationof_pid` varchar(64) DEFAULT NULL,
  `rek_isannotationof_xsdmf_id` int(11) DEFAULT NULL,
  `rek_isannotationof` varchar(64) DEFAULT NULL,
  `rek_isannotationof_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_isannotationof_id`),
  UNIQUE KEY `unique_constraint` (`rek_isannotationof_pid`,`rek_isannotationof`,`rek_isannotationof_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_isannotationof_pid`,`rek_isannotationof_order`),
  KEY `rek_isannotationof_order` (`rek_isannotationof_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_isannotationof__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_isannotationof__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_isannotationof__shadow` (
  `rek_isannotationof_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_isannotationof_pid` varchar(64) DEFAULT NULL,
  `rek_isannotationof_xsdmf_id` int(11) DEFAULT NULL,
  `rek_isannotationof` varchar(64) DEFAULT NULL,
  `rek_isannotationof_order` int(11) DEFAULT '1',
  `rek_isannotationof_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_isannotationof_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_isannotationof_pid`,`rek_isannotationof_order`),
  UNIQUE KEY `rek_isannotationof_pid` (`rek_isannotationof_pid`,`rek_isannotationof_stamp`),
  KEY `rek_isannotationof_order` (`rek_isannotationof_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_isbn`
--

DROP TABLE IF EXISTS `fez_record_search_key_isbn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_isbn` (
  `rek_isbn_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_isbn_pid` varchar(64) DEFAULT NULL,
  `rek_isbn_xsdmf_id` int(11) DEFAULT NULL,
  `rek_isbn` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_isbn_id`),
  UNIQUE KEY `unique_constraint` (`rek_isbn_pid`,`rek_isbn`),
  UNIQUE KEY `rek_isbn_pid` (`rek_isbn_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=600469 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_isbn__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_isbn__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_isbn__shadow` (
  `rek_isbn_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_isbn_pid` varchar(64) DEFAULT NULL,
  `rek_isbn_xsdmf_id` int(11) DEFAULT NULL,
  `rek_isbn` varchar(255) DEFAULT NULL,
  `rek_isbn_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_isbn_id`),
  UNIQUE KEY `rek_isbn_pid` (`rek_isbn_pid`),
  UNIQUE KEY `rek_isbn_pid_2` (`rek_isbn_pid`,`rek_isbn_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_isdatacomponentof`
--

DROP TABLE IF EXISTS `fez_record_search_key_isdatacomponentof`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_isdatacomponentof` (
  `rek_isdatacomponentof_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_isdatacomponentof_pid` varchar(64) DEFAULT NULL,
  `rek_isdatacomponentof_xsdmf_id` int(11) DEFAULT NULL,
  `rek_isdatacomponentof` varchar(64) DEFAULT NULL,
  `rek_isdatacomponentof_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_isdatacomponentof_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_isdatacomponentof_pid`,`rek_isdatacomponentof_order`),
  KEY `rek_isdatacomponentof_order` (`rek_isdatacomponentof_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_isdatacomponentof__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_isdatacomponentof__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_isdatacomponentof__shadow` (
  `rek_isdatacomponentof_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_isdatacomponentof_pid` varchar(64) DEFAULT NULL,
  `rek_isdatacomponentof_xsdmf_id` int(11) DEFAULT NULL,
  `rek_isdatacomponentof` varchar(64) DEFAULT NULL,
  `rek_isdatacomponentof_order` int(11) DEFAULT '1',
  `rek_isdatacomponentof_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_isdatacomponentof_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_isdatacomponentof_pid`,`rek_isdatacomponentof_order`),
  UNIQUE KEY `rek_isdatacomponentof_pid` (`rek_isdatacomponentof_pid`,`rek_isdatacomponentof_stamp`),
  KEY `rek_isdatacomponentof_order` (`rek_isdatacomponentof_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_isderivationof`
--

DROP TABLE IF EXISTS `fez_record_search_key_isderivationof`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_isderivationof` (
  `rek_isderivationof_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_isderivationof_pid` varchar(64) DEFAULT NULL,
  `rek_isderivationof_xsdmf_id` int(11) DEFAULT NULL,
  `rek_isderivationof` varchar(64) DEFAULT NULL,
  `rek_isderivationof_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_isderivationof_id`),
  UNIQUE KEY `unique_constraint` (`rek_isderivationof_pid`,`rek_isderivationof`,`rek_isderivationof_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_isderivationof_pid`,`rek_isderivationof_order`),
  KEY `rek_isderivationof` (`rek_isderivationof`),
  KEY `rek_isderivationof_pid` (`rek_isderivationof_pid`),
  KEY `rek_isderivationof_order` (`rek_isderivationof_order`)
) ENGINE=InnoDB AUTO_INCREMENT=7350 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_isderivationof__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_isderivationof__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_isderivationof__shadow` (
  `rek_isderivationof_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_isderivationof_pid` varchar(64) DEFAULT NULL,
  `rek_isderivationof_xsdmf_id` int(11) DEFAULT NULL,
  `rek_isderivationof` varchar(64) DEFAULT NULL,
  `rek_isderivationof_order` int(11) DEFAULT '1',
  `rek_isderivationof_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_isderivationof_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_isderivationof_pid`,`rek_isderivationof_order`),
  UNIQUE KEY `rek_isderivationof_pid_2` (`rek_isderivationof_pid`,`rek_isderivationof_stamp`),
  KEY `rek_isderivationof` (`rek_isderivationof`),
  KEY `rek_isderivationof_pid` (`rek_isderivationof_pid`),
  KEY `rek_isderivationof_order` (`rek_isderivationof_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_isi_loc`
--

DROP TABLE IF EXISTS `fez_record_search_key_isi_loc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_isi_loc` (
  `rek_isi_loc_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_isi_loc_pid` varchar(64) DEFAULT NULL,
  `rek_isi_loc_xsdmf_id` int(11) DEFAULT NULL,
  `rek_isi_loc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_isi_loc_id`),
  UNIQUE KEY `unique_constraint` (`rek_isi_loc_pid`,`rek_isi_loc`),
  UNIQUE KEY `rek_isi_loc_pid` (`rek_isi_loc_pid`),
  KEY `rek_isi_loc` (`rek_isi_loc`)
) ENGINE=InnoDB AUTO_INCREMENT=2776271 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_isi_loc__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_isi_loc__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_isi_loc__shadow` (
  `rek_isi_loc_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_isi_loc_pid` varchar(64) DEFAULT NULL,
  `rek_isi_loc_xsdmf_id` int(11) DEFAULT NULL,
  `rek_isi_loc` varchar(255) DEFAULT NULL,
  `rek_isi_loc_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_isi_loc_id`),
  UNIQUE KEY `rek_isi_loc_pid` (`rek_isi_loc_pid`),
  UNIQUE KEY `rek_isi_loc_pid_2` (`rek_isi_loc_pid`,`rek_isi_loc_stamp`),
  KEY `rek_isi_loc` (`rek_isi_loc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_ismemberof`
--

DROP TABLE IF EXISTS `fez_record_search_key_ismemberof`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_ismemberof` (
  `rek_ismemberof_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_ismemberof_pid` varchar(64) DEFAULT NULL,
  `rek_ismemberof_xsdmf_id` int(11) DEFAULT NULL,
  `rek_ismemberof` varchar(64) DEFAULT NULL,
  `rek_ismemberof_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_ismemberof_id`),
  UNIQUE KEY `unique_constraint` (`rek_ismemberof_pid`,`rek_ismemberof`,`rek_ismemberof_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_ismemberof_pid`,`rek_ismemberof_order`),
  KEY `rek_ismemberof_pid_value` (`rek_ismemberof_pid`,`rek_ismemberof`),
  KEY `rek_ismemberof_pid` (`rek_ismemberof`),
  KEY `rek_ismemberof_order` (`rek_ismemberof_order`)
) ENGINE=InnoDB AUTO_INCREMENT=7432784 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_ismemberof__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_ismemberof__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_ismemberof__shadow` (
  `rek_ismemberof_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_ismemberof_pid` varchar(64) DEFAULT NULL,
  `rek_ismemberof_xsdmf_id` int(11) DEFAULT NULL,
  `rek_ismemberof` varchar(64) DEFAULT NULL,
  `rek_ismemberof_order` int(11) DEFAULT '1',
  `rek_ismemberof_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_ismemberof_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_ismemberof_pid`,`rek_ismemberof_order`),
  UNIQUE KEY `rek_ismemberof_pid_2` (`rek_ismemberof_pid`,`rek_ismemberof_stamp`),
  KEY `rek_ismemberof_pid_value` (`rek_ismemberof_pid`,`rek_ismemberof`),
  KEY `rek_ismemberof_pid` (`rek_ismemberof`),
  KEY `rek_ismemberof_order` (`rek_ismemberof_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_issn`
--

DROP TABLE IF EXISTS `fez_record_search_key_issn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_issn` (
  `rek_issn_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_issn_pid` varchar(64) DEFAULT NULL,
  `rek_issn_xsdmf_id` int(11) DEFAULT NULL,
  `rek_issn` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_issn_id`),
  UNIQUE KEY `unique_constraint` (`rek_issn_pid`,`rek_issn`),
  UNIQUE KEY `rek_issn_pid` (`rek_issn_pid`),
  KEY `rek_issn` (`rek_issn`)
) ENGINE=InnoDB AUTO_INCREMENT=2845785 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_issn__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_issn__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_issn__shadow` (
  `rek_issn_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_issn_pid` varchar(64) DEFAULT NULL,
  `rek_issn_xsdmf_id` int(11) DEFAULT NULL,
  `rek_issn` varchar(255) DEFAULT NULL,
  `rek_issn_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_issn_id`),
  UNIQUE KEY `rek_issn_pid` (`rek_issn_pid`),
  UNIQUE KEY `rek_issn_pid_2` (`rek_issn_pid`,`rek_issn_stamp`),
  KEY `rek_issn` (`rek_issn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_issue_number`
--

DROP TABLE IF EXISTS `fez_record_search_key_issue_number`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_issue_number` (
  `rek_issue_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_issue_number_pid` varchar(64) DEFAULT NULL,
  `rek_issue_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_issue_number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_issue_number_id`),
  UNIQUE KEY `unique_constraint` (`rek_issue_number_pid`,`rek_issue_number`),
  UNIQUE KEY `rek_issue_number_pid` (`rek_issue_number_pid`),
  KEY `rek_issue_number` (`rek_issue_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3006115 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_issue_number__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_issue_number__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_issue_number__shadow` (
  `rek_issue_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_issue_number_pid` varchar(64) DEFAULT NULL,
  `rek_issue_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_issue_number` varchar(255) DEFAULT NULL,
  `rek_issue_number_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_issue_number_id`),
  UNIQUE KEY `rek_issue_number_pid` (`rek_issue_number_pid`),
  UNIQUE KEY `rek_issue_number_pid_2` (`rek_issue_number_pid`,`rek_issue_number_stamp`),
  KEY `rek_issue_number` (`rek_issue_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_journal_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_journal_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_journal_name` (
  `rek_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_journal_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_journal_name_id`),
  UNIQUE KEY `unique_constraint` (`rek_journal_name_pid`,`rek_journal_name`),
  UNIQUE KEY `rek_journal_name_pid` (`rek_journal_name_pid`),
  KEY `rek_journal_name` (`rek_journal_name`),
  FULLTEXT KEY `rek_journal_name_ft` (`rek_journal_name`)
) ENGINE=MyISAM AUTO_INCREMENT=3358005 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_journal_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_journal_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_journal_name__shadow` (
  `rek_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_journal_name` varchar(255) DEFAULT NULL,
  `rek_journal_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_journal_name_id`),
  UNIQUE KEY `rek_journal_name_pid` (`rek_journal_name_pid`),
  UNIQUE KEY `rek_journal_name_pid_2` (`rek_journal_name_pid`,`rek_journal_name_stamp`),
  KEY `rek_journal_name` (`rek_journal_name`),
  FULLTEXT KEY `rek_journal_name_ft` (`rek_journal_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_journal_name_copy`
--

DROP TABLE IF EXISTS `fez_record_search_key_journal_name_copy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_journal_name_copy` (
  `rek_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_journal_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_journal_name_id`),
  UNIQUE KEY `unique_constraint` (`rek_journal_name_pid`,`rek_journal_name`),
  UNIQUE KEY `rek_journal_name_pid` (`rek_journal_name_pid`),
  KEY `rek_journal_name` (`rek_journal_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2158803 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_keywords`
--

DROP TABLE IF EXISTS `fez_record_search_key_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_keywords` (
  `rek_keywords_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_keywords_pid` varchar(64) DEFAULT NULL,
  `rek_keywords_xsdmf_id` int(11) DEFAULT NULL,
  `rek_keywords` varchar(255) DEFAULT NULL,
  `rek_keywords_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_keywords_id`),
  UNIQUE KEY `id_pid_combo` (`rek_keywords_id`,`rek_keywords_pid`),
  UNIQUE KEY `unique_constraint` (`rek_keywords_pid`,`rek_keywords`,`rek_keywords_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_keywords_pid`,`rek_keywords_order`),
  KEY `rek_keywords_pid` (`rek_keywords_pid`),
  KEY `rek_keywords` (`rek_keywords`),
  KEY `rek_keywords_order` (`rek_keywords_order`),
  FULLTEXT KEY `rek_keywords_fulltext` (`rek_keywords`)
) ENGINE=MyISAM AUTO_INCREMENT=20633793 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_keywords__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_keywords__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_keywords__shadow` (
  `rek_keywords_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_keywords_pid` varchar(64) DEFAULT NULL,
  `rek_keywords_xsdmf_id` int(11) DEFAULT NULL,
  `rek_keywords` varchar(255) DEFAULT NULL,
  `rek_keywords_order` int(11) DEFAULT '1',
  `rek_keywords_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_keywords_id`),
  UNIQUE KEY `id_pid_combo` (`rek_keywords_id`,`rek_keywords_pid`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_keywords_pid`,`rek_keywords_order`),
  UNIQUE KEY `rek_keywords_pid_2` (`rek_keywords_pid`,`rek_keywords_stamp`),
  KEY `rek_keywords_pid` (`rek_keywords_pid`),
  KEY `rek_keywords` (`rek_keywords`),
  KEY `rek_keywords_order` (`rek_keywords_order`),
  FULLTEXT KEY `rek_keywords_fulltext` (`rek_keywords`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language`
--

DROP TABLE IF EXISTS `fez_record_search_key_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language` (
  `rek_language_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_pid` varchar(64) DEFAULT NULL,
  `rek_language_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `rek_language_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_language_id`),
  UNIQUE KEY `unique_constraint` (`rek_language_pid`,`rek_language`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_language_pid`,`rek_language_order`),
  KEY `rek_language` (`rek_language`),
  KEY `rek_language_pid` (`rek_language_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=3080672 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_language__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language__shadow` (
  `rek_language_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_pid` varchar(64) DEFAULT NULL,
  `rek_language_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `rek_language_order` int(11) DEFAULT '1',
  `rek_language_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_language_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_language_pid`,`rek_language_order`),
  UNIQUE KEY `rek_language_pid_2` (`rek_language_pid`,`rek_language_stamp`),
  KEY `rek_language` (`rek_language`),
  KEY `rek_language_pid` (`rek_language_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language_of_book_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_language_of_book_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language_of_book_title` (
  `rek_language_of_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_of_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_language_of_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language_of_book_title_order` int(11) DEFAULT '1',
  `rek_language_of_book_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`rek_language_of_book_title_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_language_of_book_title_pid`,`rek_language_of_book_title_order`),
  KEY `rek_language_of_book_title` (`rek_language_of_book_title`),
  KEY `rek_language_of_book_title_pid` (`rek_language_of_book_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=1478 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language_of_book_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_language_of_book_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language_of_book_title__shadow` (
  `rek_language_of_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_of_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_language_of_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language_of_book_title_order` int(11) DEFAULT '1',
  `rek_language_of_book_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `rek_language_of_book_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_language_of_book_title_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_language_of_book_title_pid`,`rek_language_of_book_title_order`),
  UNIQUE KEY `rek_language_of_book_title_pid_2` (`rek_language_of_book_title_pid`,`rek_language_of_book_title_stamp`),
  KEY `rek_language_of_book_title` (`rek_language_of_book_title`),
  KEY `rek_language_of_book_title_pid` (`rek_language_of_book_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language_of_journal_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_language_of_journal_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language_of_journal_name` (
  `rek_language_of_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_of_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_language_of_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language_of_journal_name_order` int(11) DEFAULT '1',
  `rek_language_of_journal_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`rek_language_of_journal_name_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_language_of_journal_name_pid`,`rek_language_of_journal_name_order`),
  KEY `rek_language_of_journal_name` (`rek_language_of_journal_name`),
  KEY `rek_language_of_journal_name_pid` (`rek_language_of_journal_name_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=8289 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language_of_journal_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_language_of_journal_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language_of_journal_name__shadow` (
  `rek_language_of_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_of_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_language_of_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language_of_journal_name_order` int(11) DEFAULT '1',
  `rek_language_of_journal_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `rek_language_of_journal_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_language_of_journal_name_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_language_of_journal_name_pid`,`rek_language_of_journal_name_order`),
  UNIQUE KEY `rek_language_of_journal_name_p_2` (`rek_language_of_journal_name_pid`,`rek_language_of_journal_name_stamp`),
  KEY `rek_language_of_journal_name` (`rek_language_of_journal_name`),
  KEY `rek_language_of_journal_name_pid` (`rek_language_of_journal_name_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language_of_parent_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_language_of_parent_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language_of_parent_title` (
  `rek_language_of_parent_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_of_parent_title_pid` varchar(64) DEFAULT NULL,
  `rek_language_of_parent_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language_of_parent_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`rek_language_of_parent_title_id`),
  UNIQUE KEY `rek_language_of_parent_title_pid` (`rek_language_of_parent_title_pid`),
  KEY `rek_language_of_parent_title` (`rek_language_of_parent_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language_of_parent_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_language_of_parent_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language_of_parent_title__shadow` (
  `rek_language_of_parent_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_of_parent_title_pid` varchar(64) DEFAULT NULL,
  `rek_language_of_parent_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language_of_parent_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `rek_language_of_parent_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_language_of_parent_title_id`),
  UNIQUE KEY `rek_language_of_parent_title_pid` (`rek_language_of_parent_title_pid`),
  UNIQUE KEY `rek_language_of_parent_title_p_2` (`rek_language_of_parent_title_pid`,`rek_language_of_parent_title_stamp`),
  KEY `rek_language_of_parent_title` (`rek_language_of_parent_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language_of_proceedings_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_language_of_proceedings_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language_of_proceedings_title` (
  `rek_language_of_proceedings_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_of_proceedings_title_pid` varchar(64) DEFAULT NULL,
  `rek_language_of_proceedings_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language_of_proceedings_title_order` int(11) DEFAULT '1',
  `rek_language_of_proceedings_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`rek_language_of_proceedings_title_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_language_of_proceedings_title_pid`,`rek_language_of_proceedings_title_order`),
  KEY `rek_language_of_proceedings_title` (`rek_language_of_proceedings_title`),
  KEY `rek_language_of_proceedings_title_pid` (`rek_language_of_proceedings_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=2342 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language_of_proceedings_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_language_of_proceedings_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language_of_proceedings_title__shadow` (
  `rek_language_of_proceedings_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_of_proceedings_title_pid` varchar(64) DEFAULT NULL,
  `rek_language_of_proceedings_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language_of_proceedings_title_order` int(11) DEFAULT '1',
  `rek_language_of_proceedings_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `rek_language_of_proceedings_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_language_of_proceedings_title_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_language_of_proceedings_title_pid`,`rek_language_of_proceedings_title_order`),
  UNIQUE KEY `rek_language_of_proceedings_ti_2` (`rek_language_of_proceedings_title_pid`,`rek_language_of_proceedings_title_stamp`),
  KEY `rek_language_of_proceedings_title` (`rek_language_of_proceedings_title`),
  KEY `rek_language_of_proceedings_title_pid` (`rek_language_of_proceedings_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language_of_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_language_of_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language_of_title` (
  `rek_language_of_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_of_title_pid` varchar(64) DEFAULT NULL,
  `rek_language_of_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language_of_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `rek_language_of_title_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_language_of_title_id`),
  UNIQUE KEY `unique_constraint` (`rek_language_of_title_pid`,`rek_language_of_title`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_language_of_title_pid`,`rek_language_of_title_order`),
  KEY `rek_language_of_title` (`rek_language_of_title`),
  KEY `rek_language_of_title_pid` (`rek_language_of_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=9857 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_language_of_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_language_of_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_language_of_title__shadow` (
  `rek_language_of_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_language_of_title_pid` varchar(64) DEFAULT NULL,
  `rek_language_of_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_language_of_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `rek_language_of_title_order` int(11) DEFAULT '1',
  `rek_language_of_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_language_of_title_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_language_of_title_pid`,`rek_language_of_title_order`),
  UNIQUE KEY `rek_language_of_title_pid_2` (`rek_language_of_title_pid`,`rek_language_of_title_stamp`),
  KEY `rek_language_of_title` (`rek_language_of_title`),
  KEY `rek_language_of_title_pid` (`rek_language_of_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_link`
--

DROP TABLE IF EXISTS `fez_record_search_key_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_link` (
  `rek_link_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_link_pid` varchar(64) DEFAULT NULL,
  `rek_link_xsdmf_id` int(11) DEFAULT NULL,
  `rek_link` text,
  `rek_link_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_link_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_link_pid`,`rek_link_order`),
  KEY `rek_link_pid` (`rek_link_pid`),
  KEY `rek_link_order` (`rek_link_order`)
) ENGINE=InnoDB AUTO_INCREMENT=2838023 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_link__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_link__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_link__shadow` (
  `rek_link_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_link_pid` varchar(64) DEFAULT NULL,
  `rek_link_xsdmf_id` int(11) DEFAULT NULL,
  `rek_link` text,
  `rek_link_order` int(11) DEFAULT '1',
  `rek_link_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_link_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_link_pid`,`rek_link_order`),
  UNIQUE KEY `rek_link_pid_2` (`rek_link_pid`,`rek_link_stamp`),
  KEY `rek_link_pid` (`rek_link_pid`),
  KEY `rek_link_order` (`rek_link_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_link_description`
--

DROP TABLE IF EXISTS `fez_record_search_key_link_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_link_description` (
  `rek_link_description_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_link_description_pid` varchar(64) DEFAULT NULL,
  `rek_link_description_xsdmf_id` int(11) DEFAULT NULL,
  `rek_link_description` text,
  `rek_link_description_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_link_description_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_link_description_pid`,`rek_link_description_order`),
  KEY `rek_link_description_pid` (`rek_link_description_pid`),
  KEY `rek_link_description_order` (`rek_link_description_order`)
) ENGINE=InnoDB AUTO_INCREMENT=2838003 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_link_description__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_link_description__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_link_description__shadow` (
  `rek_link_description_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_link_description_pid` varchar(64) DEFAULT NULL,
  `rek_link_description_xsdmf_id` int(11) DEFAULT NULL,
  `rek_link_description` text,
  `rek_link_description_order` int(11) DEFAULT '1',
  `rek_link_description_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_link_description_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_link_description_pid`,`rek_link_description_order`),
  UNIQUE KEY `rek_link_description_pid_2` (`rek_link_description_pid`,`rek_link_description_stamp`),
  KEY `rek_link_description_pid` (`rek_link_description_pid`),
  KEY `rek_link_description_order` (`rek_link_description_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `fez_record_search_key_link_link_description`
--

DROP TABLE IF EXISTS `fez_record_search_key_link_link_description`;
/*!50001 DROP VIEW IF EXISTS `fez_record_search_key_link_link_description`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `fez_record_search_key_link_link_description` (
  `rek_link_id` int(11),
  `rek_link_pid` varchar(64),
  `rek_link_xsdmf_id` int(11),
  `rek_link` text,
  `rek_link_order` int(11),
  `rek_link_description_id` int(11),
  `rek_link_description_pid` varchar(64),
  `rek_link_description_xsdmf_id` int(11),
  `rek_link_description` text,
  `rek_link_description_order` int(11)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `fez_record_search_key_loc_subject_heading`
--

DROP TABLE IF EXISTS `fez_record_search_key_loc_subject_heading`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_loc_subject_heading` (
  `rek_loc_subject_heading_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_loc_subject_heading_pid` varchar(64) DEFAULT NULL,
  `rek_loc_subject_heading_xsdmf_id` int(11) DEFAULT NULL,
  `rek_loc_subject_heading_order` int(11) DEFAULT '1',
  `rek_loc_subject_heading` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_loc_subject_heading_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_loc_subject_heading__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_loc_subject_heading__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_loc_subject_heading__shadow` (
  `rek_loc_subject_heading_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_loc_subject_heading_pid` varchar(64) DEFAULT NULL,
  `rek_loc_subject_heading_xsdmf_id` int(11) DEFAULT NULL,
  `rek_loc_subject_heading_order` int(11) DEFAULT '1',
  `rek_loc_subject_heading` varchar(255) DEFAULT NULL,
  `rek_loc_subject_heading_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_loc_subject_heading_id`),
  UNIQUE KEY `rek_loc_subject_heading_pid` (`rek_loc_subject_heading_pid`,`rek_loc_subject_heading_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_location`
--

DROP TABLE IF EXISTS `fez_record_search_key_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_location` (
  `rek_location_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_location_pid` varchar(64) DEFAULT NULL,
  `rek_location_xsdmf_id` int(11) DEFAULT NULL,
  `rek_location` varchar(255) DEFAULT NULL,
  `rek_location_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_location_id`),
  UNIQUE KEY `unique_constraint` (`rek_location_pid`,`rek_location`,`rek_location_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_location_pid`,`rek_location_order`),
  KEY `rek_location` (`rek_location`),
  KEY `rek_location_pid` (`rek_location_pid`),
  FULLTEXT KEY `rek_location_ft` (`rek_location`)
) ENGINE=MyISAM AUTO_INCREMENT=19527 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_location__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_location__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_location__shadow` (
  `rek_location_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_location_pid` varchar(64) DEFAULT NULL,
  `rek_location_xsdmf_id` int(11) DEFAULT NULL,
  `rek_location` varchar(255) DEFAULT NULL,
  `rek_location_order` int(11) DEFAULT NULL,
  `rek_location_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_location_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_location_pid`,`rek_location_order`),
  UNIQUE KEY `rek_location_pid_2` (`rek_location_pid`,`rek_location_stamp`),
  KEY `rek_location` (`rek_location`),
  KEY `rek_location_pid` (`rek_location_pid`),
  FULLTEXT KEY `rek_location_ft` (`rek_location`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_na_explanation`
--

DROP TABLE IF EXISTS `fez_record_search_key_na_explanation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_na_explanation` (
  `rek_na_explanation_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_na_explanation_pid` varchar(64) DEFAULT NULL,
  `rek_na_explanation_xsdmf_id` int(11) DEFAULT NULL,
  `rek_na_explanation` text,
  PRIMARY KEY (`rek_na_explanation_id`),
  UNIQUE KEY `rek_na_explanation_pid` (`rek_na_explanation_pid`),
  FULLTEXT KEY `rek_na_explanation` (`rek_na_explanation`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_na_explanation__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_na_explanation__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_na_explanation__shadow` (
  `rek_na_explanation_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_na_explanation_pid` varchar(64) DEFAULT NULL,
  `rek_na_explanation_xsdmf_id` int(11) DEFAULT NULL,
  `rek_na_explanation` text,
  `rek_na_explanation_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_na_explanation_id`),
  UNIQUE KEY `rek_na_explanation_pid` (`rek_na_explanation_pid`),
  UNIQUE KEY `rek_na_explanation_pid_2` (`rek_na_explanation_pid`,`rek_na_explanation_stamp`),
  FULLTEXT KEY `rek_na_explanation` (`rek_na_explanation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_native_script_book_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_native_script_book_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_native_script_book_title` (
  `rek_native_script_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_native_script_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_native_script_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_native_script_book_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_native_script_book_title_id`),
  KEY `rek_native_script_book_title` (`rek_native_script_book_title`),
  KEY `rek_native_script_book_title_pid` (`rek_native_script_book_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=630 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_native_script_book_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_native_script_book_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_native_script_book_title__shadow` (
  `rek_native_script_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_native_script_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_native_script_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_native_script_book_title` varchar(255) DEFAULT NULL,
  `rek_native_script_book_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_native_script_book_title_id`),
  UNIQUE KEY `rek_native_script_book_title_p_2` (`rek_native_script_book_title_pid`,`rek_native_script_book_title_stamp`),
  KEY `rek_native_script_book_title` (`rek_native_script_book_title`),
  KEY `rek_native_script_book_title_pid` (`rek_native_script_book_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_native_script_conference_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_native_script_conference_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_native_script_conference_name` (
  `rek_native_script_conference_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_native_script_conference_name_pid` varchar(64) DEFAULT NULL,
  `rek_native_script_conference_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_native_script_conference_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_native_script_conference_name_id`),
  KEY `rek_native_script_conference_name` (`rek_native_script_conference_name`),
  KEY `rek_native_script_conference_name_pid` (`rek_native_script_conference_name_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_native_script_conference_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_native_script_conference_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_native_script_conference_name__shadow` (
  `rek_native_script_conference_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_native_script_conference_name_pid` varchar(64) DEFAULT NULL,
  `rek_native_script_conference_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_native_script_conference_name` varchar(255) DEFAULT NULL,
  `rek_native_script_conference_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_native_script_conference_name_id`),
  UNIQUE KEY `rek_native_script_conference_n_2` (`rek_native_script_conference_name_pid`,`rek_native_script_conference_name_stamp`),
  KEY `rek_native_script_conference_name` (`rek_native_script_conference_name`),
  KEY `rek_native_script_conference_name_pid` (`rek_native_script_conference_name_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_native_script_journal_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_native_script_journal_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_native_script_journal_name` (
  `rek_native_script_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_native_script_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_native_script_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_native_script_journal_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_native_script_journal_name_id`),
  KEY `rek_native_script_journal_name` (`rek_native_script_journal_name`),
  KEY `rek_native_script_journal_name_pid` (`rek_native_script_journal_name_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=1099 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_native_script_journal_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_native_script_journal_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_native_script_journal_name__shadow` (
  `rek_native_script_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_native_script_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_native_script_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_native_script_journal_name` varchar(255) DEFAULT NULL,
  `rek_native_script_journal_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_native_script_journal_name_id`),
  UNIQUE KEY `rek_native_script_journal_name_2` (`rek_native_script_journal_name_pid`,`rek_native_script_journal_name_stamp`),
  KEY `rek_native_script_journal_name` (`rek_native_script_journal_name`),
  KEY `rek_native_script_journal_name_pid` (`rek_native_script_journal_name_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_native_script_proceedings_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_native_script_proceedings_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_native_script_proceedings_title` (
  `rek_native_script_proceedings_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_native_script_proceedings_title_pid` varchar(64) DEFAULT NULL,
  `rek_native_script_proceedings_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_native_script_proceedings_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_native_script_proceedings_title_id`),
  KEY `rek_native_script_proceedings_title` (`rek_native_script_proceedings_title`),
  KEY `rek_native_script_proceedings_title_pid` (`rek_native_script_proceedings_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_native_script_proceedings_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_native_script_proceedings_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_native_script_proceedings_title__shadow` (
  `rek_native_script_proceedings_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_native_script_proceedings_title_pid` varchar(64) DEFAULT NULL,
  `rek_native_script_proceedings_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_native_script_proceedings_title` varchar(255) DEFAULT NULL,
  `rek_native_script_proceedings_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_native_script_proceedings_title_id`),
  UNIQUE KEY `rek_native_script_proceedings__2` (`rek_native_script_proceedings_title_pid`,`rek_native_script_proceedings_title_stamp`),
  KEY `rek_native_script_proceedings_title` (`rek_native_script_proceedings_title`),
  KEY `rek_native_script_proceedings_title_pid` (`rek_native_script_proceedings_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_native_script_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_native_script_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_native_script_title` (
  `rek_native_script_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_native_script_title_pid` varchar(64) DEFAULT NULL,
  `rek_native_script_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_native_script_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_native_script_title_id`),
  KEY `rek_native_script_title` (`rek_native_script_title`),
  KEY `rek_native_script_title_pid` (`rek_native_script_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=1528 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_native_script_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_native_script_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_native_script_title__shadow` (
  `rek_native_script_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_native_script_title_pid` varchar(64) DEFAULT NULL,
  `rek_native_script_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_native_script_title` varchar(255) DEFAULT NULL,
  `rek_native_script_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_native_script_title_id`),
  UNIQUE KEY `rek_native_script_title_pid_2` (`rek_native_script_title_pid`,`rek_native_script_title_stamp`),
  KEY `rek_native_script_title` (`rek_native_script_title`),
  KEY `rek_native_script_title_pid` (`rek_native_script_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_newspaper`
--

DROP TABLE IF EXISTS `fez_record_search_key_newspaper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_newspaper` (
  `rek_newspaper_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_newspaper_pid` varchar(64) DEFAULT NULL,
  `rek_newspaper_xsdmf_id` int(11) DEFAULT NULL,
  `rek_newspaper` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_newspaper_id`),
  UNIQUE KEY `unique_constraint` (`rek_newspaper_pid`,`rek_newspaper`),
  UNIQUE KEY `rek_newspaper_pid` (`rek_newspaper_pid`),
  KEY `rek_newspaper` (`rek_newspaper`),
  FULLTEXT KEY `rek_newspaper_ft` (`rek_newspaper`)
) ENGINE=MyISAM AUTO_INCREMENT=1356 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_newspaper__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_newspaper__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_newspaper__shadow` (
  `rek_newspaper_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_newspaper_pid` varchar(64) DEFAULT NULL,
  `rek_newspaper_xsdmf_id` int(11) DEFAULT NULL,
  `rek_newspaper` varchar(255) DEFAULT NULL,
  `rek_newspaper_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_newspaper_id`),
  UNIQUE KEY `rek_newspaper_pid` (`rek_newspaper_pid`),
  UNIQUE KEY `rek_newspaper_pid_2` (`rek_newspaper_pid`,`rek_newspaper_stamp`),
  KEY `rek_newspaper` (`rek_newspaper`),
  FULLTEXT KEY `rek_newspaper_ft` (`rek_newspaper`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_notes`
--

DROP TABLE IF EXISTS `fez_record_search_key_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_notes` (
  `rek_notes_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_notes_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_notes_xsdmf_id` int(11) DEFAULT NULL,
  `rek_notes` text,
  PRIMARY KEY (`rek_notes_id`),
  UNIQUE KEY `rek_notes_pid` (`rek_notes_pid`),
  FULLTEXT KEY `rek_notes` (`rek_notes`)
) ENGINE=MyISAM AUTO_INCREMENT=609336 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_notes__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_notes__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_notes__shadow` (
  `rek_notes_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_notes_pid` varchar(64) NOT NULL DEFAULT '',
  `rek_notes_xsdmf_id` int(11) DEFAULT NULL,
  `rek_notes` text,
  `rek_notes_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_notes_id`),
  UNIQUE KEY `rek_notes_pid` (`rek_notes_pid`),
  UNIQUE KEY `rek_notes_pid_2` (`rek_notes_pid`,`rek_notes_stamp`),
  FULLTEXT KEY `rek_notes` (`rek_notes`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_org_id`
--

DROP TABLE IF EXISTS `fez_record_search_key_org_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_org_id` (
  `rek_org_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_org_id_pid` varchar(64) DEFAULT NULL,
  `rek_org_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_org_id_order` int(11) DEFAULT '1',
  `rek_org_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_org_id_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_org_id__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_org_id__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_org_id__shadow` (
  `rek_org_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_org_id_pid` varchar(64) DEFAULT NULL,
  `rek_org_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_org_id_order` int(11) DEFAULT '1',
  `rek_org_id` varchar(255) DEFAULT NULL,
  `rek_org_id_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_org_id_id`),
  UNIQUE KEY `rek_org_id_pid` (`rek_org_id_pid`,`rek_org_id_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_org_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_org_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_org_name` (
  `rek_org_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_org_name_pid` varchar(64) DEFAULT NULL,
  `rek_org_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_org_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_org_name_id`),
  UNIQUE KEY `unique_constraint` (`rek_org_name_pid`,`rek_org_name`),
  UNIQUE KEY `rek_org_name_pid` (`rek_org_name_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=155734 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_org_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_org_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_org_name__shadow` (
  `rek_org_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_org_name_pid` varchar(64) DEFAULT NULL,
  `rek_org_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_org_name` varchar(255) DEFAULT NULL,
  `rek_org_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_org_name_id`),
  UNIQUE KEY `rek_org_name_pid` (`rek_org_name_pid`),
  UNIQUE KEY `rek_org_name_pid_2` (`rek_org_name_pid`,`rek_org_name_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_org_role`
--

DROP TABLE IF EXISTS `fez_record_search_key_org_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_org_role` (
  `rek_org_role_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_org_role_pid` varchar(64) DEFAULT NULL,
  `rek_org_role_xsdmf_id` int(11) DEFAULT NULL,
  `rek_org_role_order` int(11) DEFAULT '1',
  `rek_org_role` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_org_role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_org_role__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_org_role__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_org_role__shadow` (
  `rek_org_role_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_org_role_pid` varchar(64) DEFAULT NULL,
  `rek_org_role_xsdmf_id` int(11) DEFAULT NULL,
  `rek_org_role_order` int(11) DEFAULT '1',
  `rek_org_role` varchar(255) DEFAULT NULL,
  `rek_org_role_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_org_role_id`),
  UNIQUE KEY `rek_org_role_pid` (`rek_org_role_pid`,`rek_org_role_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_org_unit_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_org_unit_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_org_unit_name` (
  `rek_org_unit_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_org_unit_name_pid` varchar(64) DEFAULT NULL,
  `rek_org_unit_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_org_unit_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_org_unit_name_id`),
  UNIQUE KEY `unique_constraint` (`rek_org_unit_name_pid`,`rek_org_unit_name`),
  UNIQUE KEY `rek_org_unit_name_pid` (`rek_org_unit_name_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=144974 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_org_unit_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_org_unit_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_org_unit_name__shadow` (
  `rek_org_unit_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_org_unit_name_pid` varchar(64) DEFAULT NULL,
  `rek_org_unit_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_org_unit_name` varchar(255) DEFAULT NULL,
  `rek_org_unit_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_org_unit_name_id`),
  UNIQUE KEY `rek_org_unit_name_pid` (`rek_org_unit_name_pid`),
  UNIQUE KEY `rek_org_unit_name_pid_2` (`rek_org_unit_name_pid`,`rek_org_unit_name_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_output_availability`
--

DROP TABLE IF EXISTS `fez_record_search_key_output_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_output_availability` (
  `rek_output_availability_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_output_availability_pid` varchar(64) DEFAULT NULL,
  `rek_output_availability_xsdmf_id` int(11) DEFAULT NULL,
  `rek_output_availability` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`rek_output_availability_id`),
  UNIQUE KEY `unique_constraint` (`rek_output_availability_pid`,`rek_output_availability`),
  UNIQUE KEY `rek_output_availability_pid` (`rek_output_availability_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=2527 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_output_availability__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_output_availability__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_output_availability__shadow` (
  `rek_output_availability_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_output_availability_pid` varchar(64) DEFAULT NULL,
  `rek_output_availability_xsdmf_id` int(11) DEFAULT NULL,
  `rek_output_availability` varchar(1) DEFAULT NULL,
  `rek_output_availability_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_output_availability_id`),
  UNIQUE KEY `rek_output_availability_pid` (`rek_output_availability_pid`),
  UNIQUE KEY `rek_output_availability_pid_2` (`rek_output_availability_pid`,`rek_output_availability_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_parent_publication`
--

DROP TABLE IF EXISTS `fez_record_search_key_parent_publication`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_parent_publication` (
  `rek_parent_publication_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_parent_publication_pid` varchar(64) DEFAULT NULL,
  `rek_parent_publication_xsdmf_id` int(11) DEFAULT NULL,
  `rek_parent_publication` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_parent_publication_id`),
  UNIQUE KEY `unique_constraint` (`rek_parent_publication_pid`,`rek_parent_publication`),
  UNIQUE KEY `rek_parent_publication_pid` (`rek_parent_publication_pid`),
  KEY `rek_parent_publication` (`rek_parent_publication`),
  FULLTEXT KEY `rek_parent_publication_ft` (`rek_parent_publication`)
) ENGINE=MyISAM AUTO_INCREMENT=3044 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_parent_publication__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_parent_publication__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_parent_publication__shadow` (
  `rek_parent_publication_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_parent_publication_pid` varchar(64) DEFAULT NULL,
  `rek_parent_publication_xsdmf_id` int(11) DEFAULT NULL,
  `rek_parent_publication` varchar(255) DEFAULT NULL,
  `rek_parent_publication_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_parent_publication_id`),
  UNIQUE KEY `rek_parent_publication_pid` (`rek_parent_publication_pid`),
  UNIQUE KEY `rek_parent_publication_pid_2` (`rek_parent_publication_pid`,`rek_parent_publication_stamp`),
  KEY `rek_parent_publication` (`rek_parent_publication`),
  FULLTEXT KEY `rek_parent_publication_ft` (`rek_parent_publication`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_patent_number`
--

DROP TABLE IF EXISTS `fez_record_search_key_patent_number`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_patent_number` (
  `rek_patent_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_patent_number_pid` varchar(64) DEFAULT NULL,
  `rek_patent_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_patent_number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_patent_number_id`),
  UNIQUE KEY `unique_constraint` (`rek_patent_number_pid`,`rek_patent_number`),
  UNIQUE KEY `rek_patent_number_pid` (`rek_patent_number_pid`),
  KEY `rek_patent_number` (`rek_patent_number`)
) ENGINE=InnoDB AUTO_INCREMENT=1625 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_patent_number__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_patent_number__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_patent_number__shadow` (
  `rek_patent_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_patent_number_pid` varchar(64) DEFAULT NULL,
  `rek_patent_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_patent_number` varchar(255) DEFAULT NULL,
  `rek_patent_number_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_patent_number_id`),
  UNIQUE KEY `rek_patent_number_pid` (`rek_patent_number_pid`),
  UNIQUE KEY `rek_patent_number_pid_2` (`rek_patent_number_pid`,`rek_patent_number_stamp`),
  KEY `rek_patent_number` (`rek_patent_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_period`
--

DROP TABLE IF EXISTS `fez_record_search_key_period`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_period` (
  `rek_period_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_period_pid` varchar(64) DEFAULT NULL,
  `rek_period_xsdmf_id` int(11) DEFAULT NULL,
  `rek_period_order` int(11) DEFAULT '1',
  `rek_period` text,
  PRIMARY KEY (`rek_period_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_period_pid`,`rek_period_order`),
  KEY `rek_period_pid` (`rek_period_pid`),
  FULLTEXT KEY `rek_period` (`rek_period`)
) ENGINE=MyISAM AUTO_INCREMENT=6004 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_period__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_period__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_period__shadow` (
  `rek_period_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_period_pid` varchar(64) DEFAULT NULL,
  `rek_period_xsdmf_id` int(11) DEFAULT NULL,
  `rek_period_order` int(11) DEFAULT '1',
  `rek_period` text,
  `rek_period_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_period_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_period_pid`,`rek_period_order`),
  UNIQUE KEY `rek_period_pid_2` (`rek_period_pid`,`rek_period_stamp`),
  KEY `rek_period_pid` (`rek_period_pid`),
  FULLTEXT KEY `rek_period` (`rek_period`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_phonetic_book_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_phonetic_book_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_phonetic_book_title` (
  `rek_phonetic_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_phonetic_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_phonetic_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_phonetic_book_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_phonetic_book_title_id`),
  UNIQUE KEY `unique_constraint` (`rek_phonetic_book_title_pid`,`rek_phonetic_book_title`),
  UNIQUE KEY `rek_phonetic_book_title_pid` (`rek_phonetic_book_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_phonetic_book_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_phonetic_book_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_phonetic_book_title__shadow` (
  `rek_phonetic_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_phonetic_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_phonetic_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_phonetic_book_title` varchar(255) DEFAULT NULL,
  `rek_phonetic_book_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_phonetic_book_title_id`),
  UNIQUE KEY `rek_phonetic_book_title_pid` (`rek_phonetic_book_title_pid`),
  UNIQUE KEY `rek_phonetic_book_title_pid_2` (`rek_phonetic_book_title_pid`,`rek_phonetic_book_title_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_phonetic_conference_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_phonetic_conference_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_phonetic_conference_name` (
  `rek_phonetic_conference_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_phonetic_conference_name_pid` varchar(64) DEFAULT NULL,
  `rek_phonetic_conference_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_phonetic_conference_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_phonetic_conference_name_id`),
  UNIQUE KEY `unique_constraint` (`rek_phonetic_conference_name_pid`,`rek_phonetic_conference_name`),
  UNIQUE KEY `rek_phonetic_conference_name_pid` (`rek_phonetic_conference_name_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_phonetic_conference_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_phonetic_conference_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_phonetic_conference_name__shadow` (
  `rek_phonetic_conference_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_phonetic_conference_name_pid` varchar(64) DEFAULT NULL,
  `rek_phonetic_conference_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_phonetic_conference_name` varchar(255) DEFAULT NULL,
  `rek_phonetic_conference_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_phonetic_conference_name_id`),
  UNIQUE KEY `rek_phonetic_conference_name_pid` (`rek_phonetic_conference_name_pid`),
  UNIQUE KEY `rek_phonetic_conference_name_p_2` (`rek_phonetic_conference_name_pid`,`rek_phonetic_conference_name_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_phonetic_journal_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_phonetic_journal_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_phonetic_journal_name` (
  `rek_phonetic_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_phonetic_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_phonetic_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_phonetic_journal_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_phonetic_journal_name_id`),
  UNIQUE KEY `unique_constraint` (`rek_phonetic_journal_name_pid`,`rek_phonetic_journal_name`),
  UNIQUE KEY `rek_phonetic_journal_name_pid` (`rek_phonetic_journal_name_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_phonetic_journal_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_phonetic_journal_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_phonetic_journal_name__shadow` (
  `rek_phonetic_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_phonetic_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_phonetic_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_phonetic_journal_name` varchar(255) DEFAULT NULL,
  `rek_phonetic_journal_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_phonetic_journal_name_id`),
  UNIQUE KEY `rek_phonetic_journal_name_pid` (`rek_phonetic_journal_name_pid`),
  UNIQUE KEY `rek_phonetic_journal_name_pid_2` (`rek_phonetic_journal_name_pid`,`rek_phonetic_journal_name_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_phonetic_newspaper`
--

DROP TABLE IF EXISTS `fez_record_search_key_phonetic_newspaper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_phonetic_newspaper` (
  `rek_phonetic_newspaper_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_phonetic_newspaper_pid` varchar(64) DEFAULT NULL,
  `rek_phonetic_newspaper_xsdmf_id` int(11) DEFAULT NULL,
  `rek_phonetic_newspaper` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_phonetic_newspaper_id`),
  UNIQUE KEY `unique_constraint` (`rek_phonetic_newspaper_pid`,`rek_phonetic_newspaper`),
  UNIQUE KEY `rek_phonetic_newspaper_pid` (`rek_phonetic_newspaper_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_phonetic_newspaper__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_phonetic_newspaper__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_phonetic_newspaper__shadow` (
  `rek_phonetic_newspaper_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_phonetic_newspaper_pid` varchar(64) DEFAULT NULL,
  `rek_phonetic_newspaper_xsdmf_id` int(11) DEFAULT NULL,
  `rek_phonetic_newspaper` varchar(255) DEFAULT NULL,
  `rek_phonetic_newspaper_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_phonetic_newspaper_id`),
  UNIQUE KEY `rek_phonetic_newspaper_pid` (`rek_phonetic_newspaper_pid`),
  UNIQUE KEY `rek_phonetic_newspaper_pid_2` (`rek_phonetic_newspaper_pid`,`rek_phonetic_newspaper_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_phonetic_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_phonetic_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_phonetic_title` (
  `rek_phonetic_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_phonetic_title_pid` varchar(64) DEFAULT NULL,
  `rek_phonetic_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_phonetic_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_phonetic_title_id`),
  UNIQUE KEY `unique_constraint` (`rek_phonetic_title_pid`,`rek_phonetic_title`),
  UNIQUE KEY `rek_phonetic_title_pid` (`rek_phonetic_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=1987 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_phonetic_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_phonetic_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_phonetic_title__shadow` (
  `rek_phonetic_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_phonetic_title_pid` varchar(64) DEFAULT NULL,
  `rek_phonetic_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_phonetic_title` varchar(255) DEFAULT NULL,
  `rek_phonetic_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_phonetic_title_id`),
  UNIQUE KEY `rek_phonetic_title_pid` (`rek_phonetic_title_pid`),
  UNIQUE KEY `rek_phonetic_title_pid_2` (`rek_phonetic_title_pid`,`rek_phonetic_title_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_place_of_publication`
--

DROP TABLE IF EXISTS `fez_record_search_key_place_of_publication`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_place_of_publication` (
  `rek_place_of_publication_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_place_of_publication_pid` varchar(64) DEFAULT NULL,
  `rek_place_of_publication_xsdmf_id` int(11) DEFAULT NULL,
  `rek_place_of_publication` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_place_of_publication_id`),
  UNIQUE KEY `unique_constraint` (`rek_place_of_publication_pid`,`rek_place_of_publication`),
  UNIQUE KEY `rek_place_of_publication_pid` (`rek_place_of_publication_pid`),
  KEY `rek_place_of_publication` (`rek_place_of_publication`),
  FULLTEXT KEY `rek_place_of_publication_ft` (`rek_place_of_publication`)
) ENGINE=MyISAM AUTO_INCREMENT=2752608 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_place_of_publication__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_place_of_publication__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_place_of_publication__shadow` (
  `rek_place_of_publication_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_place_of_publication_pid` varchar(64) DEFAULT NULL,
  `rek_place_of_publication_xsdmf_id` int(11) DEFAULT NULL,
  `rek_place_of_publication` varchar(255) DEFAULT NULL,
  `rek_place_of_publication_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_place_of_publication_id`),
  UNIQUE KEY `rek_place_of_publication_pid` (`rek_place_of_publication_pid`),
  UNIQUE KEY `rek_place_of_publication_pid_2` (`rek_place_of_publication_pid`,`rek_place_of_publication_stamp`),
  KEY `rek_place_of_publication` (`rek_place_of_publication`),
  FULLTEXT KEY `rek_place_of_publication_ft` (`rek_place_of_publication`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_prn`
--

DROP TABLE IF EXISTS `fez_record_search_key_prn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_prn` (
  `rek_prn_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_prn_pid` varchar(64) DEFAULT NULL,
  `rek_prn_xsdmf_id` int(11) DEFAULT NULL,
  `rek_prn` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_prn_id`),
  UNIQUE KEY `unique_constraint` (`rek_prn_pid`,`rek_prn`),
  UNIQUE KEY `rek_prn_pid` (`rek_prn_pid`),
  KEY `rek_prn` (`rek_prn`)
) ENGINE=InnoDB AUTO_INCREMENT=1062755 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_prn__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_prn__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_prn__shadow` (
  `rek_prn_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_prn_pid` varchar(64) DEFAULT NULL,
  `rek_prn_xsdmf_id` int(11) DEFAULT NULL,
  `rek_prn` varchar(255) DEFAULT NULL,
  `rek_prn_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_prn_id`),
  UNIQUE KEY `rek_prn_pid` (`rek_prn_pid`),
  UNIQUE KEY `rek_prn_pid_2` (`rek_prn_pid`,`rek_prn_stamp`),
  KEY `rek_prn` (`rek_prn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_proceedings_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_proceedings_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_proceedings_title` (
  `rek_proceedings_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_proceedings_title_pid` varchar(64) DEFAULT NULL,
  `rek_proceedings_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_proceedings_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_proceedings_title_id`),
  UNIQUE KEY `unique_constraint` (`rek_proceedings_title_pid`,`rek_proceedings_title`),
  UNIQUE KEY `rek_proceedings_title_pid` (`rek_proceedings_title_pid`),
  KEY `rek_proceedings_title` (`rek_proceedings_title`),
  FULLTEXT KEY `rek_proceedings_title_ft` (`rek_proceedings_title`)
) ENGINE=MyISAM AUTO_INCREMENT=597424 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_proceedings_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_proceedings_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_proceedings_title__shadow` (
  `rek_proceedings_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_proceedings_title_pid` varchar(64) DEFAULT NULL,
  `rek_proceedings_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_proceedings_title` varchar(255) DEFAULT NULL,
  `rek_proceedings_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_proceedings_title_id`),
  UNIQUE KEY `rek_proceedings_title_pid` (`rek_proceedings_title_pid`),
  UNIQUE KEY `rek_proceedings_title_pid_2` (`rek_proceedings_title_pid`,`rek_proceedings_title_stamp`),
  KEY `rek_proceedings_title` (`rek_proceedings_title`),
  FULLTEXT KEY `rek_proceedings_title_ft` (`rek_proceedings_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_publisher`
--

DROP TABLE IF EXISTS `fez_record_search_key_publisher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_publisher` (
  `rek_publisher_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_publisher_pid` varchar(64) DEFAULT NULL,
  `rek_publisher_xsdmf_id` int(11) DEFAULT NULL,
  `rek_publisher` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_publisher_id`),
  UNIQUE KEY `unique_constraint` (`rek_publisher_pid`,`rek_publisher`),
  UNIQUE KEY `rek_publisher_pid` (`rek_publisher_pid`),
  KEY `rek_publisher` (`rek_publisher`),
  FULLTEXT KEY `rek_publisher_ft` (`rek_publisher`)
) ENGINE=MyISAM AUTO_INCREMENT=2860672 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_publisher__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_publisher__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_publisher__shadow` (
  `rek_publisher_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_publisher_pid` varchar(64) DEFAULT NULL,
  `rek_publisher_xsdmf_id` int(11) DEFAULT NULL,
  `rek_publisher` varchar(255) DEFAULT NULL,
  `rek_publisher_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_publisher_id`),
  UNIQUE KEY `rek_publisher_pid` (`rek_publisher_pid`),
  UNIQUE KEY `rek_publisher_pid_2` (`rek_publisher_pid`,`rek_publisher_stamp`),
  KEY `rek_publisher` (`rek_publisher`),
  FULLTEXT KEY `rek_publisher_ft` (`rek_publisher`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_publisher_id`
--

DROP TABLE IF EXISTS `fez_record_search_key_publisher_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_publisher_id` (
  `rek_publisher_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_publisher_id_pid` varchar(64) DEFAULT NULL,
  `rek_publisher_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_publisher_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_publisher_id_id`),
  KEY `rek_publisher_id` (`rek_publisher_id`),
  KEY `rek_publisher_id_pid` (`rek_publisher_id_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_publisher_id__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_publisher_id__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_publisher_id__shadow` (
  `rek_publisher_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_publisher_id_pid` varchar(64) DEFAULT NULL,
  `rek_publisher_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_publisher_id` int(11) DEFAULT NULL,
  `rek_publisher_id_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_publisher_id_id`),
  UNIQUE KEY `rek_publisher_id_pid_2` (`rek_publisher_id_pid`,`rek_publisher_id_stamp`),
  KEY `rek_publisher_id` (`rek_publisher_id`),
  KEY `rek_publisher_id_pid` (`rek_publisher_id_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_refereed`
--

DROP TABLE IF EXISTS `fez_record_search_key_refereed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_refereed` (
  `rek_refereed_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_refereed_pid` varchar(64) DEFAULT NULL,
  `rek_refereed_xsdmf_id` int(11) DEFAULT NULL,
  `rek_refereed` int(11) DEFAULT NULL,
  PRIMARY KEY (`rek_refereed_id`),
  UNIQUE KEY `unique_constraint` (`rek_refereed_pid`,`rek_refereed`),
  UNIQUE KEY `rek_refereed_pid` (`rek_refereed_pid`),
  KEY `rek_refereed` (`rek_refereed`)
) ENGINE=InnoDB AUTO_INCREMENT=1406195 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_refereed__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_refereed__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_refereed__shadow` (
  `rek_refereed_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_refereed_pid` varchar(64) DEFAULT NULL,
  `rek_refereed_xsdmf_id` int(11) DEFAULT NULL,
  `rek_refereed` int(11) DEFAULT NULL,
  `rek_refereed_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_refereed_id`),
  UNIQUE KEY `rek_refereed_pid` (`rek_refereed_pid`),
  UNIQUE KEY `rek_refereed_pid_2` (`rek_refereed_pid`,`rek_refereed_stamp`),
  KEY `rek_refereed` (`rek_refereed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_report_number`
--

DROP TABLE IF EXISTS `fez_record_search_key_report_number`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_report_number` (
  `rek_report_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_report_number_pid` varchar(64) DEFAULT NULL,
  `rek_report_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_report_number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_report_number_id`),
  UNIQUE KEY `unique_constraint` (`rek_report_number_pid`,`rek_report_number`),
  UNIQUE KEY `rek_report_number_pid` (`rek_report_number_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=7535 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_report_number__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_report_number__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_report_number__shadow` (
  `rek_report_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_report_number_pid` varchar(64) DEFAULT NULL,
  `rek_report_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_report_number` varchar(255) DEFAULT NULL,
  `rek_report_number_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_report_number_id`),
  UNIQUE KEY `rek_report_number_pid` (`rek_report_number_pid`),
  UNIQUE KEY `rek_report_number_pid_2` (`rek_report_number_pid`,`rek_report_number_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_research_program`
--

DROP TABLE IF EXISTS `fez_record_search_key_research_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_research_program` (
  `rek_research_program_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_research_program_pid` varchar(64) DEFAULT NULL,
  `rek_research_program_xsdmf_id` int(11) DEFAULT NULL,
  `rek_research_program` varchar(255) DEFAULT NULL,
  `rek_research_program_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_research_program_id`),
  UNIQUE KEY `unique_constraint` (`rek_research_program_pid`,`rek_research_program`,`rek_research_program_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_research_program_pid`,`rek_research_program_order`),
  KEY `rek_research_program_pid` (`rek_research_program_pid`),
  KEY `rek_research_program` (`rek_research_program`),
  KEY `rek_research_program_order` (`rek_research_program_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_research_program__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_research_program__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_research_program__shadow` (
  `rek_research_program_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_research_program_pid` varchar(64) DEFAULT NULL,
  `rek_research_program_xsdmf_id` int(11) DEFAULT NULL,
  `rek_research_program` varchar(255) DEFAULT NULL,
  `rek_research_program_order` int(11) DEFAULT '1',
  `rek_research_program_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_research_program_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_research_program_pid`,`rek_research_program_order`),
  UNIQUE KEY `rek_research_program_pid_2` (`rek_research_program_pid`,`rek_research_program_stamp`),
  KEY `rek_research_program_pid` (`rek_research_program_pid`),
  KEY `rek_research_program` (`rek_research_program`),
  KEY `rek_research_program_order` (`rek_research_program_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_rights`
--

DROP TABLE IF EXISTS `fez_record_search_key_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_rights` (
  `rek_rights_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_rights_pid` varchar(64) DEFAULT NULL,
  `rek_rights_xsdmf_id` int(11) DEFAULT NULL,
  `rek_rights` text,
  PRIMARY KEY (`rek_rights_id`),
  UNIQUE KEY `rek_rights_pid` (`rek_rights_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=21886 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_rights__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_rights__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_rights__shadow` (
  `rek_rights_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_rights_pid` varchar(64) DEFAULT NULL,
  `rek_rights_xsdmf_id` int(11) DEFAULT NULL,
  `rek_rights` text,
  `rek_rights_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_rights_id`),
  UNIQUE KEY `rek_rights_pid` (`rek_rights_pid`),
  UNIQUE KEY `rek_rights_pid_2` (`rek_rights_pid`,`rek_rights_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_roman_script_book_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_roman_script_book_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_roman_script_book_title` (
  `rek_roman_script_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_roman_script_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_roman_script_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_roman_script_book_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_roman_script_book_title_id`),
  KEY `rek_roman_script_book_title` (`rek_roman_script_book_title`),
  KEY `rek_roman_script_book_title_pid` (`rek_roman_script_book_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=631 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_roman_script_book_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_roman_script_book_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_roman_script_book_title__shadow` (
  `rek_roman_script_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_roman_script_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_roman_script_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_roman_script_book_title` varchar(255) DEFAULT NULL,
  `rek_roman_script_book_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_roman_script_book_title_id`),
  UNIQUE KEY `rek_roman_script_book_title_pi_2` (`rek_roman_script_book_title_pid`,`rek_roman_script_book_title_stamp`),
  KEY `rek_roman_script_book_title` (`rek_roman_script_book_title`),
  KEY `rek_roman_script_book_title_pid` (`rek_roman_script_book_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_roman_script_conference_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_roman_script_conference_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_roman_script_conference_name` (
  `rek_roman_script_conference_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_roman_script_conference_name_pid` varchar(64) DEFAULT NULL,
  `rek_roman_script_conference_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_roman_script_conference_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_roman_script_conference_name_id`),
  KEY `rek_roman_script_conference_name` (`rek_roman_script_conference_name`),
  KEY `rek_roman_script_conference_name_pid` (`rek_roman_script_conference_name_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_roman_script_conference_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_roman_script_conference_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_roman_script_conference_name__shadow` (
  `rek_roman_script_conference_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_roman_script_conference_name_pid` varchar(64) DEFAULT NULL,
  `rek_roman_script_conference_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_roman_script_conference_name` varchar(255) DEFAULT NULL,
  `rek_roman_script_conference_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_roman_script_conference_name_id`),
  UNIQUE KEY `rek_roman_script_conference_na_2` (`rek_roman_script_conference_name_pid`,`rek_roman_script_conference_name_stamp`),
  KEY `rek_roman_script_conference_name` (`rek_roman_script_conference_name`),
  KEY `rek_roman_script_conference_name_pid` (`rek_roman_script_conference_name_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_roman_script_journal_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_roman_script_journal_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_roman_script_journal_name` (
  `rek_roman_script_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_roman_script_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_roman_script_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_roman_script_journal_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_roman_script_journal_name_id`),
  KEY `rek_roman_script_journal_name` (`rek_roman_script_journal_name`),
  KEY `rek_roman_script_journal_name_pid` (`rek_roman_script_journal_name_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=1106 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_roman_script_journal_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_roman_script_journal_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_roman_script_journal_name__shadow` (
  `rek_roman_script_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_roman_script_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_roman_script_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_roman_script_journal_name` varchar(255) DEFAULT NULL,
  `rek_roman_script_journal_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_roman_script_journal_name_id`),
  UNIQUE KEY `rek_roman_script_journal_name__2` (`rek_roman_script_journal_name_pid`,`rek_roman_script_journal_name_stamp`),
  KEY `rek_roman_script_journal_name` (`rek_roman_script_journal_name`),
  KEY `rek_roman_script_journal_name_pid` (`rek_roman_script_journal_name_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_roman_script_proceedings_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_roman_script_proceedings_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_roman_script_proceedings_title` (
  `rek_roman_script_proceedings_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_roman_script_proceedings_title_pid` varchar(64) DEFAULT NULL,
  `rek_roman_script_proceedings_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_roman_script_proceedings_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_roman_script_proceedings_title_id`),
  KEY `rek_roman_script_proceedings_title` (`rek_roman_script_proceedings_title`),
  KEY `rek_roman_script_proceedings_title_pid` (`rek_roman_script_proceedings_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_roman_script_proceedings_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_roman_script_proceedings_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_roman_script_proceedings_title__shadow` (
  `rek_roman_script_proceedings_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_roman_script_proceedings_title_pid` varchar(64) DEFAULT NULL,
  `rek_roman_script_proceedings_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_roman_script_proceedings_title` varchar(255) DEFAULT NULL,
  `rek_roman_script_proceedings_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_roman_script_proceedings_title_id`),
  UNIQUE KEY `rek_roman_script_proceedings_t_2` (`rek_roman_script_proceedings_title_pid`,`rek_roman_script_proceedings_title_stamp`),
  KEY `rek_roman_script_proceedings_title` (`rek_roman_script_proceedings_title`),
  KEY `rek_roman_script_proceedings_title_pid` (`rek_roman_script_proceedings_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_roman_script_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_roman_script_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_roman_script_title` (
  `rek_roman_script_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_roman_script_title_pid` varchar(64) DEFAULT NULL,
  `rek_roman_script_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_roman_script_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_roman_script_title_id`),
  KEY `rek_roman_script_title` (`rek_roman_script_title`),
  KEY `rek_roman_script_title_pid` (`rek_roman_script_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=1495 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_roman_script_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_roman_script_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_roman_script_title__shadow` (
  `rek_roman_script_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_roman_script_title_pid` varchar(64) DEFAULT NULL,
  `rek_roman_script_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_roman_script_title` varchar(255) DEFAULT NULL,
  `rek_roman_script_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_roman_script_title_id`),
  UNIQUE KEY `rek_roman_script_title_pid_2` (`rek_roman_script_title_pid`,`rek_roman_script_title_stamp`),
  KEY `rek_roman_script_title` (`rek_roman_script_title`),
  KEY `rek_roman_script_title_pid` (`rek_roman_script_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_scopus_id`
--

DROP TABLE IF EXISTS `fez_record_search_key_scopus_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_scopus_id` (
  `rek_scopus_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_scopus_id_pid` varchar(64) DEFAULT NULL,
  `rek_scopus_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_scopus_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_scopus_id_id`),
  UNIQUE KEY `unique_constraint` (`rek_scopus_id_pid`,`rek_scopus_id`),
  UNIQUE KEY `rek_scopus_id_pid` (`rek_scopus_id_pid`),
  KEY `rek_scopus_id` (`rek_scopus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1278950 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_scopus_id__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_scopus_id__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_scopus_id__shadow` (
  `rek_scopus_id_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_scopus_id_pid` varchar(64) DEFAULT NULL,
  `rek_scopus_id_xsdmf_id` int(11) DEFAULT NULL,
  `rek_scopus_id` varchar(255) DEFAULT NULL,
  `rek_scopus_id_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_scopus_id_id`),
  UNIQUE KEY `rek_scopus_id_pid` (`rek_scopus_id_pid`),
  UNIQUE KEY `rek_scopus_id_pid_2` (`rek_scopus_id_pid`,`rek_scopus_id_stamp`),
  KEY `rek_scopus_id` (`rek_scopus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_sensitivity_explanation`
--

DROP TABLE IF EXISTS `fez_record_search_key_sensitivity_explanation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_sensitivity_explanation` (
  `rek_sensitivity_explanation_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_sensitivity_explanation_pid` varchar(64) DEFAULT NULL,
  `rek_sensitivity_explanation_xsdmf_id` int(11) DEFAULT NULL,
  `rek_sensitivity_explanation` text,
  PRIMARY KEY (`rek_sensitivity_explanation_id`),
  UNIQUE KEY `rek_sensitivity_explanation_pid` (`rek_sensitivity_explanation_pid`),
  FULLTEXT KEY `rek_sensitivity_explanation` (`rek_sensitivity_explanation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_sensitivity_explanation__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_sensitivity_explanation__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_sensitivity_explanation__shadow` (
  `rek_sensitivity_explanation_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_sensitivity_explanation_pid` varchar(64) DEFAULT NULL,
  `rek_sensitivity_explanation_xsdmf_id` int(11) DEFAULT NULL,
  `rek_sensitivity_explanation` text,
  `rek_sensitivity_explanation_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_sensitivity_explanation_id`),
  UNIQUE KEY `rek_sensitivity_explanation_pid` (`rek_sensitivity_explanation_pid`),
  UNIQUE KEY `rek_sensitivity_explanation_pi_2` (`rek_sensitivity_explanation_pid`,`rek_sensitivity_explanation_stamp`),
  FULLTEXT KEY `rek_sensitivity_explanation` (`rek_sensitivity_explanation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_series`
--

DROP TABLE IF EXISTS `fez_record_search_key_series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_series` (
  `rek_series_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_series_pid` varchar(64) DEFAULT NULL,
  `rek_series_xsdmf_id` int(11) DEFAULT NULL,
  `rek_series` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_series_id`),
  UNIQUE KEY `unique_constraint` (`rek_series_pid`,`rek_series`),
  UNIQUE KEY `rek_series_pid` (`rek_series_pid`),
  KEY `rek_series` (`rek_series`),
  FULLTEXT KEY `rek_series_ft` (`rek_series`)
) ENGINE=MyISAM AUTO_INCREMENT=42757 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_series__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_series__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_series__shadow` (
  `rek_series_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_series_pid` varchar(64) DEFAULT NULL,
  `rek_series_xsdmf_id` int(11) DEFAULT NULL,
  `rek_series` varchar(255) DEFAULT NULL,
  `rek_series_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_series_id`),
  UNIQUE KEY `rek_series_pid` (`rek_series_pid`),
  UNIQUE KEY `rek_series_pid_2` (`rek_series_pid`,`rek_series_stamp`),
  KEY `rek_series` (`rek_series`),
  FULLTEXT KEY `rek_series_ft` (`rek_series`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_start_date`
--

DROP TABLE IF EXISTS `fez_record_search_key_start_date`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_start_date` (
  `rek_start_date_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_start_date_pid` varchar(64) DEFAULT NULL,
  `rek_start_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_start_date` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_start_date_id`),
  KEY `rek_start_date` (`rek_start_date`),
  KEY `rek_start_date_pid` (`rek_start_date_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_start_date__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_start_date__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_start_date__shadow` (
  `rek_start_date_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_start_date_pid` varchar(64) DEFAULT NULL,
  `rek_start_date_xsdmf_id` int(11) DEFAULT NULL,
  `rek_start_date` datetime DEFAULT NULL,
  `rek_start_date_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_start_date_id`),
  UNIQUE KEY `rek_start_date_pid_2` (`rek_start_date_pid`,`rek_start_date_stamp`),
  KEY `rek_start_date` (`rek_start_date`),
  KEY `rek_start_date_pid` (`rek_start_date_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_start_page`
--

DROP TABLE IF EXISTS `fez_record_search_key_start_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_start_page` (
  `rek_start_page_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_start_page_pid` varchar(64) DEFAULT NULL,
  `rek_start_page_xsdmf_id` int(11) DEFAULT NULL,
  `rek_start_page` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_start_page_id`),
  UNIQUE KEY `unique_constraint` (`rek_start_page_pid`,`rek_start_page`),
  UNIQUE KEY `rek_start_page_pid` (`rek_start_page_pid`),
  KEY `rek_start_page` (`rek_start_page`)
) ENGINE=InnoDB AUTO_INCREMENT=3733853 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_start_page__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_start_page__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_start_page__shadow` (
  `rek_start_page_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_start_page_pid` varchar(64) DEFAULT NULL,
  `rek_start_page_xsdmf_id` int(11) DEFAULT NULL,
  `rek_start_page` varchar(255) DEFAULT NULL,
  `rek_start_page_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_start_page_id`),
  UNIQUE KEY `rek_start_page_pid` (`rek_start_page_pid`),
  UNIQUE KEY `rek_start_page_pid_2` (`rek_start_page_pid`,`rek_start_page_stamp`),
  KEY `rek_start_page` (`rek_start_page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_structural_systems`
--

DROP TABLE IF EXISTS `fez_record_search_key_structural_systems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_structural_systems` (
  `rek_structural_systems_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_structural_systems_pid` varchar(64) DEFAULT NULL,
  `rek_structural_systems_xsdmf_id` int(11) DEFAULT NULL,
  `rek_structural_systems_order` int(11) DEFAULT '1',
  `rek_structural_systems` text,
  PRIMARY KEY (`rek_structural_systems_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_structural_systems_pid`,`rek_structural_systems_order`),
  KEY `rek_structural_systems_pid` (`rek_structural_systems_pid`),
  FULLTEXT KEY `rek_structural_systems` (`rek_structural_systems`)
) ENGINE=MyISAM AUTO_INCREMENT=19633 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_structural_systems__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_structural_systems__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_structural_systems__shadow` (
  `rek_structural_systems_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_structural_systems_pid` varchar(64) DEFAULT NULL,
  `rek_structural_systems_xsdmf_id` int(11) DEFAULT NULL,
  `rek_structural_systems_order` int(11) DEFAULT '1',
  `rek_structural_systems` text,
  `rek_structural_systems_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_structural_systems_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_structural_systems_pid`,`rek_structural_systems_order`),
  UNIQUE KEY `rek_structural_systems_pid_2` (`rek_structural_systems_pid`,`rek_structural_systems_stamp`),
  KEY `rek_structural_systems_pid` (`rek_structural_systems_pid`),
  FULLTEXT KEY `rek_structural_systems` (`rek_structural_systems`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_style`
--

DROP TABLE IF EXISTS `fez_record_search_key_style`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_style` (
  `rek_style_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_style_pid` varchar(64) DEFAULT NULL,
  `rek_style_xsdmf_id` int(11) DEFAULT NULL,
  `rek_style_order` int(11) DEFAULT '1',
  `rek_style` text,
  PRIMARY KEY (`rek_style_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_style_pid`,`rek_style_order`),
  KEY `rek_style_pid` (`rek_style_pid`),
  FULLTEXT KEY `rek_style` (`rek_style`)
) ENGINE=MyISAM AUTO_INCREMENT=1493 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_style__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_style__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_style__shadow` (
  `rek_style_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_style_pid` varchar(64) DEFAULT NULL,
  `rek_style_xsdmf_id` int(11) DEFAULT NULL,
  `rek_style_order` int(11) DEFAULT '1',
  `rek_style` text,
  `rek_style_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_style_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_style_pid`,`rek_style_order`),
  UNIQUE KEY `rek_style_pid_2` (`rek_style_pid`,`rek_style_stamp`),
  KEY `rek_style_pid` (`rek_style_pid`),
  FULLTEXT KEY `rek_style` (`rek_style`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_subcategory`
--

DROP TABLE IF EXISTS `fez_record_search_key_subcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_subcategory` (
  `rek_subcategory_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_subcategory_pid` varchar(64) DEFAULT NULL,
  `rek_subcategory_xsdmf_id` int(11) DEFAULT NULL,
  `rek_subcategory_order` int(11) DEFAULT '1',
  `rek_subcategory` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_subcategory_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_subcategory_pid`,`rek_subcategory_order`),
  KEY `rek_subcategory_pid` (`rek_subcategory_pid`),
  FULLTEXT KEY `rek_subcategory` (`rek_subcategory`)
) ENGINE=MyISAM AUTO_INCREMENT=16027 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_subcategory__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_subcategory__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_subcategory__shadow` (
  `rek_subcategory_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_subcategory_pid` varchar(64) DEFAULT NULL,
  `rek_subcategory_xsdmf_id` int(11) DEFAULT NULL,
  `rek_subcategory_order` int(11) DEFAULT '1',
  `rek_subcategory` varchar(255) DEFAULT NULL,
  `rek_subcategory_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_subcategory_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_subcategory_pid`,`rek_subcategory_order`),
  UNIQUE KEY `rek_subcategory_pid_2` (`rek_subcategory_pid`,`rek_subcategory_stamp`),
  KEY `rek_subcategory_pid` (`rek_subcategory_pid`),
  FULLTEXT KEY `rek_subcategory` (`rek_subcategory`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_subject`
--

DROP TABLE IF EXISTS `fez_record_search_key_subject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_subject` (
  `rek_subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_subject_pid` varchar(64) DEFAULT NULL,
  `rek_subject_xsdmf_id` int(11) DEFAULT NULL,
  `rek_subject` int(11) DEFAULT NULL,
  `rek_subject_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_subject_id`),
  UNIQUE KEY `unique_constraint` (`rek_subject_pid`,`rek_subject`,`rek_subject_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_subject_pid`,`rek_subject_order`),
  KEY `rek_subject_pid` (`rek_subject_pid`,`rek_subject`),
  KEY `rek_subject` (`rek_subject`),
  KEY `rek_subject_order` (`rek_subject_order`)
) ENGINE=InnoDB AUTO_INCREMENT=6611157 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_subject__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_subject__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_subject__shadow` (
  `rek_subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_subject_pid` varchar(64) DEFAULT NULL,
  `rek_subject_xsdmf_id` int(11) DEFAULT NULL,
  `rek_subject` int(11) DEFAULT NULL,
  `rek_subject_order` int(11) DEFAULT '1',
  `rek_subject_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_subject_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_subject_pid`,`rek_subject_order`),
  UNIQUE KEY `rek_subject_pid_2` (`rek_subject_pid`,`rek_subject_stamp`),
  KEY `rek_subject_pid` (`rek_subject_pid`,`rek_subject`),
  KEY `rek_subject` (`rek_subject`),
  KEY `rek_subject_order` (`rek_subject_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_surrounding_features`
--

DROP TABLE IF EXISTS `fez_record_search_key_surrounding_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_surrounding_features` (
  `rek_surrounding_features_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_surrounding_features_pid` varchar(64) DEFAULT NULL,
  `rek_surrounding_features_xsdmf_id` int(11) DEFAULT NULL,
  `rek_surrounding_features_order` int(11) DEFAULT '1',
  `rek_surrounding_features` text,
  PRIMARY KEY (`rek_surrounding_features_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_surrounding_features_pid`,`rek_surrounding_features_order`),
  KEY `rek_surrounding_features_pid` (`rek_surrounding_features_pid`),
  FULLTEXT KEY `rek_surrounding_features` (`rek_surrounding_features`)
) ENGINE=MyISAM AUTO_INCREMENT=5842 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_surrounding_features__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_surrounding_features__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_surrounding_features__shadow` (
  `rek_surrounding_features_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_surrounding_features_pid` varchar(64) DEFAULT NULL,
  `rek_surrounding_features_xsdmf_id` int(11) DEFAULT NULL,
  `rek_surrounding_features_order` int(11) DEFAULT '1',
  `rek_surrounding_features` text,
  `rek_surrounding_features_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_surrounding_features_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_surrounding_features_pid`,`rek_surrounding_features_order`),
  UNIQUE KEY `rek_surrounding_features_pid_2` (`rek_surrounding_features_pid`,`rek_surrounding_features_stamp`),
  KEY `rek_surrounding_features_pid` (`rek_surrounding_features_pid`),
  FULLTEXT KEY `rek_surrounding_features` (`rek_surrounding_features`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_total_chapters`
--

DROP TABLE IF EXISTS `fez_record_search_key_total_chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_total_chapters` (
  `rek_total_chapters_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_total_chapters_pid` varchar(64) DEFAULT NULL,
  `rek_total_chapters_xsdmf_id` int(11) DEFAULT NULL,
  `rek_total_chapters` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_total_chapters_id`),
  UNIQUE KEY `unique_constraint` (`rek_total_chapters_pid`,`rek_total_chapters`),
  UNIQUE KEY `rek_total_chapters_pid` (`rek_total_chapters_pid`),
  KEY `rek_total_chapters` (`rek_total_chapters`)
) ENGINE=InnoDB AUTO_INCREMENT=97175 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_total_chapters__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_total_chapters__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_total_chapters__shadow` (
  `rek_total_chapters_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_total_chapters_pid` varchar(64) DEFAULT NULL,
  `rek_total_chapters_xsdmf_id` int(11) DEFAULT NULL,
  `rek_total_chapters` varchar(255) DEFAULT NULL,
  `rek_total_chapters_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_total_chapters_id`),
  UNIQUE KEY `rek_total_chapters_pid` (`rek_total_chapters_pid`),
  UNIQUE KEY `rek_total_chapters_pid_2` (`rek_total_chapters_pid`,`rek_total_chapters_stamp`),
  KEY `rek_total_chapters` (`rek_total_chapters`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_total_pages`
--

DROP TABLE IF EXISTS `fez_record_search_key_total_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_total_pages` (
  `rek_total_pages_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_total_pages_pid` varchar(64) DEFAULT NULL,
  `rek_total_pages_xsdmf_id` int(11) DEFAULT NULL,
  `rek_total_pages` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_total_pages_id`),
  UNIQUE KEY `unique_constraint` (`rek_total_pages_pid`,`rek_total_pages`),
  UNIQUE KEY `rek_total_pages_pid` (`rek_total_pages_pid`),
  KEY `rek_total_pages` (`rek_total_pages`)
) ENGINE=InnoDB AUTO_INCREMENT=3406767 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_total_pages__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_total_pages__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_total_pages__shadow` (
  `rek_total_pages_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_total_pages_pid` varchar(64) DEFAULT NULL,
  `rek_total_pages_xsdmf_id` int(11) DEFAULT NULL,
  `rek_total_pages` varchar(255) DEFAULT NULL,
  `rek_total_pages_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_total_pages_id`),
  UNIQUE KEY `rek_total_pages_pid` (`rek_total_pages_pid`),
  UNIQUE KEY `rek_total_pages_pid_2` (`rek_total_pages_pid`,`rek_total_pages_stamp`),
  KEY `rek_total_pages` (`rek_total_pages`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_book_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_book_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_book_title` (
  `rek_translated_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_translated_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_book_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_translated_book_title_id`),
  UNIQUE KEY `unique_constraint` (`rek_translated_book_title_pid`,`rek_translated_book_title`),
  UNIQUE KEY `rek_translated_book_title_pid` (`rek_translated_book_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=699 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_book_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_book_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_book_title__shadow` (
  `rek_translated_book_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_book_title_pid` varchar(64) DEFAULT NULL,
  `rek_translated_book_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_book_title` varchar(255) DEFAULT NULL,
  `rek_translated_book_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_translated_book_title_id`),
  UNIQUE KEY `rek_translated_book_title_pid` (`rek_translated_book_title_pid`),
  UNIQUE KEY `rek_translated_book_title_pid_2` (`rek_translated_book_title_pid`,`rek_translated_book_title_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_conference_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_conference_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_conference_name` (
  `rek_translated_conference_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_conference_name_pid` varchar(64) DEFAULT NULL,
  `rek_translated_conference_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_conference_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_translated_conference_name_id`),
  UNIQUE KEY `unique_constraint` (`rek_translated_conference_name_pid`,`rek_translated_conference_name`),
  UNIQUE KEY `rek_translated_conference_name_pid` (`rek_translated_conference_name_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_conference_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_conference_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_conference_name__shadow` (
  `rek_translated_conference_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_conference_name_pid` varchar(64) DEFAULT NULL,
  `rek_translated_conference_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_conference_name` varchar(255) DEFAULT NULL,
  `rek_translated_conference_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_translated_conference_name_id`),
  UNIQUE KEY `rek_translated_conference_name_pid` (`rek_translated_conference_name_pid`),
  UNIQUE KEY `rek_translated_conference_name_2` (`rek_translated_conference_name_pid`,`rek_translated_conference_name_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_journal_name`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_journal_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_journal_name` (
  `rek_translated_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_translated_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_journal_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_translated_journal_name_id`),
  UNIQUE KEY `unique_constraint` (`rek_translated_journal_name_pid`,`rek_translated_journal_name`),
  UNIQUE KEY `rek_translated_journal_name_pid` (`rek_translated_journal_name_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=1147 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_journal_name__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_journal_name__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_journal_name__shadow` (
  `rek_translated_journal_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_journal_name_pid` varchar(64) DEFAULT NULL,
  `rek_translated_journal_name_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_journal_name` varchar(255) DEFAULT NULL,
  `rek_translated_journal_name_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_translated_journal_name_id`),
  UNIQUE KEY `rek_translated_journal_name_pid` (`rek_translated_journal_name_pid`),
  UNIQUE KEY `rek_translated_journal_name_pi_2` (`rek_translated_journal_name_pid`,`rek_translated_journal_name_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_newspaper`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_newspaper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_newspaper` (
  `rek_translated_newspaper_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_newspaper_pid` varchar(64) DEFAULT NULL,
  `rek_translated_newspaper_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_newspaper` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_translated_newspaper_id`),
  UNIQUE KEY `unique_constraint` (`rek_translated_newspaper_pid`,`rek_translated_newspaper`),
  UNIQUE KEY `rek_translated_newspaper_pid` (`rek_translated_newspaper_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_newspaper__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_newspaper__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_newspaper__shadow` (
  `rek_translated_newspaper_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_newspaper_pid` varchar(64) DEFAULT NULL,
  `rek_translated_newspaper_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_newspaper` varchar(255) DEFAULT NULL,
  `rek_translated_newspaper_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_translated_newspaper_id`),
  UNIQUE KEY `rek_translated_newspaper_pid` (`rek_translated_newspaper_pid`),
  UNIQUE KEY `rek_translated_newspaper_pid_2` (`rek_translated_newspaper_pid`,`rek_translated_newspaper_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_proceedings_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_proceedings_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_proceedings_title` (
  `rek_translated_proceedings_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_proceedings_title_pid` varchar(64) DEFAULT NULL,
  `rek_translated_proceedings_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_proceedings_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_translated_proceedings_title_id`),
  KEY `rek_translated_proceedings_title` (`rek_translated_proceedings_title`),
  KEY `rek_translated_proceedings_title_pid` (`rek_translated_proceedings_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_proceedings_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_proceedings_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_proceedings_title__shadow` (
  `rek_translated_proceedings_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_proceedings_title_pid` varchar(64) DEFAULT NULL,
  `rek_translated_proceedings_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_proceedings_title` varchar(255) DEFAULT NULL,
  `rek_translated_proceedings_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_translated_proceedings_title_id`),
  UNIQUE KEY `rek_translated_proceedings_tit_2` (`rek_translated_proceedings_title_pid`,`rek_translated_proceedings_title_stamp`),
  KEY `rek_translated_proceedings_title` (`rek_translated_proceedings_title`),
  KEY `rek_translated_proceedings_title_pid` (`rek_translated_proceedings_title_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_title`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_title` (
  `rek_translated_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_title_pid` varchar(64) DEFAULT NULL,
  `rek_translated_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_translated_title_id`),
  UNIQUE KEY `unique_constraint` (`rek_translated_title_pid`,`rek_translated_title`),
  UNIQUE KEY `rek_translated_title_pid` (`rek_translated_title_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=1945 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_translated_title__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_translated_title__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_translated_title__shadow` (
  `rek_translated_title_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_translated_title_pid` varchar(64) DEFAULT NULL,
  `rek_translated_title_xsdmf_id` int(11) DEFAULT NULL,
  `rek_translated_title` varchar(255) DEFAULT NULL,
  `rek_translated_title_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_translated_title_id`),
  UNIQUE KEY `rek_translated_title_pid` (`rek_translated_title_pid`),
  UNIQUE KEY `rek_translated_title_pid_2` (`rek_translated_title_pid`,`rek_translated_title_stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_volume_number`
--

DROP TABLE IF EXISTS `fez_record_search_key_volume_number`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_volume_number` (
  `rek_volume_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_volume_number_pid` varchar(64) DEFAULT NULL,
  `rek_volume_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_volume_number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rek_volume_number_id`),
  UNIQUE KEY `unique_constraint` (`rek_volume_number_pid`,`rek_volume_number`),
  UNIQUE KEY `rek_volume_number_pid` (`rek_volume_number_pid`),
  KEY `rek_volume_number` (`rek_volume_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3452905 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_volume_number__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_volume_number__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_volume_number__shadow` (
  `rek_volume_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_volume_number_pid` varchar(64) DEFAULT NULL,
  `rek_volume_number_xsdmf_id` int(11) DEFAULT NULL,
  `rek_volume_number` varchar(255) DEFAULT NULL,
  `rek_volume_number_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_volume_number_id`),
  UNIQUE KEY `rek_volume_number_pid` (`rek_volume_number_pid`),
  UNIQUE KEY `rek_volume_number_pid_2` (`rek_volume_number_pid`,`rek_volume_number_stamp`),
  KEY `rek_volume_number` (`rek_volume_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_xsd_display_option`
--

DROP TABLE IF EXISTS `fez_record_search_key_xsd_display_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_xsd_display_option` (
  `rek_xsd_display_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_xsd_display_option_pid` varchar(64) DEFAULT NULL,
  `rek_xsd_display_option_xsdmf_id` int(11) DEFAULT NULL,
  `rek_xsd_display_option` int(11) DEFAULT NULL,
  `rek_xsd_display_option_order` int(11) DEFAULT '1',
  PRIMARY KEY (`rek_xsd_display_option_id`),
  UNIQUE KEY `unique_constraint` (`rek_xsd_display_option_pid`,`rek_xsd_display_option`,`rek_xsd_display_option_order`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_xsd_display_option_pid`,`rek_xsd_display_option_order`),
  KEY `rek_xsd_display_option_pid` (`rek_xsd_display_option_pid`),
  KEY `rek_xsd_display_option` (`rek_xsd_display_option`),
  KEY `rek_xsd_display_option_order` (`rek_xsd_display_option_order`)
) ENGINE=InnoDB AUTO_INCREMENT=72815 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_record_search_key_xsd_display_option__shadow`
--

DROP TABLE IF EXISTS `fez_record_search_key_xsd_display_option__shadow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_record_search_key_xsd_display_option__shadow` (
  `rek_xsd_display_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `rek_xsd_display_option_pid` varchar(64) DEFAULT NULL,
  `rek_xsd_display_option_xsdmf_id` int(11) DEFAULT NULL,
  `rek_xsd_display_option` int(11) DEFAULT NULL,
  `rek_xsd_display_option_order` int(11) DEFAULT '1',
  `rek_xsd_display_option_stamp` datetime DEFAULT NULL,
  PRIMARY KEY (`rek_xsd_display_option_id`),
  UNIQUE KEY `unique_constraint_pid_order` (`rek_xsd_display_option_pid`,`rek_xsd_display_option_order`),
  UNIQUE KEY `rek_xsd_display_option_pid_2` (`rek_xsd_display_option_pid`,`rek_xsd_display_option_stamp`),
  KEY `rek_xsd_display_option_pid` (`rek_xsd_display_option_pid`),
  KEY `rek_xsd_display_option` (`rek_xsd_display_option`),
  KEY `rek_xsd_display_option_order` (`rek_xsd_display_option_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_rid_jobs`
--

DROP TABLE IF EXISTS `fez_rid_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_rid_jobs` (
  `rij_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rij_ticketno` varchar(50) DEFAULT NULL,
  `rij_lastcheck` timestamp NULL DEFAULT NULL,
  `rij_status` varchar(15) DEFAULT NULL,
  `rij_count` int(11) DEFAULT NULL,
  `rij_timestarted` timestamp NULL DEFAULT NULL,
  `rij_timefinished` timestamp NULL DEFAULT NULL,
  `rij_downloadrequest` text,
  `rij_lastresponse` text,
  `rij_response_profilelink` varchar(255) DEFAULT NULL,
  `rij_response_profilexml` mediumblob,
  `rij_response_publicationslink` varchar(255) DEFAULT NULL,
  `rij_response_publicationsxml` mediumblob,
  `rij_time_xmlcleaned` datetime DEFAULT NULL,
  PRIMARY KEY (`rij_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_rid_profile_uploads`
--

DROP TABLE IF EXISTS `fez_rid_profile_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_rid_profile_uploads` (
  `rpu_id` int(11) NOT NULL AUTO_INCREMENT,
  `rpu_email_filename` varchar(255) NOT NULL,
  `rpu_email_file_date` datetime NOT NULL,
  `rpu_response_url` varchar(255) NOT NULL,
  `rpu_response` mediumblob,
  `rpu_response_status` varchar(255) NOT NULL,
  `rpu_response_info` blob NOT NULL,
  `rpu_aut_org_username` varchar(255) NOT NULL,
  `rpu_created_date` datetime NOT NULL,
  `rpu_updated_date` datetime NOT NULL,
  PRIMARY KEY (`rpu_id`),
  UNIQUE KEY `rpu_id` (`rpu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_rid_registrations`
--

DROP TABLE IF EXISTS `fez_rid_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_rid_registrations` (
  `rre_id` int(11) NOT NULL AUTO_INCREMENT,
  `rre_aut_id` int(11) NOT NULL,
  `rre_response` mediumblob,
  `rre_created_date` datetime NOT NULL,
  `rre_updated_date` datetime NOT NULL,
  PRIMARY KEY (`rre_id`),
  UNIQUE KEY `rre_id` (`rre_id`),
  KEY `rre_aut_id` (`rre_aut_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_scopus_citations`
--

DROP TABLE IF EXISTS `fez_scopus_citations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_scopus_citations` (
  `sc_id` int(11) NOT NULL AUTO_INCREMENT,
  `sc_pid` varchar(64) NOT NULL,
  `sc_last_checked` int(10) NOT NULL,
  `sc_count` int(10) NOT NULL,
  `sc_created` int(11) NOT NULL,
  `sc_eid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_scopus_doctypes`
--

DROP TABLE IF EXISTS `fez_scopus_doctypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_scopus_doctypes` (
  `sdt_id` int(11) NOT NULL AUTO_INCREMENT,
  `sdt_code` varchar(5) DEFAULT NULL,
  `sdt_description` varchar(255) DEFAULT NULL,
  `sdt_created_date` datetime DEFAULT NULL,
  `sdt_updated_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sdt_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_search_key`
--

DROP TABLE IF EXISTS `fez_search_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_search_key` (
  `sek_id` varchar(64) NOT NULL DEFAULT '',
  `sek_namespace` varchar(64) DEFAULT NULL,
  `sek_incr_id` int(11) DEFAULT NULL,
  `sek_title` varchar(64) DEFAULT NULL,
  `sek_alt_title` varchar(64) DEFAULT NULL,
  `sek_desc` text,
  `sek_adv_visible` tinyint(1) DEFAULT '0',
  `sek_simple_used` tinyint(1) DEFAULT '0',
  `sek_myfez_visible` tinyint(1) DEFAULT NULL,
  `sek_order` int(11) DEFAULT '999',
  `sek_html_input` varchar(64) DEFAULT NULL,
  `sek_fez_variable` varchar(64) DEFAULT NULL,
  `sek_smarty_variable` varchar(64) DEFAULT NULL,
  `sek_cvo_id` int(11) unsigned DEFAULT NULL,
  `sek_lookup_function` varchar(255) DEFAULT NULL,
  `sek_data_type` varchar(10) DEFAULT NULL,
  `sek_relationship` tinyint(1) DEFAULT '0' COMMENT '0 is 1-1, 1 is 1-M',
  `sek_meta_header` varchar(64) DEFAULT NULL,
  `sek_cardinality` tinyint(1) DEFAULT '0',
  `sek_suggest_function` varchar(255) DEFAULT NULL,
  `sek_faceting` tinyint(1) DEFAULT '0',
  `sek_derived_function` varchar(255) DEFAULT NULL,
  `sek_lookup_id_function` varchar(255) DEFAULT NULL,
  `sek_bulkchange` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`sek_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_sessions`
--

DROP TABLE IF EXISTS `fez_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_sessions` (
  `session_id` varchar(100) NOT NULL DEFAULT '',
  `session_data` longtext,
  `created` datetime NOT NULL,
  `session_ip` varchar(255) DEFAULT NULL,
  `updated` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_sherpa_romeo`
--

DROP TABLE IF EXISTS `fez_sherpa_romeo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_sherpa_romeo` (
  `srm_id` int(11) NOT NULL AUTO_INCREMENT,
  `srm_issn` varchar(255) DEFAULT NULL,
  `srm_xml` mediumtext,
  `srm_journal_name` varchar(255) DEFAULT NULL,
  `srm_colour` varchar(255) DEFAULT NULL,
  `srm_date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`srm_id`),
  UNIQUE KEY `Unique` (`srm_issn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_statistics_all`
--

DROP TABLE IF EXISTS `fez_statistics_all`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_statistics_all` (
  `stl_id` int(11) NOT NULL AUTO_INCREMENT,
  `stl_archive_name` varchar(255) DEFAULT NULL,
  `stl_ip` varchar(15) DEFAULT NULL,
  `stl_hostname` varchar(255) DEFAULT NULL,
  `stl_request_date` timestamp NULL DEFAULT NULL,
  `stl_country_code` varchar(4) DEFAULT NULL,
  `stl_country_name` varchar(100) DEFAULT NULL,
  `stl_region` varchar(100) DEFAULT NULL,
  `stl_city` varchar(100) DEFAULT NULL,
  `stl_pid` varchar(255) DEFAULT NULL,
  `stl_pid_num` int(11) NOT NULL,
  `stl_dsid` varchar(255) DEFAULT NULL,
  `stl_origin` varchar(10) DEFAULT NULL,
  `stl_counter_bad` tinyint(1) DEFAULT '0',
  `stl_usr_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`stl_id`),
  KEY `stl_pid` (`stl_pid`),
  KEY `stl_dsid` (`stl_dsid`),
  KEY `stl_pid_num` (`stl_pid_num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_statistics_buffer`
--

DROP TABLE IF EXISTS `fez_statistics_buffer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_statistics_buffer` (
  `str_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `str_ip` varchar(64) DEFAULT NULL,
  `str_usr_id` int(11) DEFAULT NULL,
  `str_request_date` datetime DEFAULT NULL,
  `str_pid` varchar(255) DEFAULT NULL,
  `str_dsid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`str_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_statistics_proc`
--

DROP TABLE IF EXISTS `fez_statistics_proc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_statistics_proc` (
  `stp_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stp_latestlog` timestamp NULL DEFAULT NULL,
  `stp_lastproc` date DEFAULT NULL,
  `stp_count` int(11) DEFAULT NULL,
  `stp_count_inserted` int(11) DEFAULT NULL,
  `stp_timestarted` timestamp NULL DEFAULT NULL,
  `stp_timefinished` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`stp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_statistics_robots`
--

DROP TABLE IF EXISTS `fez_statistics_robots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_statistics_robots` (
  `str_id` int(11) NOT NULL AUTO_INCREMENT,
  `str_ip` varchar(15) DEFAULT NULL,
  `str_hostname` varchar(255) DEFAULT NULL,
  `str_date_added` date DEFAULT NULL,
  PRIMARY KEY (`str_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_statistics_sum_4weeks`
--

DROP TABLE IF EXISTS `fez_statistics_sum_4weeks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_statistics_sum_4weeks` (
  `s4w_pid` varchar(64) NOT NULL,
  `s4w_title` varchar(255) NOT NULL,
  `s4w_citation` text NOT NULL,
  `s4w_downloads` int(11) NOT NULL,
  PRIMARY KEY (`s4w_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_statistics_sum_authors`
--

DROP TABLE IF EXISTS `fez_statistics_sum_authors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_statistics_sum_authors` (
  `sau_author_id` int(11) NOT NULL,
  `sau_author_name` varchar(255) DEFAULT NULL,
  `sau_downloads` int(11) DEFAULT NULL,
  PRIMARY KEY (`sau_author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_statistics_sum_countryregion`
--

DROP TABLE IF EXISTS `fez_statistics_sum_countryregion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_statistics_sum_countryregion` (
  `scr_id` int(11) NOT NULL AUTO_INCREMENT,
  `scr_country_name` varchar(50) DEFAULT NULL,
  `scr_country_code` varchar(4) DEFAULT NULL,
  `scr_country_region` varchar(50) DEFAULT NULL,
  `scr_city` varchar(255) DEFAULT NULL,
  `scr_count_abstract` int(11) DEFAULT NULL,
  `scr_count_downloads` int(11) DEFAULT NULL,
  PRIMARY KEY (`scr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_statistics_sum_papers`
--

DROP TABLE IF EXISTS `fez_statistics_sum_papers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_statistics_sum_papers` (
  `spa_pid` varchar(64) NOT NULL,
  `spa_title` varchar(255) NOT NULL,
  `spa_citation` text NOT NULL,
  `spa_downloads` int(11) NOT NULL,
  PRIMARY KEY (`spa_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_statistics_sum_year`
--

DROP TABLE IF EXISTS `fez_statistics_sum_year`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_statistics_sum_year` (
  `syr_year` char(4) NOT NULL,
  `syr_pid` varchar(64) NOT NULL,
  `syr_title` varchar(255) NOT NULL,
  `syr_downloads` int(11) NOT NULL,
  `syr_citation` text,
  PRIMARY KEY (`syr_year`,`syr_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_statistics_sum_yearmonth`
--

DROP TABLE IF EXISTS `fez_statistics_sum_yearmonth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_statistics_sum_yearmonth` (
  `sym_year` char(4) NOT NULL,
  `sym_month` char(2) NOT NULL,
  `sym_pid` varchar(64) NOT NULL,
  `sym_title` varchar(255) NOT NULL,
  `sym_downloads` int(11) NOT NULL,
  `sym_citation` text,
  PRIMARY KEY (`sym_year`,`sym_month`,`sym_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_statistics_sum_yearmonth_figures`
--

DROP TABLE IF EXISTS `fez_statistics_sum_yearmonth_figures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_statistics_sum_yearmonth_figures` (
  `syf_year` int(4) NOT NULL,
  `syf_monthnum` int(2) NOT NULL,
  `syf_month` char(3) NOT NULL,
  `syf_abstracts` int(11) NOT NULL,
  `syf_downloads` int(11) NOT NULL,
  PRIMARY KEY (`syf_year`,`syf_monthnum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_status`
--

DROP TABLE IF EXISTS `fez_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_status` (
  `sta_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sta_title` varchar(255) DEFAULT NULL,
  `sta_order` int(11) unsigned DEFAULT NULL,
  `sta_color` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sta_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_survey`
--

DROP TABLE IF EXISTS `fez_survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_survey` (
  `sur_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sur_usr_id` int(11) DEFAULT NULL,
  `sur_experience` tinyint(1) DEFAULT NULL,
  `sur_external_freq` tinyint(1) DEFAULT NULL,
  `sur_3_cat` tinyint(1) DEFAULT NULL,
  `sur_3_elearn` tinyint(1) DEFAULT NULL,
  `sur_3_journals` tinyint(1) DEFAULT NULL,
  `sur_3_blackboard` tinyint(1) DEFAULT NULL,
  `sur_3_lecture` tinyint(1) DEFAULT NULL,
  `sur_3_instrumentation` tinyint(1) DEFAULT NULL,
  `sur_3_datasets` tinyint(1) DEFAULT NULL,
  `sur_3_remotedb` tinyint(1) DEFAULT NULL,
  `sur_3_extcom` tinyint(1) DEFAULT NULL,
  `sur_3_collab` tinyint(1) DEFAULT NULL,
  `sur_3_other` text,
  `sur_4_cat` tinyint(1) DEFAULT NULL,
  `sur_4_elearn` tinyint(1) DEFAULT NULL,
  `sur_4_journals` tinyint(1) DEFAULT NULL,
  `sur_4_blackboard` tinyint(1) DEFAULT NULL,
  `sur_4_lecture` tinyint(1) DEFAULT NULL,
  `sur_4_instrumentation` tinyint(1) DEFAULT NULL,
  `sur_4_datasets` tinyint(1) DEFAULT NULL,
  `sur_4_remotedb` tinyint(1) DEFAULT NULL,
  `sur_4_extcom` tinyint(1) DEFAULT NULL,
  `sur_4_collab` tinyint(1) DEFAULT NULL,
  `sur_4_other` text,
  `sur_comments` text,
  `sur_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`sur_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_thomson_citations`
--

DROP TABLE IF EXISTS `fez_thomson_citations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_thomson_citations` (
  `tc_id` int(11) NOT NULL AUTO_INCREMENT,
  `tc_pid` varchar(64) NOT NULL,
  `tc_last_checked` int(10) NOT NULL,
  `tc_count` int(10) NOT NULL,
  `tc_created` int(11) NOT NULL,
  `tc_isi_loc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_thomson_doctype_mappings`
--

DROP TABLE IF EXISTS `fez_thomson_doctype_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_thomson_doctype_mappings` (
  `tdm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tdm_xdis_id` int(11) unsigned NOT NULL,
  `tdm_doctype` varchar(5) NOT NULL DEFAULT '',
  `tdm_service` varchar(45) NOT NULL DEFAULT '',
  `tdm_subtype` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tdm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_thomson_incites_doctypes`
--

DROP TABLE IF EXISTS `fez_thomson_incites_doctypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_thomson_incites_doctypes` (
  `inc_id` int(11) NOT NULL,
  `inc_title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`inc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_tombstone`
--

DROP TABLE IF EXISTS `fez_tombstone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_tombstone` (
  `tom_id` int(11) NOT NULL AUTO_INCREMENT,
  `tom_pid_main` varchar(64) DEFAULT NULL,
  `tom_pid_rel` varchar(64) DEFAULT NULL,
  `tom_delete_ts` datetime DEFAULT NULL,
  PRIMARY KEY (`tom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_user`
--

DROP TABLE IF EXISTS `fez_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_user` (
  `usr_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usr_created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `usr_status` varchar(8) NOT NULL DEFAULT 'active',
  `usr_password` varchar(32) DEFAULT NULL,
  `usr_full_name` varchar(255) NOT NULL DEFAULT '',
  `usr_given_names` varchar(255) DEFAULT NULL,
  `usr_family_name` varchar(255) DEFAULT NULL,
  `usr_email` varchar(255) DEFAULT NULL,
  `usr_preferences` longtext,
  `usr_sms_email` varchar(255) DEFAULT NULL,
  `usr_username` varchar(50) NOT NULL,
  `usr_shib_username` varchar(50) DEFAULT NULL,
  `usr_administrator` tinyint(1) DEFAULT '0',
  `usr_ldap_authentication` tinyint(1) DEFAULT '0',
  `usr_login_count` int(11) DEFAULT '0',
  `usr_last_login_date` datetime DEFAULT '0000-00-00 00:00:00',
  `usr_shib_login_count` int(11) DEFAULT '0',
  `usr_external_usr_id` int(11) DEFAULT NULL,
  `usr_super_administrator` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`usr_id`),
  UNIQUE KEY `usr_username` (`usr_username`),
  FULLTEXT KEY `usr_fulltext` (`usr_full_name`,`usr_given_names`,`usr_family_name`,`usr_username`,`usr_shib_username`),
  FULLTEXT KEY `usr_full_name` (`usr_full_name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_user_comments`
--

DROP TABLE IF EXISTS `fez_user_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_user_comments` (
  `usc_id` int(11) NOT NULL AUTO_INCREMENT,
  `usc_userid` int(11) NOT NULL DEFAULT '0',
  `usc_pid` varchar(64) NOT NULL DEFAULT '',
  `usc_comment` text NOT NULL,
  `usc_rating` int(11) NOT NULL DEFAULT '0',
  `usc_date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_user_shibboleth_attribs`
--

DROP TABLE IF EXISTS `fez_user_shibboleth_attribs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_user_shibboleth_attribs` (
  `usa_usr_id` int(11) unsigned NOT NULL DEFAULT '0',
  `usa_shib_name` varchar(100) NOT NULL DEFAULT '',
  `usa_shib_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`usa_usr_id`,`usa_shib_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_wfbehaviour`
--

DROP TABLE IF EXISTS `fez_wfbehaviour`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_wfbehaviour` (
  `wfb_id` int(11) NOT NULL AUTO_INCREMENT,
  `wfb_title` varchar(255) NOT NULL DEFAULT '',
  `wfb_description` text NOT NULL,
  `wfb_version` varchar(255) NOT NULL DEFAULT '1.0',
  `wfb_script_name` varchar(255) NOT NULL DEFAULT '',
  `wfb_auto` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`wfb_id`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_wok_doctypes`
--

DROP TABLE IF EXISTS `fez_wok_doctypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_wok_doctypes` (
  `wdt_id` int(11) NOT NULL AUTO_INCREMENT,
  `wdt_code` char(2) DEFAULT NULL,
  `wdt_description` varchar(255) DEFAULT NULL,
  `wdt_created_date` datetime DEFAULT NULL,
  `wdt_updated_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`wdt_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_wok_locks`
--

DROP TABLE IF EXISTS `fez_wok_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_wok_locks` (
  `wkl_name` varchar(8) NOT NULL,
  `wkl_value` int(10) unsigned NOT NULL,
  `wkl_pid` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`wkl_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_wok_queue`
--

DROP TABLE IF EXISTS `fez_wok_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_wok_queue` (
  `wkq_key` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wkq_id` varchar(128) NOT NULL DEFAULT '',
  `wkq_op` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`wkq_key`),
  UNIQUE KEY `id_op` (`wkq_id`,`wkq_op`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_wok_queue_aut`
--

DROP TABLE IF EXISTS `fez_wok_queue_aut`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_wok_queue_aut` (
  `wka_key` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wka_id` varchar(128) NOT NULL DEFAULT '',
  `wka_aut_id` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`wka_key`),
  UNIQUE KEY `id_op` (`wka_id`,`wka_aut_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_wok_session`
--

DROP TABLE IF EXISTS `fez_wok_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_wok_session` (
  `wks_id` varchar(100) NOT NULL,
  PRIMARY KEY (`wks_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_workflow`
--

DROP TABLE IF EXISTS `fez_workflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_workflow` (
  `wfl_id` int(11) NOT NULL AUTO_INCREMENT,
  `wfl_title` varchar(255) DEFAULT NULL,
  `wfl_version` varchar(255) DEFAULT NULL,
  `wfl_description` text,
  `wfl_roles` varchar(255) DEFAULT NULL,
  `wfl_end_button_label` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`wfl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_workflow_roles`
--

DROP TABLE IF EXISTS `fez_workflow_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_workflow_roles` (
  `wfr_wfl_id` int(11) unsigned NOT NULL,
  `wfr_aro_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`wfr_wfl_id`,`wfr_aro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_workflow_sessions`
--

DROP TABLE IF EXISTS `fez_workflow_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_workflow_sessions` (
  `wfses_id` int(11) NOT NULL AUTO_INCREMENT,
  `wfses_usr_id` int(11) NOT NULL,
  `wfses_object` longtext,
  `wfses_listing` varchar(255) NOT NULL DEFAULT '',
  `wfses_date` datetime NOT NULL,
  `wfses_pid` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`wfses_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_workflow_state`
--

DROP TABLE IF EXISTS `fez_workflow_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_workflow_state` (
  `wfs_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wfs_wfl_id` int(11) unsigned DEFAULT NULL,
  `wfs_title` varchar(64) DEFAULT NULL,
  `wfs_description` text,
  `wfs_auto` tinyint(1) DEFAULT NULL,
  `wfs_wfb_id` int(11) DEFAULT NULL,
  `wfs_start` tinyint(1) DEFAULT NULL,
  `wfs_end` tinyint(1) DEFAULT NULL,
  `wfs_assigned_role_id` int(11) DEFAULT NULL,
  `wfs_transparent` tinyint(1) DEFAULT '0',
  `wfs_roles` varchar(255) DEFAULT NULL,
  `wfs_display_order` int(4) DEFAULT '999',
  PRIMARY KEY (`wfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_workflow_state_link`
--

DROP TABLE IF EXISTS `fez_workflow_state_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_workflow_state_link` (
  `wfsl_id` int(11) NOT NULL AUTO_INCREMENT,
  `wfsl_wfl_id` int(11) NOT NULL DEFAULT '0',
  `wfsl_from_id` int(11) NOT NULL DEFAULT '0',
  `wfsl_to_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`wfsl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_workflow_state_roles`
--

DROP TABLE IF EXISTS `fez_workflow_state_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_workflow_state_roles` (
  `wfsr_wfs_id` int(11) unsigned NOT NULL,
  `wfsr_aro_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`wfsr_wfs_id`,`wfsr_aro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_workflow_trigger`
--

DROP TABLE IF EXISTS `fez_workflow_trigger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_workflow_trigger` (
  `wft_id` int(11) NOT NULL AUTO_INCREMENT,
  `wft_pid` varchar(64) NOT NULL DEFAULT '',
  `wft_type_id` int(11) NOT NULL DEFAULT '0',
  `wft_wfl_id` int(11) NOT NULL DEFAULT '0',
  `wft_xdis_id` int(11) NOT NULL DEFAULT '0',
  `wft_order` int(11) NOT NULL DEFAULT '0',
  `wft_mimetype` varchar(255) NOT NULL DEFAULT '',
  `wft_icon` varchar(64) NOT NULL DEFAULT '',
  `wft_ret_id` int(11) NOT NULL DEFAULT '0',
  `wft_options` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`wft_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_xsd`
--

DROP TABLE IF EXISTS `fez_xsd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_xsd` (
  `xsd_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `xsd_title` varchar(50) DEFAULT NULL,
  `xsd_version` varchar(20) DEFAULT NULL,
  `xsd_file` longtext,
  `xsd_top_element_name` varchar(50) DEFAULT NULL,
  `xsd_element_prefix` varchar(50) DEFAULT NULL,
  `xsd_extra_ns_prefixes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`xsd_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_xsd_display`
--

DROP TABLE IF EXISTS `fez_xsd_display`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_xsd_display` (
  `xdis_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `xdis_xsd_id` int(11) DEFAULT NULL,
  `xdis_title` varchar(50) DEFAULT NULL,
  `xdis_version` varchar(20) DEFAULT NULL,
  `xdis_object_type` tinyint(1) unsigned DEFAULT '0',
  `xdis_enabled` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`xdis_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_xsd_display_attach`
--

DROP TABLE IF EXISTS `fez_xsd_display_attach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_xsd_display_attach` (
  `att_id` int(11) NOT NULL AUTO_INCREMENT,
  `att_parent_xsdmf_id` int(11) unsigned NOT NULL DEFAULT '0',
  `att_child_xsdmf_id` int(11) unsigned NOT NULL DEFAULT '0',
  `att_order` int(7) DEFAULT NULL,
  PRIMARY KEY (`att_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_xsd_display_matchfields`
--

DROP TABLE IF EXISTS `fez_xsd_display_matchfields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_xsd_display_matchfields` (
  `xsdmf_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `xsdmf_xdis_id` int(11) DEFAULT NULL,
  `xsdmf_xsdsel_id` int(11) DEFAULT NULL,
  `xsdmf_element` varchar(255) DEFAULT NULL,
  `xsdmf_title` varchar(255) DEFAULT NULL,
  `xsdmf_description` mediumtext,
  `xsdmf_long_description` mediumtext,
  `xsdmf_html_input` varchar(20) DEFAULT NULL,
  `xsdmf_multiple` tinyint(1) DEFAULT NULL,
  `xsdmf_multiple_limit` int(4) DEFAULT NULL,
  `xsdmf_valueintag` tinyint(1) DEFAULT NULL,
  `xsdmf_enabled` tinyint(1) DEFAULT NULL,
  `xsdmf_order` int(4) DEFAULT NULL,
  `xsdmf_validation_type` varchar(8) DEFAULT NULL,
  `xsdmf_required` tinyint(1) DEFAULT NULL,
  `xsdmf_static_text` mediumtext,
  `xsdmf_dynamic_text` mediumtext,
  `xsdmf_xdis_id_ref` int(11) DEFAULT NULL,
  `xsdmf_id_ref` int(11) DEFAULT NULL,
  `xsdmf_id_ref_save_type` tinyint(1) DEFAULT '0',
  `xsdmf_is_key` tinyint(1) DEFAULT NULL,
  `xsdmf_key_match` varchar(255) DEFAULT NULL,
  `xsdmf_show_in_view` tinyint(1) DEFAULT NULL,
  `xsdmf_smarty_variable` varchar(255) DEFAULT NULL,
  `xsdmf_fez_variable` varchar(50) DEFAULT NULL,
  `xsdmf_enforced_prefix` varchar(255) DEFAULT NULL,
  `xsdmf_value_prefix` varchar(255) DEFAULT NULL,
  `xsdmf_selected_option` varchar(255) DEFAULT NULL,
  `xsdmf_dynamic_selected_option` varchar(255) DEFAULT NULL,
  `xsdmf_image_location` varchar(255) DEFAULT NULL,
  `xsdmf_parent_key_match` varchar(255) DEFAULT NULL,
  `xsdmf_data_type` varchar(20) DEFAULT 'varchar',
  `xsdmf_indexed` tinyint(1) DEFAULT '0',
  `xsdmf_sek_id` varchar(64) DEFAULT NULL,
  `xsdmf_cvo_id` int(11) DEFAULT NULL,
  `xsdmf_cvo_min_level` int(11) DEFAULT NULL,
  `xsdmf_cvo_save_type` tinyint(1) DEFAULT '0',
  `xsdmf_original_xsdmf_id` int(11) DEFAULT NULL,
  `xsdmf_attached_xsdmf_id` int(11) DEFAULT NULL,
  `xsdmf_cso_value` varchar(7) DEFAULT NULL,
  `xsdmf_citation_browse` int(1) DEFAULT '0',
  `xsdmf_citation` int(1) DEFAULT '0',
  `xsdmf_citation_bold` int(1) DEFAULT '0',
  `xsdmf_citation_italics` int(1) DEFAULT '0',
  `xsdmf_citation_order` int(4) DEFAULT NULL,
  `xsdmf_citation_brackets` int(1) DEFAULT '0',
  `xsdmf_citation_prefix` varchar(100) DEFAULT NULL,
  `xsdmf_citation_suffix` varchar(100) DEFAULT NULL,
  `xsdmf_use_parent_option_list` int(1) DEFAULT '0',
  `xsdmf_parent_option_xdis_id` int(11) DEFAULT NULL,
  `xsdmf_parent_option_child_xsdmf_id` int(11) DEFAULT NULL,
  `xsdmf_org_level` varchar(64) DEFAULT NULL,
  `xsdmf_use_org_to_fill` int(1) DEFAULT '0',
  `xsdmf_org_fill_xdis_id` int(11) DEFAULT NULL,
  `xsdmf_org_fill_xsdmf_id` int(11) DEFAULT NULL,
  `xsdmf_asuggest_xdis_id` int(11) DEFAULT NULL,
  `xsdmf_asuggest_xsdmf_id` int(11) DEFAULT NULL,
  `xsdmf_date_type` tinyint(1) DEFAULT '0',
  `xsdmf_meta_header` tinyint(1) DEFAULT '0',
  `xsdmf_meta_header_name` varchar(64) DEFAULT NULL,
  `xsdmf_invisible` tinyint(1) DEFAULT '0',
  `xsdmf_show_simple_create` tinyint(1) DEFAULT '1',
  `xsdmf_xpath` text,
  `xsdmf_validation_maxlength` int(4) DEFAULT NULL,
  `xsdmf_validation_regex` mediumtext,
  `xsdmf_validation_message` mediumtext,
  PRIMARY KEY (`xsdmf_id`),
  KEY `xsdmf_xsdsel_id` (`xsdmf_xsdsel_id`),
  KEY `xsdmf_xdis_id` (`xsdmf_xdis_id`),
  KEY `xsdmf_sek_id` (`xsdmf_sek_id`),
  KEY `xsdmf_element` (`xsdmf_element`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_xsd_display_mf_option`
--

DROP TABLE IF EXISTS `fez_xsd_display_mf_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_xsd_display_mf_option` (
  `mfo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mfo_fld_id` int(10) unsigned NOT NULL DEFAULT '0',
  `mfo_value` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`mfo_id`),
  KEY `icf_fld_id` (`mfo_fld_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_xsd_loop_subelement`
--

DROP TABLE IF EXISTS `fez_xsd_loop_subelement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_xsd_loop_subelement` (
  `xsdsel_id` int(11) NOT NULL AUTO_INCREMENT,
  `xsdsel_xsdmf_id` int(11) DEFAULT NULL,
  `xsdsel_title` varchar(255) DEFAULT NULL,
  `xsdsel_type` varchar(30) DEFAULT NULL,
  `xsdsel_order` int(6) DEFAULT NULL,
  `xsdsel_attribute_loop_xdis_id` int(11) DEFAULT '0',
  `xsdsel_attribute_loop_xsdmf_id` int(11) DEFAULT '0',
  `xsdsel_indicator_xdis_id` int(11) DEFAULT '0',
  `xsdsel_indicator_xsdmf_id` int(11) DEFAULT '0',
  `xsdsel_indicator_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`xsdsel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fez_xsd_relationship`
--

DROP TABLE IF EXISTS `fez_xsd_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fez_xsd_relationship` (
  `xsdrel_id` int(11) NOT NULL AUTO_INCREMENT,
  `xsdrel_xsdmf_id` int(11) DEFAULT NULL,
  `xsdrel_xdis_id` int(11) DEFAULT NULL,
  `xsdrel_order` int(6) DEFAULT NULL,
  PRIMARY KEY (`xsdrel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hr_org_unit_distinct_manual`
--

DROP TABLE IF EXISTS `hr_org_unit_distinct_manual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hr_org_unit_distinct_manual` (
  `aurion_org_id` int(11) NOT NULL,
  `aurion_org_desc` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`aurion_org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hr_personal_details_vw`
--

DROP TABLE IF EXISTS `hr_personal_details_vw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hr_personal_details_vw` (
  `WAMIKEY` varchar(12) NOT NULL,
  `N_SURNAME` varchar(30) DEFAULT NULL,
  `N_GIVEN` varchar(35) DEFAULT NULL,
  `N_INITIALS` varchar(6) DEFAULT NULL,
  `N_TITLE` varchar(20) DEFAULT NULL,
  `PN_GIVEN` varchar(20) DEFAULT NULL,
  `GENDER` varchar(6) DEFAULT NULL,
  `PH_HOME` varchar(14) DEFAULT NULL,
  `PH_CONTACT` varchar(14) DEFAULT NULL,
  `PH_FAX` varchar(14) DEFAULT NULL,
  `PH_MOBILE` varchar(14) DEFAULT NULL,
  `EMAIL` varchar(60) DEFAULT NULL,
  `BIRTHDATE` date DEFAULT NULL,
  `AATSI_CODE` varchar(6) DEFAULT NULL,
  `WAMI_MDATE` date DEFAULT NULL,
  PRIMARY KEY (`WAMIKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hr_position_vw`
--

DROP TABLE IF EXISTS `hr_position_vw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hr_position_vw` (
  `WAMIKEY` varchar(12) NOT NULL,
  `STAFF_NUM` varchar(12) NOT NULL,
  `POS_NUM` varchar(12) DEFAULT NULL,
  `POS_TITLE` varchar(35) DEFAULT NULL,
  `DT_FROM` date DEFAULT NULL,
  `DT_TO` date DEFAULT NULL,
  `CLASS_LEV1` varchar(6) DEFAULT NULL,
  `CLASS_LEV3` varchar(6) DEFAULT NULL,
  `CLASS_LEV4` varchar(6) DEFAULT NULL,
  `AOU` decimal(5,0) DEFAULT NULL,
  `STATUS` varchar(10) NOT NULL,
  `PAYMENT_TYPE` varchar(9) DEFAULT NULL,
  `FTE` decimal(3,2) DEFAULT NULL,
  `PAYROLL_FLAG` varchar(1) DEFAULT NULL,
  `AWARD` varchar(6) DEFAULT NULL,
  `PAYPOINTLONG` varchar(50) DEFAULT NULL,
  `LOCATIONLONG` varchar(50) DEFAULT NULL,
  `POSITION_MDATE` date DEFAULT NULL,
  `ORG_DESC` varchar(255) DEFAULT NULL,
  `USER_NAME` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`WAMIKEY`,`STAFF_NUM`),
  UNIQUE KEY `NewIndex1` (`STAFF_NUM`),
  KEY `WAMIKEY` (`WAMIKEY`),
  KEY `AOU` (`AOU`),
  KEY `USER_NAME` (`USER_NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hr_select_staff_vw`
--

DROP TABLE IF EXISTS `hr_select_staff_vw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hr_select_staff_vw` (
  `WAMIKEY` varchar(12) DEFAULT NULL,
  `STAFF_NUM` varchar(12) NOT NULL,
  `PAYROLL_FLAG` varchar(1) DEFAULT NULL,
  `PAYMENT_TYPE` varchar(9) DEFAULT NULL,
  `FTE` decimal(3,2) DEFAULT '1.00',
  `CLASS_LEV2` varchar(6) DEFAULT NULL,
  `CLASS_LEV3` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `fez_record_search_key_author_author_id`
--

/*!50001 DROP TABLE IF EXISTS `fez_record_search_key_author_author_id`*/;
/*!50001 DROP VIEW IF EXISTS `fez_record_search_key_author_author_id`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */

/*!50001 VIEW `fez_record_search_key_author_author_id` AS (select `r1`.`rek_author_id` AS `rek_author_id_auto_inc`,`r1`.`rek_author_pid` AS `rek_author_pid`,`r1`.`rek_author_xsdmf_id` AS `rek_author_xsdmf_id`,`r1`.`rek_author` AS `rek_author`,`r1`.`rek_author_order` AS `rek_author_order`,`r2`.`rek_author_id_id` AS `rek_author_id_id`,`r2`.`rek_author_id_pid` AS `rek_author_id_pid`,`r2`.`rek_author_id_xsdmf_id` AS `rek_author_id_xsdmf_id`,`r2`.`rek_author_id` AS `rek_author_id`,`r2`.`rek_author_id_order` AS `rek_author_id_order` from (`fez_record_search_key_author` `r1` left join `fez_record_search_key_author_id` `r2` on(((`r1`.`rek_author_pid` = `r2`.`rek_author_id_pid`) and (`r1`.`rek_author_order` = `r2`.`rek_author_id_order`)))) order by `r2`.`rek_author_id_order`) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `fez_record_search_key_contributor_contributor_id`
--

/*!50001 DROP TABLE IF EXISTS `fez_record_search_key_contributor_contributor_id`*/;
/*!50001 DROP VIEW IF EXISTS `fez_record_search_key_contributor_contributor_id`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */

/*!50001 VIEW `fez_record_search_key_contributor_contributor_id` AS (select `r1`.`rek_contributor_id` AS `rek_contributor_id_auto_inc`,`r1`.`rek_contributor_pid` AS `rek_contributor_pid`,`r1`.`rek_contributor_xsdmf_id` AS `rek_contributor_xsdmf_id`,`r1`.`rek_contributor` AS `rek_contributor`,`r1`.`rek_contributor_order` AS `rek_contributor_order`,`r2`.`rek_contributor_id_id` AS `rek_contributor_id_id`,`r2`.`rek_contributor_id_pid` AS `rek_contributor_id_pid`,`r2`.`rek_contributor_id_xsdmf_id` AS `rek_contributor_id_xsdmf_id`,`r2`.`rek_contributor_id` AS `rek_contributor_id`,`r2`.`rek_contributor_id_order` AS `rek_contributor_id_order` from (`fez_record_search_key_contributor` `r1` left join `fez_record_search_key_contributor_id` `r2` on(((`r1`.`rek_contributor_pid` = `r2`.`rek_contributor_id_pid`) and (`r1`.`rek_contributor_order` = `r2`.`rek_contributor_id_order`)))) order by `r1`.`rek_contributor_order`) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `fez_record_search_key_core`
--

/*!50001 DROP TABLE IF EXISTS `fez_record_search_key_core`*/;
/*!50001 DROP VIEW IF EXISTS `fez_record_search_key_core`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */

/*!50001 VIEW `fez_record_search_key_core` AS (select `fez_record_search_key`.`rek_pid` AS `rek_pid`,`fez_record_search_key`.`rek_thomson_citation_count_xsdmf_id` AS `rek_thomson_citation_count_xsdmf_id`,`fez_record_search_key`.`rek_thomson_citation_count` AS `rek_thomson_citation_count`,`fez_record_search_key`.`rek_title_xsdmf_id` AS `rek_title_xsdmf_id`,`fez_record_search_key`.`rek_title` AS `rek_title`,`fez_record_search_key`.`rek_description_xsdmf_id` AS `rek_description_xsdmf_id`,`fez_record_search_key`.`rek_description` AS `rek_description`,`fez_record_search_key`.`rek_display_type_xsdmf_id` AS `rek_display_type_xsdmf_id`,`fez_record_search_key`.`rek_display_type` AS `rek_display_type`,`fez_record_search_key`.`rek_status_xsdmf_id` AS `rek_status_xsdmf_id`,`fez_record_search_key`.`rek_status` AS `rek_status`,`fez_record_search_key`.`rek_date_xsdmf_id` AS `rek_date_xsdmf_id`,`fez_record_search_key`.`rek_date` AS `rek_date`,`fez_record_search_key`.`rek_object_type_xsdmf_id` AS `rek_object_type_xsdmf_id`,`fez_record_search_key`.`rek_object_type` AS `rek_object_type`,`fez_record_search_key`.`rek_depositor_xsdmf_id` AS `rek_depositor_xsdmf_id`,`fez_record_search_key`.`rek_depositor` AS `rek_depositor`,`fez_record_search_key`.`rek_created_date_xsdmf_id` AS `rek_created_date_xsdmf_id`,`fez_record_search_key`.`rek_created_date` AS `rek_created_date`,`fez_record_search_key`.`rek_updated_date_xsdmf_id` AS `rek_updated_date_xsdmf_id`,`fez_record_search_key`.`rek_updated_date` AS `rek_updated_date`,`fez_record_search_key`.`rek_file_downloads` AS `rek_file_downloads`,`fez_record_search_key`.`rek_views` AS `rek_views`,`fez_record_search_key`.`rek_citation` AS `rek_citation`,`fez_record_search_key`.`rek_sequence` AS `rek_sequence`,`fez_record_search_key`.`rek_sequence_xsdmf_id` AS `rek_sequence_xsdmf_id`,`fez_record_search_key`.`rek_genre_xsdmf_id` AS `rek_genre_xsdmf_id`,`fez_record_search_key`.`rek_genre` AS `rek_genre`,`fez_record_search_key`.`rek_genre_type_xsdmf_id` AS `rek_genre_type_xsdmf_id`,`fez_record_search_key`.`rek_genre_type` AS `rek_genre_type`,`fez_record_search_key`.`rek_formatted_title_xsdmf_id` AS `rek_formatted_title_xsdmf_id`,`fez_record_search_key`.`rek_formatted_title` AS `rek_formatted_title`,`fez_record_search_key`.`rek_formatted_abstract_xsdmf_id` AS `rek_formatted_abstract_xsdmf_id`,`fez_record_search_key`.`rek_formatted_abstract` AS `rek_formatted_abstract`,`fez_record_search_key`.`rek_depositor_affiliation_xsdmf_id` AS `rek_depositor_affiliation_xsdmf_id`,`fez_record_search_key`.`rek_depositor_affiliation` AS `rek_depositor_affiliation`,`fez_record_search_key_proceedings_title`.`rek_proceedings_title_id` AS `rek_proceedings_title_id`,`fez_record_search_key_proceedings_title`.`rek_proceedings_title_pid` AS `rek_proceedings_title_pid`,`fez_record_search_key_proceedings_title`.`rek_proceedings_title_xsdmf_id` AS `rek_proceedings_title_xsdmf_id`,`fez_record_search_key_proceedings_title`.`rek_proceedings_title` AS `rek_proceedings_title`,`fez_record_search_key_collection_year`.`rek_collection_year_id` AS `rek_collection_year_id`,`fez_record_search_key_collection_year`.`rek_collection_year_pid` AS `rek_collection_year_pid`,`fez_record_search_key_collection_year`.`rek_collection_year_xsdmf_id` AS `rek_collection_year_xsdmf_id`,`fez_record_search_key_collection_year`.`rek_collection_year` AS `rek_collection_year`,`fez_record_search_key_total_pages`.`rek_total_pages_id` AS `rek_total_pages_id`,`fez_record_search_key_total_pages`.`rek_total_pages_pid` AS `rek_total_pages_pid`,`fez_record_search_key_total_pages`.`rek_total_pages_xsdmf_id` AS `rek_total_pages_xsdmf_id`,`fez_record_search_key_total_pages`.`rek_total_pages` AS `rek_total_pages`,`fez_record_search_key_total_chapters`.`rek_total_chapters_id` AS `rek_total_chapters_id`,`fez_record_search_key_total_chapters`.`rek_total_chapters_pid` AS `rek_total_chapters_pid`,`fez_record_search_key_total_chapters`.`rek_total_chapters_xsdmf_id` AS `rek_total_chapters_xsdmf_id`,`fez_record_search_key_total_chapters`.`rek_total_chapters` AS `rek_total_chapters`,`fez_record_search_key_notes`.`rek_notes_id` AS `rek_notes_id`,`fez_record_search_key_notes`.`rek_notes_pid` AS `rek_notes_pid`,`fez_record_search_key_notes`.`rek_notes_xsdmf_id` AS `rek_notes_xsdmf_id`,`fez_record_search_key_notes`.`rek_notes` AS `rek_notes`,`fez_record_search_key_publisher`.`rek_publisher_id` AS `rek_publisher_id`,`fez_record_search_key_publisher`.`rek_publisher_pid` AS `rek_publisher_pid`,`fez_record_search_key_publisher`.`rek_publisher_xsdmf_id` AS `rek_publisher_xsdmf_id`,`fez_record_search_key_publisher`.`rek_publisher` AS `rek_publisher`,`fez_record_search_key_refereed`.`rek_refereed_id` AS `rek_refereed_id`,`fez_record_search_key_refereed`.`rek_refereed_pid` AS `rek_refereed_pid`,`fez_record_search_key_refereed`.`rek_refereed_xsdmf_id` AS `rek_refereed_xsdmf_id`,`fez_record_search_key_refereed`.`rek_refereed` AS `rek_refereed`,`fez_record_search_key_series`.`rek_series_id` AS `rek_series_id`,`fez_record_search_key_series`.`rek_series_pid` AS `rek_series_pid`,`fez_record_search_key_series`.`rek_series_xsdmf_id` AS `rek_series_xsdmf_id`,`fez_record_search_key_series`.`rek_series` AS `rek_series`,`fez_record_search_key_journal_name`.`rek_journal_name_id` AS `rek_journal_name_id`,`fez_record_search_key_journal_name`.`rek_journal_name_pid` AS `rek_journal_name_pid`,`fez_record_search_key_journal_name`.`rek_journal_name_xsdmf_id` AS `rek_journal_name_xsdmf_id`,`fez_record_search_key_journal_name`.`rek_journal_name` AS `rek_journal_name`,`fez_record_search_key_newspaper`.`rek_newspaper_id` AS `rek_newspaper_id`,`fez_record_search_key_newspaper`.`rek_newspaper_pid` AS `rek_newspaper_pid`,`fez_record_search_key_newspaper`.`rek_newspaper_xsdmf_id` AS `rek_newspaper_xsdmf_id`,`fez_record_search_key_newspaper`.`rek_newspaper` AS `rek_newspaper`,`fez_record_search_key_conference_name`.`rek_conference_name_id` AS `rek_conference_name_id`,`fez_record_search_key_conference_name`.`rek_conference_name_pid` AS `rek_conference_name_pid`,`fez_record_search_key_conference_name`.`rek_conference_name_xsdmf_id` AS `rek_conference_name_xsdmf_id`,`fez_record_search_key_conference_name`.`rek_conference_name` AS `rek_conference_name`,`fez_record_search_key_book_title`.`rek_book_title_id` AS `rek_book_title_id`,`fez_record_search_key_book_title`.`rek_book_title_pid` AS `rek_book_title_pid`,`fez_record_search_key_book_title`.`rek_book_title_xsdmf_id` AS `rek_book_title_xsdmf_id`,`fez_record_search_key_book_title`.`rek_book_title` AS `rek_book_title`,`fez_record_search_key_edition`.`rek_edition_id` AS `rek_edition_id`,`fez_record_search_key_edition`.`rek_edition_pid` AS `rek_edition_pid`,`fez_record_search_key_edition`.`rek_edition_xsdmf_id` AS `rek_edition_xsdmf_id`,`fez_record_search_key_edition`.`rek_edition` AS `rek_edition`,`fez_record_search_key_place_of_publication`.`rek_place_of_publication_id` AS `rek_place_of_publication_id`,`fez_record_search_key_place_of_publication`.`rek_place_of_publication_pid` AS `rek_place_of_publication_pid`,`fez_record_search_key_place_of_publication`.`rek_place_of_publication_xsdmf_id` AS `rek_place_of_publication_xsdmf_id`,`fez_record_search_key_place_of_publication`.`rek_place_of_publication` AS `rek_place_of_publication`,`fez_record_search_key_start_page`.`rek_start_page_id` AS `rek_start_page_id`,`fez_record_search_key_start_page`.`rek_start_page_pid` AS `rek_start_page_pid`,`fez_record_search_key_start_page`.`rek_start_page_xsdmf_id` AS `rek_start_page_xsdmf_id`,`fez_record_search_key_start_page`.`rek_start_page` AS `rek_start_page`,`fez_record_search_key_end_page`.`rek_end_page_id` AS `rek_end_page_id`,`fez_record_search_key_end_page`.`rek_end_page_pid` AS `rek_end_page_pid`,`fez_record_search_key_end_page`.`rek_end_page_xsdmf_id` AS `rek_end_page_xsdmf_id`,`fez_record_search_key_end_page`.`rek_end_page` AS `rek_end_page`,`fez_record_search_key_chapter_number`.`rek_chapter_number_id` AS `rek_chapter_number_id`,`fez_record_search_key_chapter_number`.`rek_chapter_number_pid` AS `rek_chapter_number_pid`,`fez_record_search_key_chapter_number`.`rek_chapter_number_xsdmf_id` AS `rek_chapter_number_xsdmf_id`,`fez_record_search_key_chapter_number`.`rek_chapter_number` AS `rek_chapter_number`,`fez_record_search_key_issue_number`.`rek_issue_number_id` AS `rek_issue_number_id`,`fez_record_search_key_issue_number`.`rek_issue_number_pid` AS `rek_issue_number_pid`,`fez_record_search_key_issue_number`.`rek_issue_number_xsdmf_id` AS `rek_issue_number_xsdmf_id`,`fez_record_search_key_issue_number`.`rek_issue_number` AS `rek_issue_number`,`fez_record_search_key_volume_number`.`rek_volume_number_id` AS `rek_volume_number_id`,`fez_record_search_key_volume_number`.`rek_volume_number_pid` AS `rek_volume_number_pid`,`fez_record_search_key_volume_number`.`rek_volume_number_xsdmf_id` AS `rek_volume_number_xsdmf_id`,`fez_record_search_key_volume_number`.`rek_volume_number` AS `rek_volume_number`,`fez_record_search_key_conference_dates`.`rek_conference_dates_id` AS `rek_conference_dates_id`,`fez_record_search_key_conference_dates`.`rek_conference_dates_pid` AS `rek_conference_dates_pid`,`fez_record_search_key_conference_dates`.`rek_conference_dates_xsdmf_id` AS `rek_conference_dates_xsdmf_id`,`fez_record_search_key_conference_dates`.`rek_conference_dates` AS `rek_conference_dates`,`fez_record_search_key_conference_location`.`rek_conference_location_id` AS `rek_conference_location_id`,`fez_record_search_key_conference_location`.`rek_conference_location_pid` AS `rek_conference_location_pid`,`fez_record_search_key_conference_location`.`rek_conference_location_xsdmf_id` AS `rek_conference_location_xsdmf_id`,`fez_record_search_key_conference_location`.`rek_conference_location` AS `rek_conference_location`,`fez_record_search_key_patent_number`.`rek_patent_number_id` AS `rek_patent_number_id`,`fez_record_search_key_patent_number`.`rek_patent_number_pid` AS `rek_patent_number_pid`,`fez_record_search_key_patent_number`.`rek_patent_number_xsdmf_id` AS `rek_patent_number_xsdmf_id`,`fez_record_search_key_patent_number`.`rek_patent_number` AS `rek_patent_number`,`fez_record_search_key_country_of_issue`.`rek_country_of_issue_id` AS `rek_country_of_issue_id`,`fez_record_search_key_country_of_issue`.`rek_country_of_issue_pid` AS `rek_country_of_issue_pid`,`fez_record_search_key_country_of_issue`.`rek_country_of_issue_xsdmf_id` AS `rek_country_of_issue_xsdmf_id`,`fez_record_search_key_country_of_issue`.`rek_country_of_issue` AS `rek_country_of_issue`,`fez_record_search_key_date_available`.`rek_date_available_id` AS `rek_date_available_id`,`fez_record_search_key_date_available`.`rek_date_available_pid` AS `rek_date_available_pid`,`fez_record_search_key_date_available`.`rek_date_available_xsdmf_id` AS `rek_date_available_xsdmf_id`,`fez_record_search_key_date_available`.`rek_date_available` AS `rek_date_available`,`fez_record_search_key_language`.`rek_language_id` AS `rek_language_id`,`fez_record_search_key_language`.`rek_language_pid` AS `rek_language_pid`,`fez_record_search_key_language`.`rek_language_xsdmf_id` AS `rek_language_xsdmf_id`,`fez_record_search_key_language`.`rek_language` AS `rek_language`,`fez_record_search_key_phonetic_title`.`rek_phonetic_title_id` AS `rek_phonetic_title_id`,`fez_record_search_key_phonetic_title`.`rek_phonetic_title_pid` AS `rek_phonetic_title_pid`,`fez_record_search_key_phonetic_title`.`rek_phonetic_title_xsdmf_id` AS `rek_phonetic_title_xsdmf_id`,`fez_record_search_key_phonetic_title`.`rek_phonetic_title` AS `rek_phonetic_title`,`fez_record_search_key_language_of_title`.`rek_language_of_title_id` AS `rek_language_of_title_id`,`fez_record_search_key_language_of_title`.`rek_language_of_title_pid` AS `rek_language_of_title_pid`,`fez_record_search_key_language_of_title`.`rek_language_of_title_xsdmf_id` AS `rek_language_of_title_xsdmf_id`,`fez_record_search_key_language_of_title`.`rek_language_of_title` AS `rek_language_of_title`,`fez_record_search_key_translated_title`.`rek_translated_title_id` AS `rek_translated_title_id`,`fez_record_search_key_translated_title`.`rek_translated_title_pid` AS `rek_translated_title_pid`,`fez_record_search_key_translated_title`.`rek_translated_title_xsdmf_id` AS `rek_translated_title_xsdmf_id`,`fez_record_search_key_translated_title`.`rek_translated_title` AS `rek_translated_title`,`fez_record_search_key_phonetic_journal_name`.`rek_phonetic_journal_name_id` AS `rek_phonetic_journal_name_id`,`fez_record_search_key_phonetic_journal_name`.`rek_phonetic_journal_name_pid` AS `rek_phonetic_journal_name_pid`,`fez_record_search_key_phonetic_journal_name`.`rek_phonetic_journal_name_xsdmf_id` AS `rek_phonetic_journal_name_xsdmf_id`,`fez_record_search_key_phonetic_journal_name`.`rek_phonetic_journal_name` AS `rek_phonetic_journal_name`,`fez_record_search_key_translated_journal_name`.`rek_translated_journal_name_id` AS `rek_translated_journal_name_id`,`fez_record_search_key_translated_journal_name`.`rek_translated_journal_name_pid` AS `rek_translated_journal_name_pid`,`fez_record_search_key_translated_journal_name`.`rek_translated_journal_name_xsdmf_id` AS `rek_translated_journal_name_xsdmf_id`,`fez_record_search_key_translated_journal_name`.`rek_translated_journal_name` AS `rek_translated_journal_name`,`fez_record_search_key_phonetic_book_title`.`rek_phonetic_book_title_id` AS `rek_phonetic_book_title_id`,`fez_record_search_key_phonetic_book_title`.`rek_phonetic_book_title_pid` AS `rek_phonetic_book_title_pid`,`fez_record_search_key_phonetic_book_title`.`rek_phonetic_book_title_xsdmf_id` AS `rek_phonetic_book_title_xsdmf_id`,`fez_record_search_key_phonetic_book_title`.`rek_phonetic_book_title` AS `rek_phonetic_book_title`,`fez_record_search_key_translated_book_title`.`rek_translated_book_title_id` AS `rek_translated_book_title_id`,`fez_record_search_key_translated_book_title`.`rek_translated_book_title_pid` AS `rek_translated_book_title_pid`,`fez_record_search_key_translated_book_title`.`rek_translated_book_title_xsdmf_id` AS `rek_translated_book_title_xsdmf_id`,`fez_record_search_key_translated_book_title`.`rek_translated_book_title` AS `rek_translated_book_title`,`fez_record_search_key_phonetic_newspaper`.`rek_phonetic_newspaper_id` AS `rek_phonetic_newspaper_id`,`fez_record_search_key_phonetic_newspaper`.`rek_phonetic_newspaper_pid` AS `rek_phonetic_newspaper_pid`,`fez_record_search_key_phonetic_newspaper`.`rek_phonetic_newspaper_xsdmf_id` AS `rek_phonetic_newspaper_xsdmf_id`,`fez_record_search_key_phonetic_newspaper`.`rek_phonetic_newspaper` AS `rek_phonetic_newspaper`,`fez_record_search_key_translated_newspaper`.`rek_translated_newspaper_id` AS `rek_translated_newspaper_id`,`fez_record_search_key_translated_newspaper`.`rek_translated_newspaper_pid` AS `rek_translated_newspaper_pid`,`fez_record_search_key_translated_newspaper`.`rek_translated_newspaper_xsdmf_id` AS `rek_translated_newspaper_xsdmf_id`,`fez_record_search_key_translated_newspaper`.`rek_translated_newspaper` AS `rek_translated_newspaper`,`fez_record_search_key_phonetic_conference_name`.`rek_phonetic_conference_name_id` AS `rek_phonetic_conference_name_id`,`fez_record_search_key_phonetic_conference_name`.`rek_phonetic_conference_name_pid` AS `rek_phonetic_conference_name_pid`,`fez_record_search_key_phonetic_conference_name`.`rek_phonetic_conference_name_xsdmf_id` AS `rek_phonetic_conference_name_xsdmf_id`,`fez_record_search_key_phonetic_conference_name`.`rek_phonetic_conference_name` AS `rek_phonetic_conference_name`,`fez_record_search_key_translated_conference_name`.`rek_translated_conference_name_id` AS `rek_translated_conference_name_id`,`fez_record_search_key_translated_conference_name`.`rek_translated_conference_name_pid` AS `rek_translated_conference_name_pid`,`fez_record_search_key_translated_conference_name`.`rek_translated_conference_name_xsdmf_id` AS `rek_translated_conference_name_xsdmf_id`,`fez_record_search_key_translated_conference_name`.`rek_translated_conference_name` AS `rek_translated_conference_name`,`fez_record_search_key_issn`.`rek_issn_id` AS `rek_issn_id`,`fez_record_search_key_issn`.`rek_issn_pid` AS `rek_issn_pid`,`fez_record_search_key_issn`.`rek_issn_xsdmf_id` AS `rek_issn_xsdmf_id`,`fez_record_search_key_issn`.`rek_issn` AS `rek_issn`,`fez_record_search_key_isbn`.`rek_isbn_id` AS `rek_isbn_id`,`fez_record_search_key_isbn`.`rek_isbn_pid` AS `rek_isbn_pid`,`fez_record_search_key_isbn`.`rek_isbn_xsdmf_id` AS `rek_isbn_xsdmf_id`,`fez_record_search_key_isbn`.`rek_isbn` AS `rek_isbn`,`fez_record_search_key_isi_loc`.`rek_isi_loc_id` AS `rek_isi_loc_id`,`fez_record_search_key_isi_loc`.`rek_isi_loc_pid` AS `rek_isi_loc_pid`,`fez_record_search_key_isi_loc`.`rek_isi_loc_xsdmf_id` AS `rek_isi_loc_xsdmf_id`,`fez_record_search_key_isi_loc`.`rek_isi_loc` AS `rek_isi_loc`,`fez_record_search_key_prn`.`rek_prn_id` AS `rek_prn_id`,`fez_record_search_key_prn`.`rek_prn_pid` AS `rek_prn_pid`,`fez_record_search_key_prn`.`rek_prn_xsdmf_id` AS `rek_prn_xsdmf_id`,`fez_record_search_key_prn`.`rek_prn` AS `rek_prn`,`fez_record_search_key_output_availability`.`rek_output_availability_id` AS `rek_output_availability_id`,`fez_record_search_key_output_availability`.`rek_output_availability_pid` AS `rek_output_availability_pid`,`fez_record_search_key_output_availability`.`rek_output_availability_xsdmf_id` AS `rek_output_availability_xsdmf_id`,`fez_record_search_key_output_availability`.`rek_output_availability` AS `rek_output_availability`,`fez_record_search_key_na_explanation`.`rek_na_explanation_id` AS `rek_na_explanation_id`,`fez_record_search_key_na_explanation`.`rek_na_explanation_pid` AS `rek_na_explanation_pid`,`fez_record_search_key_na_explanation`.`rek_na_explanation_xsdmf_id` AS `rek_na_explanation_xsdmf_id`,`fez_record_search_key_na_explanation`.`rek_na_explanation` AS `rek_na_explanation`,`fez_record_search_key_sensitivity_explanation`.`rek_sensitivity_explanation_id` AS `rek_sensitivity_explanation_id`,`fez_record_search_key_sensitivity_explanation`.`rek_sensitivity_explanation_pid` AS `rek_sensitivity_explanation_pid`,`fez_record_search_key_sensitivity_explanation`.`rek_sensitivity_explanation_xsdmf_id` AS `rek_sensitivity_explanation_xsdmf_id`,`fez_record_search_key_sensitivity_explanation`.`rek_sensitivity_explanation` AS `rek_sensitivity_explanation`,`fez_record_search_key_org_unit_name`.`rek_org_unit_name_id` AS `rek_org_unit_name_id`,`fez_record_search_key_org_unit_name`.`rek_org_unit_name_pid` AS `rek_org_unit_name_pid`,`fez_record_search_key_org_unit_name`.`rek_org_unit_name_xsdmf_id` AS `rek_org_unit_name_xsdmf_id`,`fez_record_search_key_org_unit_name`.`rek_org_unit_name` AS `rek_org_unit_name`,`fez_record_search_key_org_name`.`rek_org_name_id` AS `rek_org_name_id`,`fez_record_search_key_org_name`.`rek_org_name_pid` AS `rek_org_name_pid`,`fez_record_search_key_org_name`.`rek_org_name_xsdmf_id` AS `rek_org_name_xsdmf_id`,`fez_record_search_key_org_name`.`rek_org_name` AS `rek_org_name`,`fez_record_search_key_report_number`.`rek_report_number_id` AS `rek_report_number_id`,`fez_record_search_key_report_number`.`rek_report_number_pid` AS `rek_report_number_pid`,`fez_record_search_key_report_number`.`rek_report_number_xsdmf_id` AS `rek_report_number_xsdmf_id`,`fez_record_search_key_report_number`.`rek_report_number` AS `rek_report_number`,`fez_record_search_key_parent_publication`.`rek_parent_publication_id` AS `rek_parent_publication_id`,`fez_record_search_key_parent_publication`.`rek_parent_publication_pid` AS `rek_parent_publication_pid`,`fez_record_search_key_parent_publication`.`rek_parent_publication_xsdmf_id` AS `rek_parent_publication_xsdmf_id`,`fez_record_search_key_parent_publication`.`rek_parent_publication` AS `rek_parent_publication`,`fez_record_search_key_scopus_id`.`rek_scopus_id_id` AS `rek_scopus_id_id`,`fez_record_search_key_scopus_id`.`rek_scopus_id_pid` AS `rek_scopus_id_pid`,`fez_record_search_key_scopus_id`.`rek_scopus_id_xsdmf_id` AS `rek_scopus_id_xsdmf_id`,`fez_record_search_key_scopus_id`.`rek_scopus_id` AS `rek_scopus_id`,`fez_record_search_key_convener`.`rek_convener_id` AS `rek_convener_id`,`fez_record_search_key_convener`.`rek_convener_pid` AS `rek_convener_pid`,`fez_record_search_key_convener`.`rek_convener_xsdmf_id` AS `rek_convener_xsdmf_id`,`fez_record_search_key_convener`.`rek_convener` AS `rek_convener` from (((((((((((((((((((((((((((((((((((((((((((((((((`fez_record_search_key` left join `fez_record_search_key_proceedings_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_proceedings_title`.`rek_proceedings_title_pid`))) left join `fez_record_search_key_collection_year` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_collection_year`.`rek_collection_year_pid`))) left join `fez_record_search_key_total_pages` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_total_pages`.`rek_total_pages_pid`))) left join `fez_record_search_key_total_chapters` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_total_chapters`.`rek_total_chapters_pid`))) left join `fez_record_search_key_notes` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_notes`.`rek_notes_pid`))) left join `fez_record_search_key_publisher` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_publisher`.`rek_publisher_pid`))) left join `fez_record_search_key_refereed` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_refereed`.`rek_refereed_pid`))) left join `fez_record_search_key_series` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_series`.`rek_series_pid`))) left join `fez_record_search_key_journal_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_journal_name`.`rek_journal_name_pid`))) left join `fez_record_search_key_newspaper` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_newspaper`.`rek_newspaper_pid`))) left join `fez_record_search_key_conference_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_conference_name`.`rek_conference_name_pid`))) left join `fez_record_search_key_book_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_book_title`.`rek_book_title_pid`))) left join `fez_record_search_key_edition` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_edition`.`rek_edition_pid`))) left join `fez_record_search_key_place_of_publication` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_place_of_publication`.`rek_place_of_publication_pid`))) left join `fez_record_search_key_start_page` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_start_page`.`rek_start_page_pid`))) left join `fez_record_search_key_end_page` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_end_page`.`rek_end_page_pid`))) left join `fez_record_search_key_chapter_number` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_chapter_number`.`rek_chapter_number_pid`))) left join `fez_record_search_key_issue_number` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_issue_number`.`rek_issue_number_pid`))) left join `fez_record_search_key_volume_number` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_volume_number`.`rek_volume_number_pid`))) left join `fez_record_search_key_conference_dates` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_conference_dates`.`rek_conference_dates_pid`))) left join `fez_record_search_key_conference_location` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_conference_location`.`rek_conference_location_pid`))) left join `fez_record_search_key_patent_number` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_patent_number`.`rek_patent_number_pid`))) left join `fez_record_search_key_country_of_issue` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_country_of_issue`.`rek_country_of_issue_pid`))) left join `fez_record_search_key_date_available` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_date_available`.`rek_date_available_pid`))) left join `fez_record_search_key_language` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_language`.`rek_language_pid`))) left join `fez_record_search_key_phonetic_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_phonetic_title`.`rek_phonetic_title_pid`))) left join `fez_record_search_key_language_of_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_language_of_title`.`rek_language_of_title_pid`))) left join `fez_record_search_key_translated_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_translated_title`.`rek_translated_title_pid`))) left join `fez_record_search_key_phonetic_journal_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_phonetic_journal_name`.`rek_phonetic_journal_name_pid`))) left join `fez_record_search_key_translated_journal_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_translated_journal_name`.`rek_translated_journal_name_pid`))) left join `fez_record_search_key_phonetic_book_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_phonetic_book_title`.`rek_phonetic_book_title_pid`))) left join `fez_record_search_key_translated_book_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_translated_book_title`.`rek_translated_book_title_pid`))) left join `fez_record_search_key_phonetic_newspaper` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_phonetic_newspaper`.`rek_phonetic_newspaper_pid`))) left join `fez_record_search_key_translated_newspaper` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_translated_newspaper`.`rek_translated_newspaper_pid`))) left join `fez_record_search_key_phonetic_conference_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_phonetic_conference_name`.`rek_phonetic_conference_name_pid`))) left join `fez_record_search_key_translated_conference_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_translated_conference_name`.`rek_translated_conference_name_pid`))) left join `fez_record_search_key_issn` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_issn`.`rek_issn_pid`))) left join `fez_record_search_key_isbn` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_isbn`.`rek_isbn_pid`))) left join `fez_record_search_key_isi_loc` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_isi_loc`.`rek_isi_loc_pid`))) left join `fez_record_search_key_prn` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_prn`.`rek_prn_pid`))) left join `fez_record_search_key_output_availability` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_output_availability`.`rek_output_availability_pid`))) left join `fez_record_search_key_na_explanation` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_na_explanation`.`rek_na_explanation_pid`))) left join `fez_record_search_key_sensitivity_explanation` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_sensitivity_explanation`.`rek_sensitivity_explanation_pid`))) left join `fez_record_search_key_org_unit_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_org_unit_name`.`rek_org_unit_name_pid`))) left join `fez_record_search_key_org_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_org_name`.`rek_org_name_pid`))) left join `fez_record_search_key_report_number` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_report_number`.`rek_report_number_pid`))) left join `fez_record_search_key_parent_publication` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_parent_publication`.`rek_parent_publication_pid`))) left join `fez_record_search_key_scopus_id` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_scopus_id`.`rek_scopus_id_pid`))) left join `fez_record_search_key_convener` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_convener`.`rek_convener_pid`)))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `fez_record_search_key_core_filtered`
--

/*!50001 DROP TABLE IF EXISTS `fez_record_search_key_core_filtered`*/;
/*!50001 DROP VIEW IF EXISTS `fez_record_search_key_core_filtered`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */

/*!50001 VIEW `fez_record_search_key_core_filtered` AS (select `fez_record_search_key`.`rek_pid` AS `rek_pid`,`fez_record_search_key_scopus_id`.`rek_scopus_id_id` AS `rek_scopus_id_id`,`fez_record_search_key_scopus_id`.`rek_scopus_id_pid` AS `rek_scopus_id_pid`,`fez_record_search_key_scopus_id`.`rek_scopus_id_xsdmf_id` AS `rek_scopus_id_xsdmf_id`,`fez_record_search_key_scopus_id`.`rek_scopus_id` AS `rek_scopus_id`,`fez_record_search_key`.`rek_thomson_citation_count_xsdmf_id` AS `rek_thomson_citation_count_xsdmf_id`,`fez_record_search_key`.`rek_thomson_citation_count` AS `rek_thomson_citation_count`,`fez_record_search_key`.`rek_title_xsdmf_id` AS `rek_title_xsdmf_id`,`fez_record_search_key`.`rek_title` AS `rek_title`,`fez_record_search_key`.`rek_description_xsdmf_id` AS `rek_description_xsdmf_id`,`fez_record_search_key`.`rek_description` AS `rek_description`,`fez_record_search_key`.`rek_display_type_xsdmf_id` AS `rek_display_type_xsdmf_id`,`fez_record_search_key`.`rek_display_type` AS `rek_display_type`,`fez_record_search_key`.`rek_status_xsdmf_id` AS `rek_status_xsdmf_id`,`fez_record_search_key`.`rek_status` AS `rek_status`,`fez_record_search_key`.`rek_date_xsdmf_id` AS `rek_date_xsdmf_id`,`fez_record_search_key`.`rek_date` AS `rek_date`,`fez_record_search_key`.`rek_object_type_xsdmf_id` AS `rek_object_type_xsdmf_id`,`fez_record_search_key`.`rek_object_type` AS `rek_object_type`,`fez_record_search_key`.`rek_depositor_xsdmf_id` AS `rek_depositor_xsdmf_id`,`fez_record_search_key`.`rek_depositor` AS `rek_depositor`,`fez_record_search_key`.`rek_created_date_xsdmf_id` AS `rek_created_date_xsdmf_id`,`fez_record_search_key`.`rek_created_date` AS `rek_created_date`,`fez_record_search_key`.`rek_updated_date_xsdmf_id` AS `rek_updated_date_xsdmf_id`,`fez_record_search_key`.`rek_updated_date` AS `rek_updated_date`,`fez_record_search_key`.`rek_file_downloads` AS `rek_file_downloads`,`fez_record_search_key`.`rek_views` AS `rek_views`,`fez_record_search_key`.`rek_citation` AS `rek_citation`,`fez_record_search_key`.`rek_sequence` AS `rek_sequence`,`fez_record_search_key`.`rek_sequence_xsdmf_id` AS `rek_sequence_xsdmf_id`,`fez_record_search_key`.`rek_genre_xsdmf_id` AS `rek_genre_xsdmf_id`,`fez_record_search_key`.`rek_genre` AS `rek_genre`,`fez_record_search_key`.`rek_genre_type_xsdmf_id` AS `rek_genre_type_xsdmf_id`,`fez_record_search_key`.`rek_genre_type` AS `rek_genre_type`,`fez_record_search_key`.`rek_formatted_title_xsdmf_id` AS `rek_formatted_title_xsdmf_id`,`fez_record_search_key`.`rek_formatted_title` AS `rek_formatted_title`,`fez_record_search_key`.`rek_formatted_abstract_xsdmf_id` AS `rek_formatted_abstract_xsdmf_id`,`fez_record_search_key`.`rek_formatted_abstract` AS `rek_formatted_abstract`,`fez_record_search_key`.`rek_depositor_affiliation_xsdmf_id` AS `rek_depositor_affiliation_xsdmf_id`,`fez_record_search_key`.`rek_depositor_affiliation` AS `rek_depositor_affiliation`,`fez_record_search_key_proceedings_title`.`rek_proceedings_title_id` AS `rek_proceedings_title_id`,`fez_record_search_key_proceedings_title`.`rek_proceedings_title_pid` AS `rek_proceedings_title_pid`,`fez_record_search_key_proceedings_title`.`rek_proceedings_title_xsdmf_id` AS `rek_proceedings_title_xsdmf_id`,`fez_record_search_key_proceedings_title`.`rek_proceedings_title` AS `rek_proceedings_title`,`fez_record_search_key_collection_year`.`rek_collection_year_id` AS `rek_collection_year_id`,`fez_record_search_key_collection_year`.`rek_collection_year_pid` AS `rek_collection_year_pid`,`fez_record_search_key_collection_year`.`rek_collection_year_xsdmf_id` AS `rek_collection_year_xsdmf_id`,`fez_record_search_key_collection_year`.`rek_collection_year` AS `rek_collection_year`,`fez_record_search_key_total_pages`.`rek_total_pages_id` AS `rek_total_pages_id`,`fez_record_search_key_total_pages`.`rek_total_pages_pid` AS `rek_total_pages_pid`,`fez_record_search_key_total_pages`.`rek_total_pages_xsdmf_id` AS `rek_total_pages_xsdmf_id`,`fez_record_search_key_total_pages`.`rek_total_pages` AS `rek_total_pages`,`fez_record_search_key_total_chapters`.`rek_total_chapters_id` AS `rek_total_chapters_id`,`fez_record_search_key_total_chapters`.`rek_total_chapters_pid` AS `rek_total_chapters_pid`,`fez_record_search_key_total_chapters`.`rek_total_chapters_xsdmf_id` AS `rek_total_chapters_xsdmf_id`,`fez_record_search_key_total_chapters`.`rek_total_chapters` AS `rek_total_chapters`,`fez_record_search_key_notes`.`rek_notes_id` AS `rek_notes_id`,`fez_record_search_key_notes`.`rek_notes_pid` AS `rek_notes_pid`,`fez_record_search_key_notes`.`rek_notes_xsdmf_id` AS `rek_notes_xsdmf_id`,`fez_record_search_key_notes`.`rek_notes` AS `rek_notes`,`fez_record_search_key_publisher`.`rek_publisher_id` AS `rek_publisher_id`,`fez_record_search_key_publisher`.`rek_publisher_pid` AS `rek_publisher_pid`,`fez_record_search_key_publisher`.`rek_publisher_xsdmf_id` AS `rek_publisher_xsdmf_id`,`fez_record_search_key_publisher`.`rek_publisher` AS `rek_publisher`,`fez_record_search_key_refereed`.`rek_refereed_id` AS `rek_refereed_id`,`fez_record_search_key_refereed`.`rek_refereed_pid` AS `rek_refereed_pid`,`fez_record_search_key_refereed`.`rek_refereed_xsdmf_id` AS `rek_refereed_xsdmf_id`,`fez_record_search_key_refereed`.`rek_refereed` AS `rek_refereed`,`fez_record_search_key_series`.`rek_series_id` AS `rek_series_id`,`fez_record_search_key_series`.`rek_series_pid` AS `rek_series_pid`,`fez_record_search_key_series`.`rek_series_xsdmf_id` AS `rek_series_xsdmf_id`,`fez_record_search_key_series`.`rek_series` AS `rek_series`,`fez_record_search_key_journal_name`.`rek_journal_name_id` AS `rek_journal_name_id`,`fez_record_search_key_journal_name`.`rek_journal_name_pid` AS `rek_journal_name_pid`,`fez_record_search_key_journal_name`.`rek_journal_name_xsdmf_id` AS `rek_journal_name_xsdmf_id`,`fez_record_search_key_journal_name`.`rek_journal_name` AS `rek_journal_name`,`fez_record_search_key_newspaper`.`rek_newspaper_id` AS `rek_newspaper_id`,`fez_record_search_key_newspaper`.`rek_newspaper_pid` AS `rek_newspaper_pid`,`fez_record_search_key_newspaper`.`rek_newspaper_xsdmf_id` AS `rek_newspaper_xsdmf_id`,`fez_record_search_key_newspaper`.`rek_newspaper` AS `rek_newspaper`,`fez_record_search_key_conference_name`.`rek_conference_name_id` AS `rek_conference_name_id`,`fez_record_search_key_conference_name`.`rek_conference_name_pid` AS `rek_conference_name_pid`,`fez_record_search_key_conference_name`.`rek_conference_name_xsdmf_id` AS `rek_conference_name_xsdmf_id`,`fez_record_search_key_conference_name`.`rek_conference_name` AS `rek_conference_name`,`fez_record_search_key_book_title`.`rek_book_title_id` AS `rek_book_title_id`,`fez_record_search_key_book_title`.`rek_book_title_pid` AS `rek_book_title_pid`,`fez_record_search_key_book_title`.`rek_book_title_xsdmf_id` AS `rek_book_title_xsdmf_id`,`fez_record_search_key_book_title`.`rek_book_title` AS `rek_book_title`,`fez_record_search_key_edition`.`rek_edition_id` AS `rek_edition_id`,`fez_record_search_key_edition`.`rek_edition_pid` AS `rek_edition_pid`,`fez_record_search_key_edition`.`rek_edition_xsdmf_id` AS `rek_edition_xsdmf_id`,`fez_record_search_key_edition`.`rek_edition` AS `rek_edition`,`fez_record_search_key_place_of_publication`.`rek_place_of_publication_id` AS `rek_place_of_publication_id`,`fez_record_search_key_place_of_publication`.`rek_place_of_publication_pid` AS `rek_place_of_publication_pid`,`fez_record_search_key_place_of_publication`.`rek_place_of_publication_xsdmf_id` AS `rek_place_of_publication_xsdmf_id`,`fez_record_search_key_place_of_publication`.`rek_place_of_publication` AS `rek_place_of_publication`,`fez_record_search_key_start_page`.`rek_start_page_id` AS `rek_start_page_id`,`fez_record_search_key_start_page`.`rek_start_page_pid` AS `rek_start_page_pid`,`fez_record_search_key_start_page`.`rek_start_page_xsdmf_id` AS `rek_start_page_xsdmf_id`,`fez_record_search_key_start_page`.`rek_start_page` AS `rek_start_page`,`fez_record_search_key_end_page`.`rek_end_page_id` AS `rek_end_page_id`,`fez_record_search_key_end_page`.`rek_end_page_pid` AS `rek_end_page_pid`,`fez_record_search_key_end_page`.`rek_end_page_xsdmf_id` AS `rek_end_page_xsdmf_id`,`fez_record_search_key_end_page`.`rek_end_page` AS `rek_end_page`,`fez_record_search_key_chapter_number`.`rek_chapter_number_id` AS `rek_chapter_number_id`,`fez_record_search_key_chapter_number`.`rek_chapter_number_pid` AS `rek_chapter_number_pid`,`fez_record_search_key_chapter_number`.`rek_chapter_number_xsdmf_id` AS `rek_chapter_number_xsdmf_id`,`fez_record_search_key_chapter_number`.`rek_chapter_number` AS `rek_chapter_number`,`fez_record_search_key_issue_number`.`rek_issue_number_id` AS `rek_issue_number_id`,`fez_record_search_key_issue_number`.`rek_issue_number_pid` AS `rek_issue_number_pid`,`fez_record_search_key_issue_number`.`rek_issue_number_xsdmf_id` AS `rek_issue_number_xsdmf_id`,`fez_record_search_key_issue_number`.`rek_issue_number` AS `rek_issue_number`,`fez_record_search_key_volume_number`.`rek_volume_number_id` AS `rek_volume_number_id`,`fez_record_search_key_volume_number`.`rek_volume_number_pid` AS `rek_volume_number_pid`,`fez_record_search_key_volume_number`.`rek_volume_number_xsdmf_id` AS `rek_volume_number_xsdmf_id`,`fez_record_search_key_volume_number`.`rek_volume_number` AS `rek_volume_number`,`fez_record_search_key_conference_dates`.`rek_conference_dates_id` AS `rek_conference_dates_id`,`fez_record_search_key_conference_dates`.`rek_conference_dates_pid` AS `rek_conference_dates_pid`,`fez_record_search_key_conference_dates`.`rek_conference_dates_xsdmf_id` AS `rek_conference_dates_xsdmf_id`,`fez_record_search_key_conference_dates`.`rek_conference_dates` AS `rek_conference_dates`,`fez_record_search_key_conference_location`.`rek_conference_location_id` AS `rek_conference_location_id`,`fez_record_search_key_conference_location`.`rek_conference_location_pid` AS `rek_conference_location_pid`,`fez_record_search_key_conference_location`.`rek_conference_location_xsdmf_id` AS `rek_conference_location_xsdmf_id`,`fez_record_search_key_conference_location`.`rek_conference_location` AS `rek_conference_location`,`fez_record_search_key_patent_number`.`rek_patent_number_id` AS `rek_patent_number_id`,`fez_record_search_key_patent_number`.`rek_patent_number_pid` AS `rek_patent_number_pid`,`fez_record_search_key_patent_number`.`rek_patent_number_xsdmf_id` AS `rek_patent_number_xsdmf_id`,`fez_record_search_key_patent_number`.`rek_patent_number` AS `rek_patent_number`,`fez_record_search_key_country_of_issue`.`rek_country_of_issue_id` AS `rek_country_of_issue_id`,`fez_record_search_key_country_of_issue`.`rek_country_of_issue_pid` AS `rek_country_of_issue_pid`,`fez_record_search_key_country_of_issue`.`rek_country_of_issue_xsdmf_id` AS `rek_country_of_issue_xsdmf_id`,`fez_record_search_key_country_of_issue`.`rek_country_of_issue` AS `rek_country_of_issue`,`fez_record_search_key_date_available`.`rek_date_available_id` AS `rek_date_available_id`,`fez_record_search_key_date_available`.`rek_date_available_pid` AS `rek_date_available_pid`,`fez_record_search_key_date_available`.`rek_date_available_xsdmf_id` AS `rek_date_available_xsdmf_id`,`fez_record_search_key_date_available`.`rek_date_available` AS `rek_date_available`,`fez_record_search_key_language`.`rek_language_id` AS `rek_language_id`,`fez_record_search_key_language`.`rek_language_pid` AS `rek_language_pid`,`fez_record_search_key_language`.`rek_language_xsdmf_id` AS `rek_language_xsdmf_id`,`fez_record_search_key_language`.`rek_language` AS `rek_language`,`fez_record_search_key_phonetic_title`.`rek_phonetic_title_id` AS `rek_phonetic_title_id`,`fez_record_search_key_phonetic_title`.`rek_phonetic_title_pid` AS `rek_phonetic_title_pid`,`fez_record_search_key_phonetic_title`.`rek_phonetic_title_xsdmf_id` AS `rek_phonetic_title_xsdmf_id`,`fez_record_search_key_phonetic_title`.`rek_phonetic_title` AS `rek_phonetic_title`,`fez_record_search_key_language_of_title`.`rek_language_of_title_id` AS `rek_language_of_title_id`,`fez_record_search_key_language_of_title`.`rek_language_of_title_pid` AS `rek_language_of_title_pid`,`fez_record_search_key_language_of_title`.`rek_language_of_title_xsdmf_id` AS `rek_language_of_title_xsdmf_id`,`fez_record_search_key_language_of_title`.`rek_language_of_title` AS `rek_language_of_title`,`fez_record_search_key_translated_title`.`rek_translated_title_id` AS `rek_translated_title_id`,`fez_record_search_key_translated_title`.`rek_translated_title_pid` AS `rek_translated_title_pid`,`fez_record_search_key_translated_title`.`rek_translated_title_xsdmf_id` AS `rek_translated_title_xsdmf_id`,`fez_record_search_key_translated_title`.`rek_translated_title` AS `rek_translated_title`,`fez_record_search_key_phonetic_journal_name`.`rek_phonetic_journal_name_id` AS `rek_phonetic_journal_name_id`,`fez_record_search_key_phonetic_journal_name`.`rek_phonetic_journal_name_pid` AS `rek_phonetic_journal_name_pid`,`fez_record_search_key_phonetic_journal_name`.`rek_phonetic_journal_name_xsdmf_id` AS `rek_phonetic_journal_name_xsdmf_id`,`fez_record_search_key_phonetic_journal_name`.`rek_phonetic_journal_name` AS `rek_phonetic_journal_name`,`fez_record_search_key_translated_journal_name`.`rek_translated_journal_name_id` AS `rek_translated_journal_name_id`,`fez_record_search_key_translated_journal_name`.`rek_translated_journal_name_pid` AS `rek_translated_journal_name_pid`,`fez_record_search_key_translated_journal_name`.`rek_translated_journal_name_xsdmf_id` AS `rek_translated_journal_name_xsdmf_id`,`fez_record_search_key_translated_journal_name`.`rek_translated_journal_name` AS `rek_translated_journal_name`,`fez_record_search_key_phonetic_book_title`.`rek_phonetic_book_title_id` AS `rek_phonetic_book_title_id`,`fez_record_search_key_phonetic_book_title`.`rek_phonetic_book_title_pid` AS `rek_phonetic_book_title_pid`,`fez_record_search_key_phonetic_book_title`.`rek_phonetic_book_title_xsdmf_id` AS `rek_phonetic_book_title_xsdmf_id`,`fez_record_search_key_phonetic_book_title`.`rek_phonetic_book_title` AS `rek_phonetic_book_title`,`fez_record_search_key_translated_book_title`.`rek_translated_book_title_id` AS `rek_translated_book_title_id`,`fez_record_search_key_translated_book_title`.`rek_translated_book_title_pid` AS `rek_translated_book_title_pid`,`fez_record_search_key_translated_book_title`.`rek_translated_book_title_xsdmf_id` AS `rek_translated_book_title_xsdmf_id`,`fez_record_search_key_translated_book_title`.`rek_translated_book_title` AS `rek_translated_book_title`,`fez_record_search_key_phonetic_newspaper`.`rek_phonetic_newspaper_id` AS `rek_phonetic_newspaper_id`,`fez_record_search_key_phonetic_newspaper`.`rek_phonetic_newspaper_pid` AS `rek_phonetic_newspaper_pid`,`fez_record_search_key_phonetic_newspaper`.`rek_phonetic_newspaper_xsdmf_id` AS `rek_phonetic_newspaper_xsdmf_id`,`fez_record_search_key_phonetic_newspaper`.`rek_phonetic_newspaper` AS `rek_phonetic_newspaper`,`fez_record_search_key_translated_newspaper`.`rek_translated_newspaper_id` AS `rek_translated_newspaper_id`,`fez_record_search_key_translated_newspaper`.`rek_translated_newspaper_pid` AS `rek_translated_newspaper_pid`,`fez_record_search_key_translated_newspaper`.`rek_translated_newspaper_xsdmf_id` AS `rek_translated_newspaper_xsdmf_id`,`fez_record_search_key_translated_newspaper`.`rek_translated_newspaper` AS `rek_translated_newspaper`,`fez_record_search_key_phonetic_conference_name`.`rek_phonetic_conference_name_id` AS `rek_phonetic_conference_name_id`,`fez_record_search_key_phonetic_conference_name`.`rek_phonetic_conference_name_pid` AS `rek_phonetic_conference_name_pid`,`fez_record_search_key_phonetic_conference_name`.`rek_phonetic_conference_name_xsdmf_id` AS `rek_phonetic_conference_name_xsdmf_id`,`fez_record_search_key_phonetic_conference_name`.`rek_phonetic_conference_name` AS `rek_phonetic_conference_name`,`fez_record_search_key_translated_conference_name`.`rek_translated_conference_name_id` AS `rek_translated_conference_name_id`,`fez_record_search_key_translated_conference_name`.`rek_translated_conference_name_pid` AS `rek_translated_conference_name_pid`,`fez_record_search_key_translated_conference_name`.`rek_translated_conference_name_xsdmf_id` AS `rek_translated_conference_name_xsdmf_id`,`fez_record_search_key_translated_conference_name`.`rek_translated_conference_name` AS `rek_translated_conference_name`,`fez_record_search_key_issn`.`rek_issn_id` AS `rek_issn_id`,`fez_record_search_key_issn`.`rek_issn_pid` AS `rek_issn_pid`,`fez_record_search_key_issn`.`rek_issn_xsdmf_id` AS `rek_issn_xsdmf_id`,`fez_record_search_key_issn`.`rek_issn` AS `rek_issn`,`fez_record_search_key_isbn`.`rek_isbn_id` AS `rek_isbn_id`,`fez_record_search_key_isbn`.`rek_isbn_pid` AS `rek_isbn_pid`,`fez_record_search_key_isbn`.`rek_isbn_xsdmf_id` AS `rek_isbn_xsdmf_id`,`fez_record_search_key_isbn`.`rek_isbn` AS `rek_isbn`,`fez_record_search_key_isi_loc`.`rek_isi_loc_id` AS `rek_isi_loc_id`,`fez_record_search_key_isi_loc`.`rek_isi_loc_pid` AS `rek_isi_loc_pid`,`fez_record_search_key_isi_loc`.`rek_isi_loc_xsdmf_id` AS `rek_isi_loc_xsdmf_id`,`fez_record_search_key_isi_loc`.`rek_isi_loc` AS `rek_isi_loc`,`fez_record_search_key_prn`.`rek_prn_id` AS `rek_prn_id`,`fez_record_search_key_prn`.`rek_prn_pid` AS `rek_prn_pid`,`fez_record_search_key_prn`.`rek_prn_xsdmf_id` AS `rek_prn_xsdmf_id`,`fez_record_search_key_prn`.`rek_prn` AS `rek_prn`,`fez_record_search_key_output_availability`.`rek_output_availability_id` AS `rek_output_availability_id`,`fez_record_search_key_output_availability`.`rek_output_availability_pid` AS `rek_output_availability_pid`,`fez_record_search_key_output_availability`.`rek_output_availability_xsdmf_id` AS `rek_output_availability_xsdmf_id`,`fez_record_search_key_output_availability`.`rek_output_availability` AS `rek_output_availability`,`fez_record_search_key_na_explanation`.`rek_na_explanation_id` AS `rek_na_explanation_id`,`fez_record_search_key_na_explanation`.`rek_na_explanation_pid` AS `rek_na_explanation_pid`,`fez_record_search_key_na_explanation`.`rek_na_explanation_xsdmf_id` AS `rek_na_explanation_xsdmf_id`,`fez_record_search_key_na_explanation`.`rek_na_explanation` AS `rek_na_explanation`,`fez_record_search_key_sensitivity_explanation`.`rek_sensitivity_explanation_id` AS `rek_sensitivity_explanation_id`,`fez_record_search_key_sensitivity_explanation`.`rek_sensitivity_explanation_pid` AS `rek_sensitivity_explanation_pid`,`fez_record_search_key_sensitivity_explanation`.`rek_sensitivity_explanation_xsdmf_id` AS `rek_sensitivity_explanation_xsdmf_id`,`fez_record_search_key_sensitivity_explanation`.`rek_sensitivity_explanation` AS `rek_sensitivity_explanation`,`fez_record_search_key_org_unit_name`.`rek_org_unit_name_id` AS `rek_org_unit_name_id`,`fez_record_search_key_org_unit_name`.`rek_org_unit_name_pid` AS `rek_org_unit_name_pid`,`fez_record_search_key_org_unit_name`.`rek_org_unit_name_xsdmf_id` AS `rek_org_unit_name_xsdmf_id`,`fez_record_search_key_org_unit_name`.`rek_org_unit_name` AS `rek_org_unit_name`,`fez_record_search_key_org_name`.`rek_org_name_id` AS `rek_org_name_id`,`fez_record_search_key_org_name`.`rek_org_name_pid` AS `rek_org_name_pid`,`fez_record_search_key_org_name`.`rek_org_name_xsdmf_id` AS `rek_org_name_xsdmf_id`,`fez_record_search_key_org_name`.`rek_org_name` AS `rek_org_name`,`fez_record_search_key_report_number`.`rek_report_number_id` AS `rek_report_number_id`,`fez_record_search_key_report_number`.`rek_report_number_pid` AS `rek_report_number_pid`,`fez_record_search_key_report_number`.`rek_report_number_xsdmf_id` AS `rek_report_number_xsdmf_id`,`fez_record_search_key_report_number`.`rek_report_number` AS `rek_report_number`,`fez_record_search_key_parent_publication`.`rek_parent_publication_id` AS `rek_parent_publication_id`,`fez_record_search_key_parent_publication`.`rek_parent_publication_pid` AS `rek_parent_publication_pid`,`fez_record_search_key_parent_publication`.`rek_parent_publication_xsdmf_id` AS `rek_parent_publication_xsdmf_id`,`fez_record_search_key_parent_publication`.`rek_parent_publication` AS `rek_parent_publication`,`fez_record_search_key_convener`.`rek_convener_id` AS `rek_convener_id`,`fez_record_search_key_convener`.`rek_convener_pid` AS `rek_convener_pid`,`fez_record_search_key_convener`.`rek_convener_xsdmf_id` AS `rek_convener_xsdmf_id`,`fez_record_search_key_convener`.`rek_convener` AS `rek_convener` from ((((((((((((((((((((((((((((((((((((((((((((((((((((`fez_record_search_key` join `fez_auth_index2_lister` on(((`fez_auth_index2_lister`.`authi_pid` = `fez_record_search_key`.`rek_pid`) and (`fez_record_search_key`.`rek_status` = 2)))) join `fez_auth_rule_group_rules` on((`fez_auth_index2_lister`.`authi_arg_id` = `fez_auth_rule_group_rules`.`argr_arg_id`))) join `fez_auth_rules` on(((`fez_auth_rule_group_rules`.`argr_ar_id` = `fez_auth_rules`.`ar_id`) and (`fez_auth_rules`.`ar_rule` = _utf8'public_list') and (`fez_auth_rules`.`ar_value` = 1)))) left join `fez_record_search_key_proceedings_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_proceedings_title`.`rek_proceedings_title_pid`))) left join `fez_record_search_key_collection_year` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_collection_year`.`rek_collection_year_pid`))) left join `fez_record_search_key_total_pages` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_total_pages`.`rek_total_pages_pid`))) left join `fez_record_search_key_total_chapters` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_total_chapters`.`rek_total_chapters_pid`))) left join `fez_record_search_key_notes` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_notes`.`rek_notes_pid`))) left join `fez_record_search_key_publisher` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_publisher`.`rek_publisher_pid`))) left join `fez_record_search_key_refereed` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_refereed`.`rek_refereed_pid`))) left join `fez_record_search_key_series` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_series`.`rek_series_pid`))) left join `fez_record_search_key_journal_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_journal_name`.`rek_journal_name_pid`))) left join `fez_record_search_key_newspaper` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_newspaper`.`rek_newspaper_pid`))) left join `fez_record_search_key_conference_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_conference_name`.`rek_conference_name_pid`))) left join `fez_record_search_key_book_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_book_title`.`rek_book_title_pid`))) left join `fez_record_search_key_edition` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_edition`.`rek_edition_pid`))) left join `fez_record_search_key_place_of_publication` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_place_of_publication`.`rek_place_of_publication_pid`))) left join `fez_record_search_key_start_page` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_start_page`.`rek_start_page_pid`))) left join `fez_record_search_key_end_page` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_end_page`.`rek_end_page_pid`))) left join `fez_record_search_key_chapter_number` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_chapter_number`.`rek_chapter_number_pid`))) left join `fez_record_search_key_issue_number` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_issue_number`.`rek_issue_number_pid`))) left join `fez_record_search_key_volume_number` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_volume_number`.`rek_volume_number_pid`))) left join `fez_record_search_key_conference_dates` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_conference_dates`.`rek_conference_dates_pid`))) left join `fez_record_search_key_conference_location` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_conference_location`.`rek_conference_location_pid`))) left join `fez_record_search_key_patent_number` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_patent_number`.`rek_patent_number_pid`))) left join `fez_record_search_key_country_of_issue` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_country_of_issue`.`rek_country_of_issue_pid`))) left join `fez_record_search_key_date_available` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_date_available`.`rek_date_available_pid`))) left join `fez_record_search_key_language` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_language`.`rek_language_pid`))) left join `fez_record_search_key_phonetic_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_phonetic_title`.`rek_phonetic_title_pid`))) left join `fez_record_search_key_language_of_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_language_of_title`.`rek_language_of_title_pid`))) left join `fez_record_search_key_translated_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_translated_title`.`rek_translated_title_pid`))) left join `fez_record_search_key_phonetic_journal_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_phonetic_journal_name`.`rek_phonetic_journal_name_pid`))) left join `fez_record_search_key_translated_journal_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_translated_journal_name`.`rek_translated_journal_name_pid`))) left join `fez_record_search_key_phonetic_book_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_phonetic_book_title`.`rek_phonetic_book_title_pid`))) left join `fez_record_search_key_translated_book_title` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_translated_book_title`.`rek_translated_book_title_pid`))) left join `fez_record_search_key_phonetic_newspaper` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_phonetic_newspaper`.`rek_phonetic_newspaper_pid`))) left join `fez_record_search_key_translated_newspaper` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_translated_newspaper`.`rek_translated_newspaper_pid`))) left join `fez_record_search_key_phonetic_conference_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_phonetic_conference_name`.`rek_phonetic_conference_name_pid`))) left join `fez_record_search_key_translated_conference_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_translated_conference_name`.`rek_translated_conference_name_pid`))) left join `fez_record_search_key_issn` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_issn`.`rek_issn_pid`))) left join `fez_record_search_key_isbn` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_isbn`.`rek_isbn_pid`))) left join `fez_record_search_key_isi_loc` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_isi_loc`.`rek_isi_loc_pid`))) left join `fez_record_search_key_prn` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_prn`.`rek_prn_pid`))) left join `fez_record_search_key_output_availability` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_output_availability`.`rek_output_availability_pid`))) left join `fez_record_search_key_na_explanation` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_na_explanation`.`rek_na_explanation_pid`))) left join `fez_record_search_key_sensitivity_explanation` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_sensitivity_explanation`.`rek_sensitivity_explanation_pid`))) left join `fez_record_search_key_org_unit_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_org_unit_name`.`rek_org_unit_name_pid`))) left join `fez_record_search_key_org_name` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_org_name`.`rek_org_name_pid`))) left join `fez_record_search_key_report_number` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_report_number`.`rek_report_number_pid`))) left join `fez_record_search_key_parent_publication` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_parent_publication`.`rek_parent_publication_pid`))) left join `fez_record_search_key_scopus_id` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_scopus_id`.`rek_scopus_id_pid`))) left join `fez_record_search_key_convener` on((`fez_record_search_key`.`rek_pid` = `fez_record_search_key_convener`.`rek_convener_pid`)))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `fez_record_search_key_link_link_description`
--

/*!50001 DROP TABLE IF EXISTS `fez_record_search_key_link_link_description`*/;
/*!50001 DROP VIEW IF EXISTS `fez_record_search_key_link_link_description`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */

/*!50001 VIEW `fez_record_search_key_link_link_description` AS (select `fez_record_search_key_link`.`rek_link_id` AS `rek_link_id`,`fez_record_search_key_link`.`rek_link_pid` AS `rek_link_pid`,`fez_record_search_key_link`.`rek_link_xsdmf_id` AS `rek_link_xsdmf_id`,`fez_record_search_key_link`.`rek_link` AS `rek_link`,`fez_record_search_key_link`.`rek_link_order` AS `rek_link_order`,`fez_record_search_key_link_description`.`rek_link_description_id` AS `rek_link_description_id`,`fez_record_search_key_link_description`.`rek_link_description_pid` AS `rek_link_description_pid`,`fez_record_search_key_link_description`.`rek_link_description_xsdmf_id` AS `rek_link_description_xsdmf_id`,`fez_record_search_key_link_description`.`rek_link_description` AS `rek_link_description`,`fez_record_search_key_link_description`.`rek_link_description_order` AS `rek_link_description_order` from (`fez_record_search_key_link` join `fez_record_search_key_link_description` on(((`fez_record_search_key_link`.`rek_link_pid` = `fez_record_search_key_link_description`.`rek_link_description_pid`) and (`fez_record_search_key_link`.`rek_link_order` = `fez_record_search_key_link_description`.`rek_link_description_order`)))) order by `fez_record_search_key_link`.`rek_link_order`) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-10-26 13:00:10
