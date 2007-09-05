CREATE TABLE %TABLE_PREFIX%record_locks (
        `rl_id` int(11) NOT NULL auto_increment,
        `rl_pid` varchar(64) NOT NULL,
        `rl_usr_id` int(11) NOT NULL,
        PRIMARY KEY  (`rl_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
