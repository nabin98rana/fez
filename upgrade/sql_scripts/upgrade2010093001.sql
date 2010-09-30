alter table %TABLE_PREFIX%config MODIFY config_name varchar(100);

insert ignore into %TABLE_PREFIX%config (`config_name`, `config_module`, `config_value`) values ('app_my_research_new_items_collection','core','');
