CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%digital_object (
  pidns varchar(5) NOT NULL,
  pidint int(11) NOT NULL,
  xdis_id int(11) NOT NULL,
  sta_id int(11) NOT NULL,
  usr_id int(11) NOT NULL,
  grp_id int(11) DEFAULT NULL,
  copyright enum('on','off') NOT NULL DEFAULT 'on',
  depositor int(11) DEFAULT NULL,
  depositor_affiliation int(11) DEFAULT NULL,
  additional_notes text,
  refereed enum('on','off') DEFAULT NULL,
  herdc_status int(11) DEFAULT NULL,
  institutional_status int(11) DEFAULT NULL,
  follow_up enum('on','off') NOT NULL DEFAULT 'off',
  follow_up_imu enum('on','off') NOT NULL DEFAULT 'off',
  created timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (pidns,pidint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;