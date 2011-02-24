create table %TABLE_PREFIX%workflow_sessions (  
        wfses_id int NOT NULL AUTO_INCREMENT , 
        wfses_usr_id int NOT NULL , 
        wfses_object blob , 
        wfses_listing varchar (255)  NOT NULL ,
        wfses_date datetime   NOT NULL ,
        PRIMARY KEY ( wfses_id))  ;
