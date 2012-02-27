DROP TABLE IF EXISTS  %TABLE_PREFIX%author_affiliation;              
CREATE TABLE %TABLE_PREFIX%author_affiliation (              
                          af_id int(10) unsigned NOT NULL auto_increment,  
                          af_pid varchar(32) NOT NULL,                     
                          af_author_id int(11) NOT NULL,                   
                          af_percent_affiliation int(11) NOT NULL,         
                          af_org_id int(11) NOT NULL,                   
                          PRIMARY KEY  (af_id)                             
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
