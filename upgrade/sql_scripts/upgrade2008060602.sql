insert ignore into %TABLE_PREFIX%config (`config_name`, `config_module`, `config_value`) values ('app_exiftool_switch','core','ON');
insert ignore into %TABLE_PREFIX%config (`config_name`, `config_module`, `config_value`) values ('app_exiftool_cmd','core','/usr/bin/exiftool');

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%exif (                                                 
            `exif_pid` varchar(64) character set utf8 NOT NULL,                     
            `exif_dsid` varchar(255) character set utf8 NOT NULL,                   
            `exif_file_size` varchar(64) character set utf8 default NULL,           
            `exif_file_size_human` varchar(64) character set utf8 default NULL,           
            `exif_image_width` int(11) default NULL,                                
            `exif_image_height` int(11) default NULL,                               
            `exif_mime_type` varchar(64) character set utf8 default NULL,           
            `exif_camera_model_name` varchar(255) character set utf8 default NULL,  
            `exif_make` varchar(255) character set utf8 default NULL,               
            `exif_create_date` datetime default NULL,                               
            `exif_file_type` varchar(64) character set utf8 default NULL,           
            `exif_page_count` int(11) default NULL,                                 
            `exif_play_duration` varchar(64) character set utf8 default NULL,       
            `exif_all` text character set utf8,                                     
            PRIMARY KEY  (`exif_pid`,`exif_dsid`)                                   
);