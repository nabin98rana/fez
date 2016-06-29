ALTER TABLE %TABLE_PREFIX%background_process ADD bgp_task_arn VARCHAR(128) NULL;
ALTER TABLE %TABLE_PREFIX%fulltext_locks CHANGE ftl_pid ftl_pid VARCHAR(128) NULL;
