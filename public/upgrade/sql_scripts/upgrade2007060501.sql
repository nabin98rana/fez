
CREATE TABLE %TABLE_PREFIX%mail_queue (
                      maq_id int(11) unsigned NOT NULL auto_increment,
                      maq_queued_date datetime NOT NULL default CURRENT_TIMESTAMP,
                      maq_status varchar(8) NOT NULL default 'pending',
                      maq_save_copy tinyint(1) NOT NULL default '1',
                      maq_sender_ip_address varchar(15) NOT NULL default '',
                      maq_recipient varchar(255) NOT NULL default '',
                      maq_headers text NOT NULL,
                      maq_body longtext NOT NULL,
                      PRIMARY KEY  (maq_id),
                      KEY maq_status (maq_status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %TABLE_PREFIX%mail_queue_log (
                          mql_id int(11) unsigned NOT NULL auto_increment,
                          mql_maq_id int(11) unsigned NOT NULL default '0',
                          mql_created_date datetime NOT NULL default CURRENT_TIMESTAMP ,
                          mql_status varchar(8) NOT NULL default 'error',
                          mql_server_message text,
                          PRIMARY KEY  (mql_id),
                          KEY mql_maq_id (mql_maq_id)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
