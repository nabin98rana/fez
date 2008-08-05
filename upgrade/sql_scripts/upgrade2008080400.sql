CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%ad_hoc_sql (                                                 
            `ahs_id` int(11) NOT NULL auto_increment,                               
            `ahs_name` varchar(64) character set utf8 default NULL,           
            `ahs_query` text character set utf8 default NULL,                                     
            `ahs_query_show` text character set utf8 default NULL,                                     
            `ahs_query_count` text character set utf8 default NULL,                                     
            PRIMARY KEY  (`ahs_id`)                                   
);
