ALTER TABLE %TABLE_PREFIX%background_process ADD bgp_task_arn VARCHAR(64) NULL;
ALTER TABLE %TABLE_PREFIX%fulltext_locks CHANGE ftl_pid ftl_pid VARCHAR(64) NULL;
