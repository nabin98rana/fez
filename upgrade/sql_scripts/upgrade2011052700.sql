CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%link_status_reports (
  lsr_url varchar(255)  NOT NULL,
  lsr_status varchar(3) NOT NULL,
  lsr_timestamp timestamp NOT NULL,
  PRIMARY KEY (lsr_url)
);

/*CREATE UNIQUE INDEX idx_lsr_url ON %TABLE_PREFIX%link_status_reports (lsr_url);*/
