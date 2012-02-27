CREATE TABLE %TABLE_PREFIX%matched_journals (
  mtj_pid varchar(64) NOT NULL,
  mtj_eraid varchar(10) NOT NULL,
  mtj_status varchar(1) NOT NULL,
  PRIMARY KEY (mtj_pid)
);

CREATE TABLE %TABLE_PREFIX%matched_conferences (
  mtc_pid varchar(64) NOT NULL,
  mtc_eraid varchar(10) NOT NULL,
  mtc_status varchar(1) NOT NULL,
  PRIMARY KEY (mtc_pid)
);
