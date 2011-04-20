CREATE TABLE %TABLE_PREFIX%datastream_cache (
  dc_pid varchar(64) NOT NULL,
  dc_dsid varchar(1000) NOT NULL,
  PRIMARY KEY (dc_pid,dc_dsid)
);
