CREATE TABLE %TABLE_PREFIX%input_filter (
  ift_id int(11) NOT NULL AUTO_INCREMENT,
  ift_input_name varchar(45) NOT NULL,
  ift_filter_class varchar(45) NOT NULL,
  PRIMARY KEY (ift_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;