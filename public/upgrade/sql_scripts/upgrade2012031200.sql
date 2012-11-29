CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%digital_object (
  pidns varchar(5) NOT NULL,
  pidint int(11) NOT NULL,
  PRIMARY KEY (pidns,pidint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;