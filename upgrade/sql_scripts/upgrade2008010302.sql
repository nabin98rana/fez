create table %TABLE_PREFIX%user_comments (
     usc_id           integer       auto_increment primary key,
     usc_userid       integer       not null default 0,
     usc_pid          varchar(64)   not null default '',
     usc_comment      text          not null default '',
     usc_rating       integer       not null default 0,
     usc_date_created timestamp     not null default CURRENT_TIMESTAMP
);