CREATE TABLE %TABLE_PREFIX%recently_added_items (
     rai_pid          varchar(64)       primary key
);
CREATE TABLE %TABLE_PREFIX%cloud_tag (
     keyword      varchar(100)       primary key,
     quantity     int unsigned
);