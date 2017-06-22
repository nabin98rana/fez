CREATE TABLE %TABLE_PREFIX%background_process_pids
(
	bgpid_bgp_id int(11) not null,
	bgpid_pid varchar(64) not null,
	primary key (bgpid_bgp_id, bgpid_pid)
);

ALTER TABLE %TABLE_PREFIX%background_process_pids ADD INDEX bgpid_pid_idx (bgpid_pid)
