ALTER TABLE %TABLE_PREFIX%thomson_citations ADD COLUMN tc_isi_loc varchar(255) DEFAULT NULL;
ALTER TABLE %TABLE_PREFIX%scopus_citations ADD COLUMN sc_eid varchar(255) DEFAULT NULL;
