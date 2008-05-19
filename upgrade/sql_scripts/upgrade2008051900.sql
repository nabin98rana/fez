insert ignore into %TABLE_PREFIX%config (`config_name`, `config_module`, `config_value`) values ('app_solr_indexer','core','OFF');
CREATE UNIQUE INDEX pid_op ON fez_fulltext_queue (ftq_pid, ftq_op);