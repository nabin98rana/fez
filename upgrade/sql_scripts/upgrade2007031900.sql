create table `%TABLE_PREFIX%citations` 
        (  `cit_id` int NOT NULL AUTO_INCREMENT , 
           `cit_xdis_id` int NOT NULL , 
           `cit_template` text NOT NULL , 
           `cit_type` varchar (10) NOT NULL , 
           PRIMARY KEY ( `cit_id`)) ; 
