DROP TABLE IF EXISTS %TABLE_PREFIX%search_key;

CREATE TABLE %TABLE_PREFIX%search_key (
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
  PRIMARY KEY  (`sek_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (2,'Title',NULL,1,1,1,0,'text','none','',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (3,'Author',NULL,1,1,NULL,1,'text','','',1);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (4,'Subject','',1,1,0,20,'contvocab','none','',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (5,'Description',NULL,1,1,NULL,2,'text','','',1);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (6,'File Attachment Name','',0,0,0,9,'text','none','',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (7,'File Attachment Content',NULL,0,0,NULL,999,'text','none','',1);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (8,'isMemberOf','Collection',0,0,1,12,'combo','none','',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (9,'Status',NULL,0,0,1,6,'combo','none','Status::getUnpublishedAssocList()',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (10,'Object Type',NULL,1,0,NULL,8,'multiple','none','$ret_list',1);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (11,'Display Type',NULL,1,0,NULL,5,'combo','none','$xdis_list',1);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (12,'Keywords','',0,0,0,3,'text','none','',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (13,'Notes',NULL,0,0,NULL,999,'','','',1);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (14,'Date','',0,0,0,7,'date','none','',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (15,'XSD Display Option',NULL,0,0,NULL,999,'','','',1);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (16,'File Downloads','',0,0,0,999,'text','none','',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (17,'Created Date','',0,0,0,999,'text','none','',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (18,'Updated Date',NULL,0,0,NULL,999,'text','none','',1);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (19,'Research Program','',0,0,0,4,'text','none','',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (20,'Depositor','',1,0,1,16,'combo','none','User::getAssocList()',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (21,'isDerivationOf',NULL,0,0,0,15,'text','none','',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (22,'Assigned User ID','Assigned',0,0,1,11,'combo','none','User::getAssocList()',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (23,'Assigned Group ID','Team/Group',0,0,1,10,'combo','none','Group::getAssocListAll()',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (24,'isDataComponentOf',NULL,0,0,0,13,'multiple','none','',450005);
insert  into %TABLE_PREFIX%search_key(`sek_id`,`sek_title`,`sek_alt_title`,`sek_adv_visible`,`sek_simple_used`,`sek_myfez_visible`,`sek_order`,`sek_html_input`,`sek_fez_variable`,`sek_smarty_variable`,`sek_cvo_id`) values (25,'isAnnotationOf',NULL,0,0,0,14,'multiple','none','',450005);
